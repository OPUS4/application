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
    private $documentsHelper;

    /**
     * Initializes controller.
     */
    public function init() {
        parent::init();
        $this->documentsHelper = $this->_helper->getHelper('Documents');
    }

    /**
     * Switches the status of a document to published.
     */
    public function publishAction() {
        $docId = $this->getRequest()->getParam('docId');

        // Check if document identifier is valid
        if (!$this->documentsHelper->isValidId($docId)) {
            return $this->_redirectTo('index', array('failure' =>
                $this->view->translate('admin_document_error_novalidid')),
                    'documents', 'admin');
        }

        $doc = new Opus_Document($docId);

        if ($doc->getServerState() === 'published') {
            return $this->_redirectTo('index', array('failure' =>
                $this->view->translate(
                        'admin_document_error_already_published')),
                    'document', 'admin', array('id' => $docId));
        }

        switch ($this->__confirm($docId, 'publish')) {
            case 'YES':
                $doc->setServerState('published');
                //        $doc->setServerDatePublished(date('Y-m-d'));
                //        $doc->setServerDatePublished(date('c'));
                $date = new Zend_Date();
                $doc->setServerDatePublished(
                        $date->get('yyyy-MM-ddThh:mm:ss') . 'Z');
                $doc->store();

                $message = $this->view->translate('document_published', $docId);
                return $this->_redirectTo('index', $message, 'document',
                        'admin', array('id' => $docId));
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
     * Unpublishes a document (sets ServerState to unpublished).
     */
    public function unpublishAction() {
        $docId = $this->getRequest()->getParam('docId');

        // Check if document identifier is valid
        if (!$this->documentsHelper->isValidId($docId)) {
            return $this->_redirectTo('index', array('failure' =>
                $this->view->translate('admin_document_error_novalidid')),
                    'documents', 'admin');
        }

        $doc = new Opus_Document($docId);

        if ($doc->getServerState() === 'unpublished') {
            return $this->_redirectTo('index', array('failure' =>
                $this->view->translate(
                        'admin_document_error_already_unpublished')),
                    'document', 'admin', array('id' => $docId));
        }

        switch ($this->__confirm($docId, 'unpublish')) {
            case 'YES':
                $doc->setServerState('unpublished');
                $doc->store();

                $message = $this->view->translate('document_unpublished',
                        $docId);
                return $this->_redirectTo('index', $message, 'document',
                        'admin', array('id' => $docId));
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
     * Deletes a document (sets state to deleted).
     */
    public function deleteAction() {
        $docId = $this->getRequest()->getParam('docId');

        // Check if document identifier is valid
        if (!$this->documentsHelper->isValidId($docId)) {
            return $this->_redirectTo('index', array('failure' =>
                $this->view->translate('admin_document_delete_novalidid')),
                    'documents', 'admin');
        }

        $doc = new Opus_Document($docId);

        if ($doc->getServerState() === 'deleted') {
            return $this->_redirectTo('index', array('failure' =>
                $this->view->translate('admin_document_error_already_deleted')),
                    'document', 'admin', array('id' => $docId));
        }

        switch ($this->__confirm($docId, 'delete')) {
            case 'YES':
                $doc->delete();
                return $this->_redirectTo('index', $this->view->translate(
                        'admin_documents_delete_success'), 'document',
                        'admin', array('id' => $docId));
                break;
            case 'NO':
                return $this->_redirectTo('index', null, 'document', 'admin',
                        array('id' => $docId));

                break;
            default:
                break;
        }
    }

    /**
     * Deletes a document permanently (removes it from database and disk).
     */
    public function permanentdeleteAction() {
        $docId = $this->getRequest()->getParam('docId');

        // Check if document identifier is valid
        if (!$this->documentsHelper->isValidId($docId)) {
            return $this->_redirectTo('index', array('failure' =>
                $this->view->translate('admin_document_error_novalidid')),
                    'documents', 'admin');
        }

        $doc = new Opus_Document($docId);

        switch ($this->__confirm($docId, 'permanentdelete')) {
            case 'YES':
                try {
                    $doc->deletePermanent();
                }
                catch (Exception $e) {
                    $this->_redirectTo('index', array('failure' =>
                        $e->getMessage()), 'documents', 'admin');
                }
                return $this->_redirectTo('index', $this->view->translate(
                        'admin_documents_permanent_delete_success'),
                        'documents', 'admin');
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
    private function __confirm($docId, $action) {
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
        $this->view->title = $this->view->translate('admin_doc_' . $action);
        $this->view->text = $this->view->translate(
                'admin_doc_' . $action . '_sure', $docId);
        $yesnoForm = $this->__getConfirmationForm($docId, $action);
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
    private function __getConfirmationForm($docId, $action) {
        $yesnoForm = new Admin_Form_YesNoForm();
        $idElement = new Zend_Form_Element_Hidden('id');
        $idElement->setValue($docId);
        $yesnoForm->addElement($idElement);
        $yesnoForm->setAction($this->view->url(
                array("controller" => "workflow", "action" => $action)));
        $yesnoForm->setMethod('post');
        return $yesnoForm;
    }

}

