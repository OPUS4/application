<?php
/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the Cooperative Library Network Berlin-Brandenburg,
 * the Saarland University and State Library, the Saxon State Library -
 * Dresden State and University Library, the Bielefeld University Library and
 * the University Library of Hamburg University of Technology with funding from
 * the German Research Foundation and the European Regional Development Fund.
 *
 * LICENCE
 * OPUS is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or any later version.
 * OPUS is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details. You should have received a copy of the GNU General Public License
 * along with OPUS; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * @category    Application
 * @package     Notification
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Util_Notification {

    const SUBMISSION = "submission";
    const PUBLICATION = "publication";

    private $_logger;
    private $_config;

    public function __construct($logger = null, $config = null) {
        $this->_logger = is_null($logger) ? Zend_Registry::get('Zend_Log') : $logger;
        $this->_config = is_null($config) ? Zend_Registry::get('Zend_Config') : $config;
    }

    /**
     *
     * @param Opus_Document $document das Dokument auf das sich die Notifizierung bezieht
     * @param String $context Notifizierungskontext
     * @param String $url vollständiger Deeplink, der in der Mail angezeigt werden soll
     * @param boolean $notifySubmitter Wenn false, wird der Submitter nicht notifiziert
     * @param array $notifyAuthors Bitmaske, die für jeden Autor (über den Index referenziert) angibt, ob ihm/ihr eine
     *                             E-Mail gesendet werden kann (wenn false, dann wird keine Notifizierung versendet)
     */
    public function prepareMail($document, $context, $url, $notifySubmitter = true, $notifyAuthors = array()) {
        if (!$this->validateContext($context)) {
            $this->_logger->err(
                "context $context is currently not supported or delivery of notification mails is not"
                . ' enabled for the current context'
            );
            return;
        }

        $this->_logger->info("prepare $context notification email for document id " . $document->getId());
        
        $authorAddresses = array();
        $authors = array();
        $title = "";

        $personAuthors = $document->getPersonAuthor();
        if (!empty($personAuthors)) {
            $index = 0;
            foreach ($personAuthors as $author) {
                // TODO Komma nur wenn FirstName present
                $name = trim($author->getLastName() . ", " . $author->getFirstName());
                array_push($authors, $name);
                if ($context == self::PUBLICATION) {
                    $email = trim($author->getEmail());
                    if (!empty($email) && (empty($notifyAuthors) || (isset($notifyAuthors[$index])
                                && $notifyAuthors[$index]))) {
                        array_push($authorAddresses, array( "name" => $name, "address" => $email));
                    }
                }
                $index++;
            }
        }

        // TODO Funktionalität existiert bereits (Documents Helper oder so)
        $titlesMain = $document->getTitleMain();
        if (!empty($titlesMain)) {
            // ermittle (den ersten) TitleMain in Dokumentsprache
            $language = $document->getLanguage();
            foreach ($titlesMain as $titleMain) {
                if ($titleMain->getLanguage() == $language) {
                    $title = trim($titleMain->getValue());
                    break;
                }
            }
        }
        
        $this->scheduleNotification(
            $this->getMailSubject($context, $document->getId(), $authors, $title),
            $this->getMailBody($context, $document->getId(), $authors, $title, $url),
            $this->getRecipients($context, $authorAddresses, $document, $notifySubmitter)
        );

        $this->_logger->info("$context notification mail creation was completed successfully");
    }

    private function getMailSubject($context, $docId, $authors, $title) {
        $authorString = "";
        for ($i = 0; $i < count($authors); $i++) {
            if ($i > 0) {
                $authorString .= " ; ";
            }
            $authorString .= $authors[$i];
        }
        if ($authorString == "") {
            $authorString = "n/a";
        }
        if ($title == "") {
            $title = "n/a";
        }
        if ($context == self::SUBMISSION && isset($this->_config->notification->document->submitted->subject)) {
            return sprintf($this->_config->notification->document->submitted->subject, $docId, $authorString, $title);
        }
        if ($context == self::PUBLICATION && isset($this->_config->notification->document->published->subject)) {
            return sprintf($this->_config->notification->document->published->subject, $docId, $authorString, $title);
        }
        $this->_logger->err("could not construct mail subject based on application configuration");
    }

    private function getMailBody($context, $docId, $authors, $title, $url) {
        if ($context == self::SUBMISSION && isset($this->_config->notification->document->submitted->template)) {
            return $this->getTemplate(
                $this->_config->notification->document->submitted->template, $docId, $authors,
                $title, $url
            );
        }
        if ($context == self::PUBLICATION && isset($this->_config->notification->document->published->template)) {
            return $this->getTemplate(
                $this->_config->notification->document->published->template, $docId, $authors,
                $title, $url
            );
        }
    }

    private function getTemplate($template, $docId, $authors, $title, $url) {
        $templateFileName = APPLICATION_PATH . '/application/configs/mail_templates/' . $template;
        if (!is_file($templateFileName)) {
            $this->_logger->err(
                "could not find mail template based on application configuration: '$templateFileName'"
                . ' does not exist or is not readable'
            );
            return;
        }
        ob_start();
        extract(
            array(            
            "authors" => $authors,
            "title" => $title,
            "docId" => $docId,
            "url" => $url
            )
        );
        require($templateFileName);
        $body = ob_get_contents();
        ob_end_clean();
        return $body;
    }

    private function getRecipients($context, $authorAddresses = null, $document = null, $notifySubmitter = true) {
        $addresses = array();

        switch ($context) {
            case self::SUBMISSION:
                if (isset($this->_config->notification->document->submitted->email)) {
                    $addresses = $this->buildAddressesArray(
                        $context,
                        $this->_config->notification->document->submitted->email
                    );
                }
                break;

            case self::PUBLICATION:
                if (isset($this->_config->notification->document->published->email)) {
                    $addresses = $this->buildAddressesArray(
                        $context,
                        $this->_config->notification->document->published->email
                    );
                }

                for ($i = 0; $i < count($authorAddresses); $i++) {
                    $authorAddress = $authorAddresses[$i];
                    array_push($addresses, $authorAddress);
                    $this->_logger->debug(
                        "send $context notification mail to author " . $authorAddress['address']
                        . " (" . $authorAddress['name'] . ")"
                    );
                }

                if ($notifySubmitter && !is_null($document)) {
                    $submitter = $document->getPersonSubmitter();
                    if (!empty($submitter)) {
                        $name = trim($submitter[0]->getLastName() . ", " . $submitter[0]->getFirstName());
                        $email = trim($submitter[0]->getEmail());
                        if (!empty($email)) {
                            array_push($addresses, array( "name" => $name , "address" => $email));
                            $this->_logger->debug("send $context notification mail to submitter $email ($name)");
                        }
                    }
                }
                break;

            default:
                $addresses = null;
                break;
        }

        return $addresses;
    }

    private function buildAddressesArray($context, $emails) {
        $addresses = array();

        if (strlen(trim($emails)) > 0) {
            foreach (explode(",", $emails) as $address) {
                $address = trim($address);
                $this->_logger->debug("send $context notification mail to $address");
                array_push($addresses, array("name" => $address, "address" => $address));
            }
        }

        return $addresses;
    }

    private function validateContext($context) {
        if ($context == self::SUBMISSION) {
            return isset($this->_config->notification->document->submitted->enabled)
                    && $this->_config->notification->document->submitted->enabled == 1;
        }
        if ($context == self::PUBLICATION) {
            return isset($this->_config->notification->document->published->enabled)
                    && $this->_config->notification->document->published->enabled == 1;;
        }
        $this->_logger->err("Email notification mechanism is not supported for context '$context'");
        return false;
    }

    private function scheduleNotification($subject, $message, $recipients) {
        if (empty($recipients)) {
            $this->_logger->warn("No recipients could be determined for email notification: skip operation");
            return;
        }

        $addressesUsed = array();

        foreach ($recipients as $recipient) {
            if (!in_array($recipient['address'], $addressesUsed)) {
                $job = new Opus_Job();
                $job->setLabel(Opus_Job_Worker_MailNotification::LABEL);
                $job->setData(
                    array(
                    'subject' => $subject,
                    'message' => $message,
                    'users' => array($recipient)
                    )
                );

                if (isset($this->_config->runjobs->asynchronous) && $this->_config->runjobs->asynchronous) {
                    // Queue job (execute asynchronously)
                    // skip creating job if equal job already exists
                    if (true === $job->isUniqueInQueue()) {
                        $job->store();
                    }
                }
                else {
                    // Execute job immediately (synchronously)
                    try {
                        $mail = new Opus_Job_Worker_MailNotification($this->_logger, false);
                        $mail->work($job);
                    }
                    catch (Exception $exc) {
                        $this->_logger->err("Email notification failed: ".$exc);
                    }
                }

                array_push($addressesUsed, $recipient['address']);
            }
        }

    }
}
