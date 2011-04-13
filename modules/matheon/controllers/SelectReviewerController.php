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
 * @package     Module_Matheon
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Matheon_SelectReviewerController extends Controller_Action {

    public function init() {
        parent::init();

        $this->requirePrivilege('publish');
        $this->getHelper('MainMenu')->setActive('publish');
    }

    public function formAction() {
        // Get the document info from the session.
        $session = new Zend_Session_Namespace('Publish');

        if (!isset($session, $session->depositConfirmDocumentId)) {
            throw new Application_Exception('Cannot find document Id to print form for.');
        }

        // initialize document model
        $docId = $session->depositConfirmDocumentId;

        $documentModel = new Matheon_Model_Document($docId);
        $documentModel->requireServerState('unpublished');
        $documentModel->requireSubmitterIsCurrentUser();
        $this->view->docId = $docId;

        // Initialize form.
        $reviewerForm = new Matheon_Form_ChooseReviewer();
        $reviewerForm->setMethod('POST')
                ->setAction($this->_helper->url('form'))
                ->setReviewerOptions($this->__fetchReviewers());

        // Check data.
        $request = $this->getRequest();
        if (!$request->isPost() or !$reviewerForm->isValid( $request->getPost() )) {
            $this->view->reviewerForm = $reviewerForm;
            return;
        }

        // Process query.
        $reviewerId = $reviewerForm->getReviewerId();

        $documentModel->storeEnrichmentKeyValue('reviewer.user_id', $reviewerId);
        $documentModel->addReadFilePrivilege('guest');
        $documentModel->store();

        // Send publish notification.
        $reviewerAccount = new Opus_Account($reviewerId);
        $recipients = array_unique(array(
                    'admin',
                    $reviewerAccount->getLogin(),
                ));

        $this->_logger->debug('sending messages to users (' . implode(",", $recipients) . ')');
        if ($this->__sendPublishNotification($documentModel, $recipients)) {
            $this->view->success = true;
        }

        return $this->render('confirm');
    }

    /**
     *
     * @param Matheon_Model_Document $document
     * @param array $recipient
     * @return void
     */
    private function __sendPublishNotification($document, $recipient) {
        $config = Zend_Registry::getInstance()->get('Zend_Config');
        $serverUrl = $this->view->serverUrl();
        $baseUrlServer = $serverUrl . $this->getRequest()->getBaseUrl();
        $baseUrlFiles = $serverUrl . (isset($config, $config->deliver->url->prefix) ? $config->deliver->url->prefix : '/documents');

        $job = new Opus_Job();
        $job->setLabel(Opus_Job_Worker_MailPublishNotification::LABEL);
        $job->setData(array(
            'subject' => $document->renderPublishMailSubject(),
            'message' => $document->renderPublishMailBody($baseUrlServer, $baseUrlFiles),
            'users' => $recipient,
            'docId' => $document->getId(),
        ));


        //throw new Exception(var_export($job, true));

        if (isset($config->runjobs->asynchronous) && $config->runjobs->asynchronous) {
            // Queue job (execute asynchronously)
            // skip creating job if equal job already exists
            if (true === $job->isUniqueInQueue()) {
                $job->store();
            }
            return true;
        }

        // Execute job immediately (synchronously)
        $mail = new Opus_Job_Worker_MailPublishNotification($this->_logger);
        $mail->work($job);

        return true;
    }

    /**
     * Get a list of all accounts with reviewer role.
     *
     * @return array
     */
    private function __fetchReviewers() {
        $role = Opus_UserRole::fetchByName('reviewer');
        $reviewerSelect = array('' => '-- please choose --');

        foreach ($role->getAllAccountIds() AS $id) {
            $user = new Opus_Account($id);
            $login = $user->getLogin();

            if (is_null($user)) {
                $this->_logger->warn("-- skipping name: " . $login . " (user does not exist)");
                continue;
            }

            $key = $user->getId();
            $firstname = trim($user->getFirstName());
            $lastname = trim($user->getLastName());

            $displayValue = "--- user-id: " . $key . ' ---';
            if (!empty($firstname) or !empty($lastname)) {
                $displayValue = $lastname . ", " . $firstname;
            }
            else {
                $this->_logger->warn("-- incomplete name: " . $login . " (missing first/last name)");
            }

            $reviewerSelect[$key] = $displayValue;
        }

        asort($reviewerSelect);
        return $reviewerSelect;
    }


    public function debugAction() {
        $this->requirePrivilege('admin');

        $docId = $this->_getParam('docId');
        $document = new Opus_Document($docId);
        $document->setServerState('unpublished');

        $loggedUserModel = new Publish_Model_LoggedUser();
        $loggedUserId = $loggedUserModel->getUserId();

        $document->addEnrichment()
                ->setKeyName('submitter.user_id')
                ->setValue($loggedUserId);

        $document->store();

        $session = new Zend_Session_Namespace('Publish');
        $session->depositConfirmDocumentId = $docId;
    }
}
