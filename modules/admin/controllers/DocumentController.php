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
 * @author      Maximilian Salomon <salomon@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Controller for showing and editing a document in the administration.
 *
 * @category    Application
 * @package     Module_Admin
 */
class Admin_DocumentController extends Application_Controller_Action
{

    /**
     * Helper for verifying document IDs.
     * @var Application_Controller_Action_Helper_Documents
     */
    private $_documentsHelper;

    /**
     * Initializes controller.
     */
    public function init()
    {
        parent::init();
        $this->_documentsHelper = $this->_helper->getHelper('Documents');
    }

    /**
     * Produces metadata overview page of a document.
     * @return Opus_Document
     */
    public function indexAction()
    {
        $docId = $this->getRequest()->getParam('id');

        $document = $this->_documentsHelper->getDocumentForId($docId);

        if (isset($document)) {
            $this->view->document = $document;
            $this->view->documentAdapter = new Application_Util_DocumentAdapter($this->view, $document);

            $form = new Admin_Form_Document();
            $form->populateFromModel($document);
            $form->prepareRenderingAsView();

            $this->view->contentWrapperDisabled = true;
            $this->_helper->breadcrumbs()->setDocumentBreadcrumb($document);

            $this->renderForm(new Admin_Form_Wrapper($form));
        }
        else {
            // missing or bad parameter => go back to main page
            return $this->_helper->Redirector->redirectTo(
                'index', array('failure' =>
                $this->view->translate('admin_document_error_novalidid')),
                'documents', 'admin'
            );
        }
    }

    /**
     * Zeigt Metadaten-Formular an bzw. verarbeitet POST Requests vom Formular.
     *
     * TODO prüfen ob Form DocID mit URL DocID übereinstimmt
     */
    public function editAction()
    {
        $docId = $this->getRequest()->getParam('id');

        $document = $this->_documentsHelper->getDocumentForId($docId);

        if (!isset($document)) {
            return $this->_helper->Redirector->redirectTo(
                'index', array('failure' =>
                $this->view->translate('admin_document_error_novalidid')),
                'documents', 'admin'
            );
        }
        else {
            $editSession = new Admin_Model_DocumentEditSession($docId);

            if ($this->getRequest()->isPost()) {
                $data = $this->getRequest()->getPost();
                $data = $data['Document']; // 'Document' Form wraps actual metadata form

                $form = Admin_Form_Document::getInstanceFromPost($data, $document);
                $form->populate($data);

                // Use return value for decision how to continue
                $result = $form->processPost($data, $data);

                if (is_array($result)) {
                    $target = $result['target']; // TODO check if present
                    $result = $result['result']; // TODO check if present
                }

                switch ($result) {
                    case Admin_Form_Document::RESULT_SAVE:
                        if ($form->isValid($data)) {
                            // Formular ist korrekt; aktualisiere Dokument
                            $form->updateModel($document);

                            try {
                                $document->store();

                                // TODO redirect to Übersicht/Browsing/???
                                $message = $this->view->translate('admin_document_update_success');
                                return $this->_helper->Redirector->redirectTo(
                                    'index', $message, 'document', 'admin', array('id' => $docId)
                                );
                            }
                            catch (Exception $ex) {
                                $message = $this->view->translate('admin_document_error_exception_storing');
                                $message = sprintf($message, $ex->getMessage());
                                $form->setMessage($message);
                            }
                        }
                        else {
                            $form->setMessage($this->view->translate('admin_document_error_validation'));
                        }
                        break;

                    case Admin_Form_Document::RESULT_SAVE_AND_CONTINUE:
                        if ($form->isValid($data)) {
                            // Formular ist korrekt; aktualisiere Dokument
                            $form->updateModel($document);

                            // TODO handle exceptions
                            $document->store();
                        }
                        else {
                            // Zend_Debug::dump($form->getErrors());
                            $form->setMessage($this->view->translate('admin_document_error_validation'));
                        }
                        break;

                    case Admin_Form_Document::RESULT_CANCEL:
                        // TODO redirect to origin page (Store in Session oder Form?)
                        // Possible Rücksprungziele: Frontdoor, Metadaten-Übersicht, Suchergebnisse (Documents, ?)
                        return $this->_helper->Redirector->redirectTo(
                            'index', null, 'document', 'admin', array('id' => $docId)
                        );
                        break;

                    case Admin_Form_Document::RESULT_SWITCH_TO:
                        $editSession->storePost($data, $docId);

                        // TODO Parameter in Unterarray 'params' => array() verlagern?
                        $target['document'] = $docId;

                        $action = $target['action'];
                        unset($target['action']);
                        $controller = $target['controller'];
                        unset($target['controller']);
                        $module = $target['module'];
                        unset($target['module']);

                        return $this->_helper->Redirector->redirectTo($action, null, $controller, $module, $target);
                        break;

                    default:
                        // Zurueck zum Formular
                        break;
                }
            }
            else {
                // GET zeige neues oder gespeichertes Formular an

                // Hole gespeicherten POST aus Session
                $post = $editSession->retrievePost($docId);

                $continue = $this->getRequest()->getParam('continue', null);

                if ($post && !is_null($continue)) {
                    // Initialisiere Formular vom gespeicherten POST
                    $form = Admin_Form_Document::getInstanceFromPost($post, $document);
                    $form->populate($post);

                    // Führe Rücksprung aus
                    $form->continueEdit($this->getRequest(), $editSession);
                }
                else {
                    // Initialisiere Formular vom Dokument
                    $form = new Admin_Form_Document();
                    $form->populateFromModel($document);
                }

            }

            $wrappedForm = new Admin_Form_Wrapper($form);
            $wrappedForm->setAction('#current');
            $this->view->form = $wrappedForm;
        }

        $this->view->document = $document;
        $this->view->documentAdapter = new Application_Util_DocumentAdapter($this->view, $document);

        // Beim wechseln der Sprache würden Änderungen in editierten Felder verloren gehen
        $this->view->languageSelectorDisabled = true;
        $this->view->contentWrapperDisabled = true;
        $this->_helper->breadcrumbs()->setDocumentBreadcrumb($document);

        $this->renderForm($this->view->form);

        // Add translations for Javascript code
        $javascriptTranslations = $this->view->getHelper('javascriptMessages');
        $javascriptTranslations->addMessage('identifierInvalidFormat');
        $javascriptTranslations->addMessage('identifierInvalidCheckdigit');
    }

    /**
     * Creates a new document and opens it in the edit formular.
     *
     * TODO move creating of new document to model (maybe additional operations later)
     */
    public function createAction()
    {
        $doc = new Opus_Document();

        $docId = $doc->store();

        return $this->_helper->Redirector->redirectTo(
            'edit', 'admin_document_created', 'document', 'admin', ['id' => $docId]
        );
    }
}
