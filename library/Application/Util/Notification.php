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
 * @copyright   Copyright (c) 2012-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 *
 * TODO remove concept of 'context' - class should not implement different context variations (use OO principles)
 */
class Application_Util_Notification extends Application_Model_Abstract
{

    public function __construct($logger = null, $config = null)
    {
        $this->setConfig($config);
        $this->setLogger($logger);
    }

    /**
     *
     * @param Opus_Document $document das Dokument auf das sich die Notifizierung bezieht
     * @param String $url vollständiger Deeplink, der in der Mail angezeigt werden soll
     * @param boolean $notifySubmitter Wenn false, wird der Submitter nicht notifiziert
     * @param array $notifyAuthors Bitmaske, die für jeden Autor (über den Index referenziert) angibt, ob ihm/ihr eine
     *                             E-Mail gesendet werden kann (wenn false, dann wird keine Notifizierung versendet)
     *
     * TODO this class should not collect recipients on its own -> recipients should be provided
     */
    public function prepareMail($document, $url, $notifySubmitter = true, $notifyAuthors = [])
    {
        $logger = $this->getLogger();

        $logger->info("prepare notification email for document id " . $document->getId());

        $authorAddresses = [];
        $authors = $this->getAuthors($document);
        $title = $document->getMainTitle();

        $this->scheduleNotification(
            $this->getMailSubject($document->getId(), $authors, $title),
            $this->getMailBody($document->getId(), $authors, $title, $url),
            $this->getRecipients($authorAddresses, $document, $notifySubmitter)
        );

        $logger->info("notification mail creation was completed successfully");
    }

    /**
     * @param $document
     * @param $url
     * @param $recipients
     *
     * TODO this function is only used for PublicatioNotification at the moment - cleanup!
     */
    public function prepareMailFor($document, $url, $recipients)
    {
        $logger = $this->getLogger();

        $logger->info("prepare notification email for document id " . $document->getId());

        $authors = $this->getAuthors($document);

        $title = $document->getMainTitle();

        // TODO currently we need to convert between the old and new array structure
        // TODO the components and interfaces involved need to be defined clearly

        $converted = [];

        foreach ($recipients as $address => $recipient) {
            $entry = [];
            $entry['address'] = $address;

            if (is_array($recipient['name'])) {
                $entry['name'] = $recipient['name'][0]; // TODO only use name of first address occurence
            } else {
                $entry['name'] = $recipient['name'];
            }
        }

        $this->scheduleNotification(
            $this->getMailSubject($document->getId(), $authors, $title),
            $this->getMailBody($document->getId(), $authors, $title, $url),
            $converted
        );

        $logger->info("notification mail creation was completed successfully");
    }

    public function getAuthors($document)
    {
        $authors = [];

        $personAuthors = $document->getPersonAuthor();
        if (!empty($personAuthors)) {
            foreach ($personAuthors as $author) {
                // TODO Komma nur wenn FirstName present
                $name = trim($author->getLastName() . ", " . $author->getFirstName());
                array_push($authors, $name);
            }
        }

        return $authors;
    }

    private function getMailSubject($docId, $authors, $title)
    {
        $logger = $this->getLogger();

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

        $subjectTemplate = $this->getSubjectTemplate();

        if (strlen(trim($subjectTemplate)) > 0) {
            return sprintf($subjectTemplate, $docId, $authorString, $title);
        } else {
            $logger->err("could not construct mail subject based on application configuration");
            return '';
        }
    }

    public function getSubjectTemplate()
    {
        $config = $this->getConfig();

        if (isset($config->notification->document->submitted->subject)) {
            return $config->notification->document->submitted->subject;
        }
    }

    public function getMailBody($docId, $authors, $title, $url)
    {
        $config = $this->getConfig();

        if (isset($config->notification->document->submitted->template)) {
            return $this->getTemplate(
                $config->notification->document->submitted->template, $docId, $authors,
                $title, $url
            );
        }
    }

    public function getTemplate($template, $docId, $authors, $title, $url)
    {
        $templateFileName = APPLICATION_PATH . '/application/configs/mail_templates/' . $template;
        if (!is_file($templateFileName)) {
            $this->getLogger()->err(
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

    public function getRecipients($authorAddresses = null, $document = null, $notifySubmitter = true)
    {
        $config = $this->getConfig();

        $addresses = [];

        if (isset($config->notification->document->submitted->email)) {
            $addresses = $this->buildAddressesArray(
                $config->notification->document->submitted->email
            );
        }

        return $addresses;
    }

    public function buildAddressesArray($emails)
    {
        $addresses = [];

        if (strlen(trim($emails)) > 0) {
            foreach (explode(",", $emails) as $address) {
                $address = trim($address);
                $this->getLogger()->debug("send notification mail to $address");
                array_push($addresses, ["name" => $address, "address" => $address]);
            }
        }

        return $addresses;
    }

    /**
     * @return bool
     * @throws Zend_Exception
     *
     * TODO should this class be responsible for this decision?
     */
    public function isEnabled()
    {
        $config = $this->getConfig();

        return isset($config->notification->document->published->enabled)
                && $config->notification->document->published->enabled == 1;
    }

    /**
     * @param $subject
     * @param $message
     * @param $recipients
     * @throws Opus_Model_Exception
     *
     * TODO the code here should not decide if synchronous or asynchronous - create a job and go (either way)
     * TODO the code here should not filter recipients (that should have happened earlier)
     */
    private function scheduleNotification($subject, $message, $recipients)
    {
        if (empty($recipients)) {
            $this->getLogger()->warn("No recipients could be determined for email notification: skip operation");
            return;
        }

        $addressesUsed = [];

        foreach ($recipients as $recipient) {
            // only send if email address has not been used before
            if (!in_array($recipient['address'], $addressesUsed)) {
                $job = new Opus_Job();
                $job->setLabel(Opus_Job_Worker_MailNotification::LABEL);
                $job->setData([
                    'subject' => $subject,
                    'message' => $message,
                    'users' => [$recipient]
                ]);

                $config = $this->getConfig();

                if (isset($config->runjobs->asynchronous) && $config->runjobs->asynchronous) {
                    // Queue job (execute asynchronously)
                    // skip creating job if equal job already exists
                    if (true === $job->isUniqueInQueue()) {
                        $job->store();
                    }
                } else {
                    // Execute job immediately (synchronously)
                    try {
                        $mail = new Opus_Job_Worker_MailNotification($this->getLogger(), false);
                        $mail->work($job);
                    }
                    catch (Exception $exc) {
                        $this->getLogger()->err("Email notification failed: " . $exc);
                    }
                }

                $addressesUsed[] = $recipient['address'];
            }
        }
    }
}
