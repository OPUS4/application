<?php
/*
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
 * @category    TODO
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Mails notifications for newly published documents.
 */
class Mail_PublishNotification
{

    private $docId = null;

    private $config;

    private $logger;

    private $view;

    /**
     * Constructs an instance of publish notification.
     * @param <type> $docId
     * @param <type> $view
     */
    public function __construct($docId, $view) {
        $this->docId = $docId;
        $this->view = $view;
        $this->_init();
    }

    /**
     * Initializes the instance.
     */
    protected function _init() {
        $this->config = Zend_Registry::get('Zend_Config');
        $this->logger = zend_Registry::get('Zend_Log');
    }

    /**
     * Sends the publish notifications to the recipients.
     * @return boolean
     */
    public function send() {
        $from = $this->getFrom();
        $fromName = $this->getFromName();
        $subject = $this->getSubject();
        $messageBody = $this->getMessageBody();
        $recipient = $this->getRecipients();

        $mailSendMail = new Opus_Mail_SendMail();

        try {
            $this->logger->debug('Send publish notification.');
            $this->logger->debug('address = ' . $from);
            $mailSendMail->sendMail(
                    $from, $fromName, $subject, $messageBody, $recipient);
        } catch (Exception $e) {
            $this->logger->err($e);
            return false;
        }

        return true;
    }

    /**
     * Returns the 'from' address for notification.
     *
     * @return string
     */
    public function getFrom() {
        if (isset($this->config->mail->opus->address)) {
            $from = $this->config->mail->opus->address;
        }

        return $from;
    }

    /**
     * Returns the 'from name' for notification.
     * @return string
     */
    public function getFromName() {
        if (isset($this->config->mail->opus->name)) {
            $fromName = $this->config->mail->opus->name;
        }

        return $fromName;
    }

    /**
     * Returns subject for publish notification.
     * @return string
     */
    public function getSubject() {
        return 'New document published (' . $this->docId . ')';
    }

    /**
     * Return message body for notifications.
     *
     * @return string
     *
     * TODO use script to render message body
     */
    public function getMessageBody() {
        $message = "A new document was published" . "\n\n";

        $message .= $this->getDocumentUrl($this->docId) . "\n\n";

        $message .= "Please review the document" . "\n\n";

        return $message;
    }

    /**
     * Returns URL for document.
     * @param <type> $docId
     * @return <type>
     */
    public function getDocumentUrl($docId) {
        $url_frontdoor = array(
            'module'     => 'frontdoor',
            'controller' => 'index',
            'action'     => 'index',
            'docId'      => $this->docId
        );

        $baseUrl = $this->view->serverUrl(); // TODO doesn't work

        return $baseUrl . $this->view->url($url_frontdoor, 'default', true);
    }

    /**
     * Returns recipients for publish notifications.
     *
     * @return array
     */
    public function getRecipients() {
        $recipients = array();

        $config = Zend_Registry::get('Zend_Config');

        $referees = $config->referees;

        if (!empty($referees)) {
            $index = 1;
            foreach ($referees as $name => $address) {
                $recipients[$index] = array('name' => $name, 'address' => $address);
                $index++;
            }
        }
        else {
            $recipients = null;
        }

        return $recipients;
    }

}
?>