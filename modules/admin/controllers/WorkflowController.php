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
 * @package     Module_Admin
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Controller handles transitions of documents between states.
 */
class Admin_WorkflowController extends Controller_Action {

    /**
     * Helper for verifying document IDs.
     * @var Controller_Helper_Documents
     */
    private $__documentsHelper;

    /**
     * Helper for workflow functionality.
     * @var Controller_Helper_Workflow
     */
    private $__workflowHelper;

    /**
     * Initializes controller.
     */
    public function init() {
        parent::init();
        $this->__documentsHelper = $this->_helper->getHelper('Documents');
        $this->__workflowHelper = $this->_helper->getHelper('Workflow');
    }


    /**
     * Switches the status of a document to target state.
     */
    public function changestateAction() {
        $docId = $this->getRequest()->getParam('docId');
        $targetState = $this->getRequest()->getParam('targetState');

        $document = $this->__documentsHelper->getDocumentForId($docId);

        // Check if document identifier is valid
        if (!isset($document)) {
            return $this->_redirectTo('index', array('failure' =>
                $this->view->translate('admin_document_error_novalidid')),
                    'documents', 'admin');
        }

        // Check if valid target state
        if (!$this->__workflowHelper->isValidState($targetState)) {
            return $this->_redirectTo('index', array('failure' =>
                $this->view->translate('admin_workflow_error_invalidstate')),
                    'document', 'admin', array('id' => $docId));
        }

        // Check if allowed target state
        if (!$this->__workflowHelper->isTransitionAllowed($document, $targetState)) {
            return $this->_redirectTo('index', array('failure' =>
                $this->view->translate('admin_workflow_error_illegal_transition',
                        $targetState)), 'document', 'admin', array('id' => $docId));
        }

        // Check if document is already in target state
        if ($document->getServerState() === $targetState) {
            // if defined used custom message for state, other use common key
            $key = 'admin_workflow_error_already_' . $targetState;
            if (!$this->view->translate()->getTranslator()->isTranslated($key)) {
                $key = 'admin_workflow_error_alreadyinstate';
            }
            $message = $this->view->translate($key, $targetState);

            return $this->_redirectTo('index', array('failure' => $message),
                    'document', 'admin', array('id' => $docId));
        }

        switch ($this->__confirm($docId, $targetState)) {
            case 'YES':
                try {
                    $this->__workflowHelper->changeState($document, $targetState);
                    if ($targetState == 'published') {
                        $notification = new Util_Notification();
                        $url = $this->view->url(
                            array(
                                "module" => "frontdoor",
                                "controller" => "index",
                                "action" => "index",
                                "docId" => $document->getId()
                            ),
                            null,
                            true);
                        $notification->prepareMail($document, Util_Notification::PUBLICATION, $this->view->serverUrl() . $url);
                    }
                }
                catch (Exception $e) {
                    $this->_redirectTo('index', array('failure' =>
                        $e->getMessage()), 'documents', 'admin');
                }

                $key = 'admin_workflow_' . $targetState . '_success';

                if (!$this->view->translate()->getTranslator()->isTranslated($key)) {
                    $key = 'admin_workflow_success';
                }

                $message = $this->view->translate($key, $docId, $targetState);

                if ($targetState === 'removed') {
                    return $this->_redirectTo('index', $message, 'documents',
                            'admin');
                }
                else {
                    return $this->_redirectTo('index', $message, 'document',
                            'admin', array('id' => $docId));
                }
                break;
            case 'NO':
                $this->_redirectTo('index', null, 'document', 'admin',
                        array('id' => $docId));
                break;
            default:
                break;
        }
    }

    /**
     * Prepare or processes POST from confirmation page.
     * @param type $docId
     * @param type $action
     * @return type
     */
    private function __confirm($docId, $targetState) {
        // Check if request is POST and if yes check for user response
        if ($this->getRequest()->isPost()) {
            $sureyes = $this->getRequest()->getPost('sureyes');
            $sureno = $this->getRequest()->getPost('sureno');

            if (isset($sureyes) === true) {
                return 'YES';
            }
            else if (isset($sureno) === true) {
                return 'NO';
            }
        }

        // show confirmation page if not a POST and if not answered YES or NO
        $this->view->title = $this->view->translate('admin_workflow_' . $targetState);
        $this->view->text = $this->view->translate(
                'admin_workflow_' . $targetState . '_sure', $docId);
        $yesnoForm = $this->__getConfirmationForm($docId, $targetState);
        $this->view->form = $yesnoForm;
        $this->renderScript('document/confirm.phtml');
    }

    /**
     * Returns form for asking yes/no question like 'Delete file?'.
     *
     * @param int $id Document identifier
     * @param string $action Target action that needs to be confirmed
     * @return Admin_Form_YesNoForm
     */
    private function __getConfirmationForm($docId, $targetState) {
        $yesnoForm = new Admin_Form_YesNoForm();
        $idElement = new Zend_Form_Element_Hidden('id');
        $idElement->setValue($docId);
        $yesnoForm->addElement($idElement);
        $yesnoForm->setAction($this->view->url(
                array('controller' => 'workflow', 'action' => 'changestate',
                    'targetState' => $targetState)));
        $yesnoForm->setMethod('post');
        return $yesnoForm;
    }

}

