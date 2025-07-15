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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 *
 * TODO fix processing of notification selection
 * TODO verify previous checkbox results
 */

use Opus\Common\DocumentInterface;

/**
 * Controller handles transitions of documents between states.
 *
 * TODO use id instead of docId like DocumentController
 */
class Admin_WorkflowController extends Application_Controller_Action
{
    /**
     * Helper for verifying document IDs.
     *
     * @var Application_Controller_Action_Helper_Documents
     */
    private $documentsHelper;

    /**
     * Helper for workflow functionality.
     *
     * @var Application_Controller_Action_Helper_Workflow
     */
    private $workflowHelper;

    /** @var bool */
    private $confirmChanges = true;

    /**
     * Initializes controller.
     */
    public function init()
    {
        parent::init();

        $this->documentsHelper = $this->_helper->getHelper('Documents');
        $this->workflowHelper  = $this->_helper->getHelper('Workflow');

        $config = $this->getConfig();

        if (isset($config->confirmation->document->statechange->enabled)) {
            $this->confirmChanges = filter_var($config->confirmation->document->statechange->enabled, FILTER_VALIDATE_BOOLEAN);
        } else {
            $this->confirmChanges = true;
        }
    }

    /**
     * Switches the status of a document to target state.
     */
    public function changestateAction()
    {
        $docId       = $this->getRequest()->getParam('docId');
        $targetState = $this->getRequest()->getParam('targetState');

        $document = $this->documentsHelper->getDocumentForId($docId);

        // Check if document identifier is valid
        if (! isset($document)) {
            $this->_helper->Redirector->redirectTo(
                'index',
                [
                    'failure' => $this->view->translate(
                        'admin_document_error_novalidid'
                    ),
                ],
                'documents',
                'admin'
            );
            return;
        }

        // Check if valid target state
        if (! $this->workflowHelper->isValidState($targetState)) {
            $this->_helper->Redirector->redirectTo(
                'index',
                [
                    'failure' => $this->view->translate(
                        'admin_workflow_error_invalidstate'
                    ),
                ],
                'document',
                'admin',
                ['id' => $docId]
            );
            return;
        }

        // Check if allowed target state
        if (! $this->workflowHelper->isTransitionAllowed($document, $targetState)) {
            $this->_helper->Redirector->redirectTo(
                'index',
                [
                    'failure' => $this->view->translate(
                        'admin_workflow_error_illegal_transition',
                        $targetState
                    ),
                ],
                'document',
                'admin',
                ['id' => $docId]
            );
            return;
        }

        // Check if document is already in target state
        if ($document->getServerState() === $targetState) {
            // if defined used custom message for state, other use common key
            $key = 'admin_workflow_error_already_' . $targetState;
            if (! $this->view->translate()->getTranslator()->isTranslated($key)) {
                $key = 'admin_workflow_error_alreadyinstate';
            }
            $this->_helper->Redirector->redirectTo(
                'index',
                ['failure' => $this->view->translate($key, $targetState)],
                'document',
                'admin',
                ['id' => $docId]
            );
            return;
        }

        if ($this->confirmChanges) {
            if ($this->getRequest()->isPost()) {
                $form    = $this->getConfirmationForm($document, $targetState);
                $sureyes = $this->getRequest()->getPost('sureyes');
                if ($form->isValid($this->getRequest()->getPost()) && isset($sureyes) === true) {
                    $this->changeState($document, $targetState, $form);
                    return;
                }
                $this->_helper->Redirector->redirectTo(
                    'index',
                    null,
                    'document',
                    'admin',
                    ['id' => $docId]
                );
                return;
            }

            // show confirmation page
            $this->view->documentAdapter = new Application_Util_DocumentAdapter($this->view, $document);
            $this->view->title           = $this->view->translate('admin_workflow_' . $targetState);
            $this->view->text            = $this->view->translate('admin_workflow_' . $targetState . '_sure', [$docId]);
            $this->view->form            = $this->getConfirmationForm($document, $targetState);
        } else {
            $this->changeState($document, $targetState);
        }
    }

    /**
     * @param DocumentInterface $document
     * @param string            $targetState
     * @param Zend_Form|null    $form
     */
    private function changeState($document, $targetState, $form = null)
    {
        try {
            $this->workflowHelper->changeState($document, $targetState);

            // TODO this should be configurable/extendable for any event
            if ($targetState === 'published') {
                // TODO notification code should be a separate class/module/extension
                $this->sendNotification($document, $form);
            }
        } catch (Exception $e) {
            $this->_helper->Redirector->redirectTo(
                'index',
                ['failure' => $e->getMessage()],
                'documents',
                'admin'
            );
            return;
        }

        $key = 'admin_workflow_' . $targetState . '_success';
        if (! $this->view->translate()->getTranslator()->isTranslated($key)) {
            $key = 'admin_workflow_success';
        }
        $message = $this->view->translate($key, $document->getId(), $targetState);

        if ($targetState === 'removed') {
            $this->_helper->Redirector->redirectTo('index', $message, 'documents', 'admin');
            return;
        }

        $this->_helper->Redirector->redirectTo(
            'index',
            $message,
            'document',
            'admin',
            ['id' => $document->getId()]
        );
    }

    /**
     * @param DocumentInterface $document
     * @param Zend_Form|null    $form
     *
     * TODO get recipients from form
     * TODO dryrun mode for notifications for testing
     */
    private function sendNotification($document, $form = null)
    {
        $notification = new Application_Util_PublicationNotification();

        $url = $this->view->url(
            [
                "module"     => "frontdoor",
                "controller" => "index",
                "action"     => "index",
                "docId"      => $document->getId(),
            ],
            null,
            true
        );

        $post = $this->getRequest()->getPost();

        // TODO remove dependency on form (form only available if confirmation is activated)
        if ($form !== null) {
            $recipients = $form->getSelectedRecipients($document, $post);
        } else {
            $form       = $this->getConfirmationForm($document, 'published'); // TODO move recipient code out of form
            $recipients = $form->getRecipients($document);
        }

        $notification->prepareMailFor(
            $document,
            $this->view->serverUrl() . $url,
            $recipients
        );
    }

    /**
     * Returns form for asking yes/no question like 'Delete file?'.
     *
     * @param DocumentInterface $document
     * @param string            $targetState Target action that needs to be confirmed
     * @return Admin_Form_YesNoForm
     */
    private function getConfirmationForm($document, $targetState)
    {
        $form = new Admin_Form_WorkflowNotification($targetState);

        $form->populateFromModel($document);

        $form->setAction($this->view->url([
            'controller'  => 'workflow',
            'action'      => 'changestate',
            'targetState' => $targetState,
        ]));
        $form->setMethod('post');

        return $form;
    }
}
