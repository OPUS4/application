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
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Controller for showing and editing a document in the administration.
 */
class Admin_DocumentController extends Controller_Action {

    /**
     * Helper for verifying document IDs.
     * @var Controller_Helper_Documents
     */
    private $documentsHelper;

    /**
     * Controller helper for handling dates.
     * @var Controller_Helper_Dates
     */
    private $__dates;
    
    /**
     * Name für allgemeinen Session Namespace.
     * @var type 
     */
    private $__namespace = 'admin';

    /**
     * Allgemeiner Session Namespace.
     * @Zend_Session_Namespace type 
     */
    private $__session;

    /**
     * Session Namespaces fuer einzelne Dokument.
     * 
     * Wenn beim Editieren der Metadaten eines Dokuments auf eine andere Seite gewechselt wird (Collections, Personen),
     * wird der letzte POST in einem Namespace für eine Dokumenten-ID abgespeichert, um den Zustand des Formulares 
     * wieder herstellen zu können, wenn zur Formularseite zurück gewechselt wird.
     * 
     * @var array
     * 
     * TODO Review solution (Wie funktioniert Namespace Bereinigung?)
     */
    private $__documentNamespaces = array();
    
    /**
     * Initializes controller.
     */
    public function init() {
        parent::init();
        $this->documentsHelper = $this->_helper->getHelper('Documents');
        $this->__dates = $this->_helper->getHelper('Dates');
    }

    /**
     * Produces metadata overview page of a document.
     * @return Opus_Document
     */
    public function indexAction() {
        $docId = $this->getRequest()->getParam('id');

        $document = $this->documentsHelper->getDocumentForId($docId);

        if (isset($document)) {
            $this->view->document = $document;
            $this->view->documentAdapter = new Util_DocumentAdapter($this->view, $document);
            $this->view->overviewHelper = new Admin_Model_DocumentHelper($document);
            
            $form = new Admin_Form_Document();
            $form->populateFromModel($document);
            $form->prepareRenderingAsView();
            
            $this->view->form = new Admin_Form_Wrapper($form);
            $this->view->breadcrumbsDisabled = true;
            
            return $document;
        }
        else {
            // missing or bad parameter => go back to main page
            return $this->_redirectTo('index', array('failure' =>
                $this->view->translate('admin_document_error_novalidid')),
                    'documents', 'admin');
        }
    }
    
    /**
     * Zeigt Metadaten-Formular an bzw. verarbeitet POST Requests vom Formular.
     * 
     * TODO prüfen ob Form DocID mit URL DocID übereinstimmt
     */
    public function editAction() {
        $docId = $this->getRequest()->getParam('id');

        $document = $this->documentsHelper->getDocumentForId($docId);
        
        if (!isset($document)) {
            return $this->_redirectTo('index', array('failure' =>
                $this->view->translate('admin_document_error_novalidid')),
                    'documents', 'admin');
        }
        else {
            if ($this->getRequest()->isPost()) {
                $data = $this->getRequest()->getPost();
                $data = $data['Document']; // 'Document' Form wraps actual metadata form
                
                $form = Admin_Form_Document::getInstanceFromPost($data, $document);
                $form->populate($data);
                
                // TODO use return value for decision how to continue
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
                        
                            $document->store(); // TODO handle exceptions

                            // TODO redirect to Übersicht/Browsing/???
                            $message = $this->view->translate('admin_document_update_success');
                            return $this->_redirectTo('index', $message, 'document', 'admin', array('id' => $docId));
                        }
                        else {
                            $this->view->message = $this->view->translate('admin_document_error_validation');
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
                            $this->view->message = $this->view->translate('admin_document_error_validation');
                        }
                        break;
                        
                    case Admin_Form_Document::RESULT_CANCEL:
                        // TODO redirect to origin page
                        return $this->_redirectTo('index', null, 'document', 'admin', array('id' => $docId));
                        break;
                    
                    case Admin_Form_Document::RESULT_SWITCH_TO:
                        $this->_storePost($data, $docId);
                        
                        // TODO Parameter in Unterarray 'params' => array() verlagern?
                        $target['document'] = $docId;
                        
                        $action = $target['action'];
                        unset($target['action']);
                        $controller = $target['controller'];
                        unset($target['controller']);
                        $module = $target['module'];
                        unset($target['module']);
                        
                        return $this->_redirectTo($action, null, $controller, $module, $target);
                        break;
                    
                    default:
                        // Zurueck zum Formular
                        break;
                }
            }
            else {
                // GET zeige neues oder gespeichertes Formular an
                
                // Hole gespeicherten POST aus Session 
                $post = $this->_retrievePost($docId);
                
                if ($post) {
                    // Initialisiere Formular vom gespeicherten POST
                    $form = Admin_Form_Document::getInstanceFromPost($post, $document);
                    $form->populate($post);
                    
                    // Führe Rücksprung aus
                    $form->continueEdit($this->getRequest());
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
        $this->view->documentAdapter = new Util_DocumentAdapter($this->view, $document);
        
        // Beim wechseln der Sprache würden Änderungen in editierten Felder verloren gehen
        $this->view->languageSelectorDisabled = true;
        $this->view->breadcrumbsDisabled = true;
    }

    /**
     * Speichert POST in session.
     * @param array $post
     */
    protected function _storePost($post, $documentId) {
        $namespace = $this->_getDocumentSessionNamespace($documentId);
        
        $namespace->lastPost = $post;
    }
    
    /**
     * Liefert gespeicherten POST.
     * @param string $hash Hash für Formular
     * @return array
     */
    protected function _retrievePost($documentId) {
        $namespace = $this->_getDocumentSessionNamespace($documentId);
        
        if (isset($namespace->lastPost)) {
            $post = $namespace->lastPost;
            $namespace->lastPost = null;
            return $post;
        }
        else {
            return null;
        }
    }
    
    /**
     * Liefert Session Namespace fuer diesen Controller.
     * @return Zend_Session_Namespace
     */
    protected function _getSessionNamespace() {
        if (null === $this->__session) {
            $this->__session = new Zend_Session_Namespace($this->__namespace);
        }
 
        return $this->__session;        
    }
    
    /**
     * Liefert Session Namespace fuer einzelnes Dokument.
     * @return Zend_Session_Namespace
     */
    protected function _getDocumentSessionNamespace($documentId) {
        $key = 'doc' . $documentId;
        
        if (!array_key_exists($key, $this->__documentNamespaces)) {
            $namespace = new Zend_Session_Namespace($key);
            $this->__documentNamespaces[$key] = $namespace;
        }
        else {
            $namespace = $this->__documentNamespaces[$key];
        }
 
        return $namespace;        
    }

}
