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
 * @copyright   Copyright (c) 2008-2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Controller for showing and editing a document in the administration.
 */
class Admin_PersonController extends Controller_Action {

    private $__documentsHelper;
    
    private $__datesHelper;
    
    
    /**
     * Initializes controller.
     */
    public function init() {
        parent::init();
        $this->__documentsHelper = $this->_helper->getHelper('Documents');
        $this->__dates = $this->_helper->getHelper('Dates');
    }
    
    /**
     * Fuegt Person zu Dokument hinzu.
     * 
     * TODO Formular anzeigen
     * TODO Rolle vorauswählen
     * TODO Person speichern
     * TODO Zum MetadatenFormular zurückspringen (dabei person, contact, usw. übergeben)
     * TODO validierung
     * TODO cancel (zurück zum Metadaten Formular)
     * 
     * HTTP Parameter:
     * - Dokument-ID (document)
     * - Rolle (role)
     */
    public function assignAction() {
        $docId = $this->getRequest()->getParam('document');

        $document = $this->__documentsHelper->getDocumentForId($docId);
        
        if (!isset($document)) {
            return $this->_redirectTo('index', array('failure' =>
                $this->view->translate('admin_document_error_novalidid')),
                    'documents', 'admin');
        }
        
        if (!$this->getRequest()->isPost()) {
            // Formular anzeigen
            $form = $this->_getPersonForm();
            
            $role = $this->getRequest()->getParam('role', 'author');

            $form->getSubForm('link')->getElement(Admin_Form_DocumentPerson::ELEMENT_ROLE)->setValue($role);

            $this->view->form = $form;
        }
        else {
            // POST verarbeiten
            $post = $this->getRequest()->getPost();
            
            $form = $this->_getPersonForm();
            
            $form->populate($post);
            
            $result = $form->processPost($post, $post);
            
            switch ($result) {
                case Admin_Form_Person::RESULT_SAVE:
                    if ($form->isValid($post)) {
                        $person = $form->getModel();
                        $person->store();
                        $linkForm = $form->getSubForm('link');
                        return $this->_redirectToAndExit('edit', null, 'document', 'admin', array('id' => $docId,
                            'continue' => 'addperson', 
                            'person' => $person->getId(), 
                            // 'role' => $linkForm->getElement(Admin_Form_DocumentPerson::ELEMENT_ROLE)->getValue(),
                            'contact' => $linkForm->getElement(Admin_Form_DocumentPerson::ELEMENT_ALLOW_CONTACT)->getValue(),
                            'order' => $linkForm->getElement(Admin_Form_DocumentPerson::ELEMENT_SORT_ORDER)->getValue()
                            ));
                    }
                    // TODO Validierungsfehlernachricht für Formular anzeigen
                    break;
                case Admin_Form_Person::RESULT_CANCEL:
                    // Person nicht speichern
                    return $this->_redirectToAndExit('edit', null, 'document', 'admin', array('id' => $docId));
                    break;
                default:
                    break;
            }
            
            $this->view->form = $form;
        }
    }
    
    public function editlinkedAction() {
        $docId = $this->getRequest()->getParam('document');

        $document = $this->__documentsHelper->getDocumentForId($docId);
        
        if (!isset($document)) {
            return $this->_redirectTo('index', array('failure' =>
                $this->view->translate('admin_document_error_novalidid')),
                    'documents', 'admin');
        }
        
        if (!$this->getRequest()->isPost()) {
            // Formular anzeigen
            $form = $this->_getPersonForm();
            
            $role = $this->getRequest()->getParam('role');
            $personId = $this->getRequest()->getParam('person');
            
            $personLink = new Opus_Model_Dependent_Link_DocumentPerson(array($personId, $docId, $role));
            
            $form->populateFromModel($personLink->getModel());

            // $form->getSubForm('link')->getElement(Admin_Form_DocumentPerson::ELEMENT_ROLE)->setValue($role);

            $this->view->form = $form;
        }
        else {
            // POST verarbeiten
            $post = $this->getRequest()->getPost();
            
            $form = $this->_getPersonForm();
            
            $form->populate($post);
            
            $result = $form->processPost($post, $post);
            
            switch ($result) {
                case Admin_Form_Person::RESULT_SAVE:
                    if ($form->isValid($post)) {
                        $person = $form->getModel();
                        $person->store();
                        $linkForm = $form->getSubForm('link');
                        return $this->_redirectToAndExit('edit', null, 'document', 'admin', array('id' => $docId,
                            'continue' => 'updateperson', 
                            'person' => $person->getId(), 
                            // 'role' => $linkForm->getElement(Admin_Form_DocumentPerson::ELEMENT_ROLE)->getValue(),
                            'contact' => $linkForm->getElement(Admin_Form_DocumentPerson::ELEMENT_ALLOW_CONTACT)->getValue(),
                            'order' => $linkForm->getElement(Admin_Form_DocumentPerson::ELEMENT_SORT_ORDER)->getValue()
                            ));
                    }
                    // TODO Validierungsfehlernachricht für Formular anzeigen
                    break;
                case Admin_Form_Person::RESULT_CANCEL:
                    // Person nicht speichern
                    return $this->_redirectToAndExit('edit', null, 'document', 'admin', array('id' => $docId));
                    break;
                default:
                    break;
            }
            
            $this->view->form = $form;
        }
    }
    
    protected function _getPersonForm() {
        $form = new Admin_Form_Person();
        
        $form->addDecorator('Form');
        
        $linkForm = new Admin_Form_DocumentPerson();
        
        $linkForm->removeElement(Admin_Form_Person::ELEMENT_PERSON_ID);
        $linkForm->removeElement(Admin_Form_DocumentPerson::ELEMENT_EDIT);
        $linkForm->removeElement(Admin_Form_DocumentPerson::ELEMENT_REMOVE);
        
        $form->addSubForm($linkForm, 'link');
        
        // Zend_Debug::dump($linkForm->getElements());
        
        return $form;
    }
    
}