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
 * Formular fuer Metadaten eines Dokuments.
 */
class Admin_Form_Document extends Zend_Form {

    // TODO review these constants and their purpose
    const SAVE = 'save';
    
    const SAVE_AND_CONTINUE = 'saveAndContinue';
    
    const CANCEL = 'cancel';
    
    const SHOW = 'show';
    
    const SWITCH_TO = 'switch';
    
    public function init() {
        parent::init();
        
        $this->addElement('hash', 'opus_hash', array('salt' => 'unique'));
        
        $this->addSubForm(new Admin_Form_DocumentGeneral(), 'General');
        
        $this->addSubForm(new Admin_Form_DocumentPersons(), 'Persons');
        
        // Bibliographische Beschreibung
        $this->addSubForm(new Admin_Form_DocumentTitles(), 'Titles');
        $this->addSubForm(new Admin_Form_DocumentBibliographic(), 'Bibliographic');
        $this->addSubForm(new Admin_Form_DocumentMultiSubForm('Admin_Form_DocumentSeries', 'Series'), 'Series');
        
        $this->addSubForm(new Admin_Form_DocumentMultiSubForm('Admin_Form_DocumentEnrichment', 'Enrichment'), 
                'Enrichments');

        $this->addSubForm(new Admin_Form_DocumentCollections(), 'Collections');

        // Inhaltliche Erschließung
        
        $subform = new Admin_Form_DocumentSection();
        $subform->addSubForm(new Admin_Form_DocumentMultiSubForm('Admin_Form_DocumentAbstract', 'TitleAbstract'), 
                'Abstracts');
        $subform->addSubForm(new Admin_Form_DocumentSubjects(), 'Subjects');
        $this->addSubForm($subform, 'Content');
        
        // Weiteres Allgemeines
        $this->addSubForm(new Admin_Form_DocumentMultiSubForm('Admin_Form_DocumentIdentifier', 'Identifier'), 
                'Identifiers');
        $this->addSubForm(new Admin_Form_DocumentLicences(), 'Licences');
        $this->addSubForm(new Admin_Form_DocumentMultiSubForm('Admin_Form_DocumentPatent', 'Patent'), 'Patents');
        $this->addSubForm(new Admin_Form_DocumentMultiSubForm('Admin_Form_DocumentNote', 'Note'), 'Notes');
        
        $element = new Zend_Form_Element_Hidden('id');
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Submit('save');
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Submit('saveAndContinue');
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Submit('cancel');
        $this->addElement($element);
    }
    

    /**
     * Populates form from model values.
     */
    public function populateFromModel($document) {
        $this->getElement('id')->setValue($document->getId());
        
        $subforms = $this->getSubForms();
        
        foreach ($subforms as $form) {
            $form->populateFromModel($document);
        }
    }
    
    /**
     * Aktualisiert Instanz von Opus_Document mit Formularwerten.
     * @param Opus_Document $document
     */
    public function updateModel($document) {
        $subforms = $this->getSubForms();
        
        foreach ($subforms as $form) {
            $form->updateModel($document);
        }
    }
    
    /**
     * Konstruiert Formular mit Unterformularen basierend auf POST Daten.
     * @param array $data
     */
    public static function constructFromPost($data) {
        $form = new Admin_Form_Document();
        
        $subforms = $form->getSubForms();
        
        foreach ($subforms as $name => $subform) {
            if (array_key_exists($name, $data)) {
                $subform->constructFromPost($data[$name]);
            }
        }
        
        return $form;
    }
    
    /**
     * Verarbeitet POST Request vom Formular.
     * @param type $data
     */
    public function processPost($data) {
        // Prüfen, ob "Speichern" geklickt wurde
        if (array_key_exists('save', $data)) {
            return self::SAVE;
        }
        else if (array_key_exists('saveAndContinue', $data)) {
            return self::SAVE_AND_CONTINUE;
        }
        else if (array_key_exists('cancel', $data)) {
            return self::CANCEL;
        }
        else {
            // POST Daten an Unterformulare weiterreichen
            $subforms = $this->getSubForms();
            
            foreach ($subforms as $name => $form) {
                if (array_key_exists($name, $data)) {
                    // TODO process return value (exit from loop if success)
                    $result = $form->processPost($data[$name], $data);
                    
                    if (!is_null($result)) {
                        return $result;
                    }
                }
            }
        }
        
    }
    
    public function continueEdit($request) {
        $subforms = $this->getSubForms();

        foreach ($subforms as $name => $subform) {
            $subform->continueEdit($request);
        }
    }
    
    /**
     * TODO AbstractDocumentSubForm enthaelt die selbe funktion
     */
    public function prepareRenderingAsView() {
        $this->_removeElements();
        $this->_prepareRenderingOfElements();
        
        $subforms = $this->getSubForms();
        
        foreach ($subforms as $subform) {
            $subform->prepareRenderingAsView();
            if ($subform->isEmpty()) {
                $this->removeSubForm($subform->getName());
            }
        }
    }
    
    protected function _removeElements() {
        $elements = $this->getElements();
        
        foreach ($elements as $element) {
            $value = $element->getValue();
        
            if ($element instanceof Zend_Form_Element_Button 
                    || $element instanceof Zend_Form_Element_Submit) {
                $this->removeElement($element->getName());
            }
            else if (trim($value) === '') {
                $this->removeElement($element->getName());
            }
            else if ($element instanceof Zend_Form_Element_Checkbox) {
                if ($element->getValue() == 0) {
                    $this->removeElement($element->getName());
                }
            }
        }
    }
    
    protected function _prepareRenderingOfElements() {
        $elements = $this->getElements();
        
        foreach ($elements as $element) {
           if ($element instanceof Zend_Form_Element_Text || $element instanceof Zend_Form_Element_Textarea) {
                $element->setDecorators(array(
                    array('ViewScript', array('viewScript' => 'form/staticElement.phtml'))));
            }
            else if ($element instanceof Zend_Form_Element_Select) {
                $element->setDecorators(array(
                    array('ViewScript', array('viewScript' => 'form/staticSelect.phtml'))));
            }
            else if ($element instanceof Zend_Form_Element_Checkbox) {
                $element->setDecorators(array(
                    array('ViewScript', array('viewScript' => 'form/staticCheckbox.phtml'))));
            }
        }
    }
        
}
