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
 * Unterformular fuer eine einem Dokument zugewiesene Person im Metadaten-Formular.
 * 
 * TODO Ausgabe über Partial (einschließlich aller Personen Informationen)
 * TODO Verarbeitung Edit
 * TODO Verarbeitung Remove
 * TODO Verarbeitung Sortierung
 * TODO Aktualisierung Modell
 */
class Admin_Form_DocumentPerson extends Admin_Form_AbstractDocumentSubForm {
    
    /**
     * Name fuer Formularelement fuer Feld AllowEmailContact.
     */
    const ELEMENT_ALLOW_CONTACT = 'AllowContact';
    
    /**
     * Name fuer Formularelement fuer Feld Role.
     */
    const ELEMENT_ROLE = 'Role';
    
    /**
     * Name fuer Formularelement fuer Feld SortOrder.
     */
    const ELEMENT_SORT_ORDER = 'SortOrder';
    
    /**
     * Name fuer Button zum Editieren der Person.
     */
    const ELEMENT_EDIT = 'Edit';
    
    /**
     * Name fuer Button zum Entfernen der Person.
     */
    const ELEMENT_REMOVE = 'Remove';
    
    /**
     * Konstante fuer das POST Ergebnis Person entfernen.
     */
    const RESULT_REMOVE = 'remove';
    
    /**
     * Erzeugt die Formularelemente.
     */
    public function init() {
        parent::init();
        
        $elementFactory = new Admin_Model_FormElementFactory();
                
        $element = new Zend_Form_Element_Hidden(Admin_Form_Person::ELEMENT_PERSON_ID);
        $this->addElement($element);
        
        $element = $elementFactory->createPersonRoleSelect(self::ELEMENT_ROLE);
        $element->setLabel('Role');
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Checkbox(self::ELEMENT_ALLOW_CONTACT);
        $element->setLabel('AllowEmailContact');
        // $element->setDecorators(array('ViewHelper'));
        $this->addElement($element);
        
        // TODO Durch SELECT ersetzen?
        $element = new Zend_Form_Element_Text(self::ELEMENT_SORT_ORDER);
        $element->setLabel('SortOrder');
        $this->addElement($element);
        
        // Edit Button
        $element = new Zend_Form_Element_Submit(self::ELEMENT_EDIT);
        $element->setDecorators(array('ViewHelper'));
        $this->addElement($element);
        
        // Remove Button
        $element = new Zend_Form_Element_Submit(self::ELEMENT_REMOVE);
        $element->setDecorators(array('ViewHelper'));
        $this->addElement($element);
    }
    
    public function initRendering() {
        $this->setDisableLoadDefaultDecorators(true);
        
        $this->setDecorators(array(array(
            'ViewScript', array('viewScript' => 'form/personForm.phtml'))));
    }
    
    public function populateFromModel($personLink) {
        if ($personLink instanceof Opus_Model_Dependent_Link_DocumentPerson) {
            $this->getElement(self::ELEMENT_ALLOW_CONTACT)->setValue($personLink->getAllowEmailContact());
            $this->getElement(self::ELEMENT_SORT_ORDER)->setValue($personLink->getSortOrder());
            $this->getElement(Admin_Form_Person::ELEMENT_PERSON_ID)->setValue($personLink->getModel()->getId());
            $this->getElement(self::ELEMENT_ROLE)->setValue($personLink->getRole());
        }
        else {
            $this->getLog()->err('populateFromModel called with object that is not instance of '
                    . 'Opus_Model_Dependent_Link_DocumentPerson');
        }
    }
    
    public function populateFromPost($post) {
        // TODO needed?
    }
    
    public function processPost($post, $context) {
        if (array_key_exists(self::ELEMENT_REMOVE, $post)) {
            return self::RESULT_REMOVE;
        }
        else if (array_key_exists(self::ELEMENT_EDIT, $post)) {
            return array( 'result' => Admin_Form_Document::RESULT_SWITCH_TO, 
                'target' => array(
                'module' => 'admin',
                'controller' => 'person',
                'action' => 'editlinked',
                'personId' => $this->getElement(Admin_Form_Person::ELEMENT_PERSON_ID)->getValue()
                )
            );
        }
        
        return null;
    }
    
    public function getLinkModel($documentId, $personRole) {
        $personId = $this->getElement(Admin_Form_Person::ELEMENT_PERSON_ID)->getValue();
        
        try {
            $personLink = new Opus_Model_Dependent_Link_DocumentPerson(array($personId, $documentId, $personRole));
        }
        catch (Opus_Model_NotFoundException $opnfe) {
            $personLink = new Opus_Model_Dependent_Link_DocumentPerson();
            $person = new Opus_Person($personId);
            $personLink->setModel($person);
            $personLink->setRole($personRole);
        }
        $this->updateModel($personLink); 
        
        return $personLink;
    }
    
    public function updateModel($personLink) {
        $personLink->setAllowEmailContact($this->getElementValue(self::ELEMENT_ALLOW_CONTACT));
        $personLink->setSortOrder($this->getElementValue(self::ELEMENT_SORT_ORDER));
        $personLink->setRole($this->getElementValue(self::ELEMENT_ROLE));
    }
    
    public function prepareRenderingAsView() {
        parent::prepareRenderingAsView();
        
        $this->removeElement(self::ELEMENT_SORT_ORDER);
        $this->removeElement(self::ELEMENT_ROLE);
    }

}
