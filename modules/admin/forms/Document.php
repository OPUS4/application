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
class Admin_Form_Document extends Admin_Form_AbstractDocumentSubForm {

    /**
     * Name für Button zum Speichern.
     */
    const ELEMENT_SAVE = 'save';
    
    /**
     * Name für Button zum Speichern und im Metadaten-Formular bleiben.
     */
    const ELEMENT_SAVE_AND_CONTINUE = 'saveAndContinue';
    
    /**
     * Name für Button um das Editieren abzubrechen.
     */
    const ELEMENT_CANCEL = 'cancel';
    
    /**
     * Ergebnis wenn keine weiteren Aktionen ausgeführt werden müssen.
     * 
     * Unterformulare, die einen POST erfolgreich abgearbeitet haben, zum Beispiel ein Unterformular entfernt oder 
     * hinzugefügt haben melden dieses Signal, um zu zeigen, daß das Formular wieder ausgegeben werden kann.
     */
    const RESULT_SHOW = 'show';
    
    /**
     * Ergebnis von Unterformular, wenn die angezeigte Seite gewechselt werden soll.
     * 
     * Unterformulare, die Aufgrund des POST möchten, daß zu einer anderen Seite gewechselt wird schicken, dieses
     * Ergebnis zusammen mit den notwendigen Informationen für den Seitenwechsel.
     */
    const RESULT_SWITCH_TO = 'switch';
    
    const RESULT_SAVE = 'save';
    
    const RESULT_CANCEL = 'cancel';
    
    const RESULT_SAVE_AND_CONTINUE = 'saveAndContinue';
    
    private $message = null;
    
    /**
     * Konstruiert das Metadaten-Formular aus verschiedenen Unterformularen und den Aktion Buttons.
     */
    public function init() {
        parent::init();
        
        $this->setDecorators(array('FormElements'));
        
        $this->addSubForm(new Admin_Form_ActionBox($this), 'ActionBox');
        
        $subform = new Admin_Form_InfoBox();
        $subform->addDecorator(array('wrapperDiv' => 'HtmlTag'), 
                array('tag' => 'div', 'placement' => 'prepend', 'class' => 'wrapper', 'openOnly' => 'true'));
        $subform->addDecorator(array('wrapperDl' => 'HtmlTag'), 
                array('tag' => 'dl', 'placement' => 'append', 'openOnly' => 'true'));
        $this->addSubForm($subform, 'InfoBox');
        
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
        $subform->setLegend('admin_document_section_content');
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
        
        $this->addElement('hash', 'opus_hash', array('salt' => 'unique')); // TODO salt?
        
        $element = new Zend_Form_Element_Submit(self::ELEMENT_SAVE);
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Submit(self::ELEMENT_SAVE_AND_CONTINUE);
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Submit(self::ELEMENT_CANCEL);
        $this->addElement($element);
        
        $element->addDecorator(array('wrapperDl' => 'HtmlTag'), 
                array('tag' => 'dl', 'placement' => 'append', 'closeOnly' => 'true'));
        $element->addDecorator('HtmlTag',
                array('tag' => 'div', 'placement' => 'append', 'closeOnly' => 'true'));
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
    public static function getInstanceFromPost($data, $document = null) {
        $form = new Admin_Form_Document();
        
        $subforms = $form->getSubForms();
        
        foreach ($subforms as $name => $subform) {
            if (array_key_exists($name, $data)) {
                $subform->constructFromPost($data[$name], $document);
            }
            else {
                // ActionBox und InfoBox haben keine Element die im POST enthalten wären, müssen aber nach POST wieder
                // neu initialisiert werden 
                $subform->constructFromPost(array(), $document);
            }
        }
        
        return $form;
    }
    
    /**
     * Verarbeitet POST Request vom Formular.
     * @param type $data
     */
    public function processPost($data, $context) {
        // Prüfen, ob "Speichern" geklickt wurde
        if (array_key_exists(self::ELEMENT_SAVE, $data)) {
            return self::RESULT_SAVE;
        }
        else if (array_key_exists(self::ELEMENT_SAVE_AND_CONTINUE, $data)) {
            return self::RESULT_SAVE_AND_CONTINUE;
        }
        else if (array_key_exists(self::ELEMENT_CANCEL, $data)) {
            return self::RESULT_CANCEL;
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
    
    public function loadDefaultDecorators() {
        parent::loadDefaultDecorators();
        
        $this->removeDecorator('Fieldset');
        $this->removeDecorator('DtDdWrapper');
    }
    
    public function setMessage($message) {
        $this->message = $message;
    }
    
    public function getMessage() {
        return $this->message;
    }
            
}
