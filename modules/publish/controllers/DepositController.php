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
 * @package     Module_Publish
 * @author      Susanne Gottwald <gottwald@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Main entry point for this module.
 *
 * @category    Application
 * @package     Module_Publish
 */
class Publish_DepositController extends Controller_Action {

    public $depositData = array();
    public $log;
    public $session;
    public $document;

    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array()) {
        $this->log = Zend_Registry::get('Zend_Log');
        $this->session = new Zend_Session_Namespace('Publish');

        parent::__construct($request, $response, $invokeArgs);
    }

    /**
     * stores a delivered form as document in the database
     * uses check_array
     */
    public function depositAction() {

        if ($this->getRequest()->isPost() !== true) {
            return $this->_redirectTo('index', '', 'index');
        }
        
        //post content is just checked for buttons
        $post = $this->getRequest()->getPost();
        if (array_key_exists('back', $post)) {
            return $this->_forward('check', 'form');
        } else if (array_key_exists('abort', $post)) {
            if (isset($this->session->documentId)) {
                $this->document = new Opus_Document($this->session->documentId);
                $this->document->deletePermanent();
            }
            return $this->_redirectTo('index', '', 'index');
        }
        
        $this->view->title = $this->view->translate('publish_controller_index');
        $this->view->subtitle = $this->view->translate('publish_controller_deposit_successful');

        //deposit data is coming from the session
        if (isset($this->session->elements)) {
            foreach ($this->session->elements AS $element) {
                $this->depositData[$element['name']] = $element['value'];
                $this->log->debug('SAVING DATA: ' . $element['name'] . ' + ' . $element['value']);
            }
        }

        if (isset($this->depositData['send']))
            unset($this->depositData['send']);

        $depositData = new Publish_Model_Deposit($this->depositData);
        $this->document = $depositData->getDocument();
        $this->document->setServerState('unpublished');
        $this->session->documentId = $this->document->store();
        $docId = $this->session->documentId;

        $this->log->info("Document $docId was sucessfully stored!");

        // Build URLs for the publish-notification-mail.
        $fullDocUrl = $this->__getDocumentUrl($docId);
        $reviewUrl = $this->view->serverUrl() . $this->view->url(array(
                    'module' => 'review',
                    'controller' => 'index',
                    'action' => 'index'));
        $adminEditUrl = $this->view->serverUrl() . $this->view->url(array(
                    'module' => 'admin',
                    'controller' => 'documents',
                    'action' => 'edit',
                    'id' => $docId));


        $this->log->debug("fullDocUrl:   $fullDocUrl");
        $this->log->debug("reviewUrl:    $reviewUrl");
        $this->log->debug("adminEditUrl: $adminEditUrl");

        $subject = $this->view->translate('mail_publish_notification_subject', $docId);
        $message = $this->view->translate('mail_publish_notification', $fullDocUrl, $reviewUrl, $adminEditUrl);

        $this->log->debug("sending email (subject): $subject");
        $this->log->debug("sending email (body):    \n$message\n-- end email.");
        $this->__scheduleNotification($subject, $message);
        
        $targetAction = 'confirm';
        $targetController = 'deposit';
        $targetModule = 'publish';

        $config = Zend_Registry::get('Zend_Config');
        if (isset($config) and isset($config->publish->depositComplete)) {
            $targetAction = $config->publish->depositComplete->action;
            $targetController = $config->publish->depositComplete->controller;
            $targetModule = $config->publish->depositComplete->module;
        }

        return $this->_redirectToAndExit($targetAction, null, $targetController, $targetModule);
    }

    /**
     * Shows a confirmation for the user, when the publication process is
     * finished.
     */
    public function confirmAction() {
        $this->view->docId = $this->session->documentId;

        if (true === Opus_Security_Realm::getInstance()->check('clearance')) {
            $this->view->showFrontdoor = true;
        }
        //unset all possible session content
        $this->session->unsetAll();

        return;
    }

    /**
     * Schedules notifications for referees.
     * @param <type> $projects
     */
    private function __scheduleNotification($subject, $message) {

        $job = new Opus_Job();
        $job->setLabel(Opus_Job_Worker_MailPublishNotification::LABEL);
        $job->setData(array(
            'subject' => $subject,
            'message' => $message,
            'users' => 'admin',
            'docId' => $this->session->documentId
        ));

        if (isset($config->runjobs->asynchronous) && $config->runjobs->asynchronous) {
            // Queue job (execute asynchronously)
            // skip creating job if equal job already exists
            if (true === $job->isUniqueInQueue()) {
                $job->store();
            }
        } else {
            // Execute job immediately (synchronously)
            $mail = new Opus_Job_Worker_MailPublishNotification($this->log);
            $mail->work($job);
        }
    }

    /**
     * Return frontdoor URL for document.
     * @param <type> $docId
     * @return <type>
     *
     * FIXME move into controller or view helper
     */
    private function __getDocumentUrl($docId) {
        $url_frontdoor = array(
            'module' => 'frontdoor',
            'controller' => 'index',
            'action' => 'index',
            'docId' => $docId
        );

        $baseUrl = $this->view->serverUrl(); // TODO doesn't work

        return $baseUrl . $this->view->url($url_frontdoor, 'default', true);
    }

}

