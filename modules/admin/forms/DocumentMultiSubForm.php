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
 * SubForm um mehrere Unterformulare (z.B. Patente) zu verwalten.
 * 
 * TODO improv positioning of anker (move within identifiable block)
 */
class Admin_Form_DocumentMultiSubForm extends Admin_Form_AbstractDocumentSubForm {

    /**
     * Klasse für Unterformulare.
     * @var type 
     */
    private $_subFormClass;
    
    /**
     * Opus_Document Feldname für Unterformulare.
     * @var type
     */
    private $_fieldName;
    
    /**
     * 
     * @param String $_subFormClass
     * @param multi $options
     */
    public function __construct($subFormClass, $fieldName, $options = null) {
        $this->_subFormClass = $subFormClass;
        $this->_fieldName = $fieldName;
        
        parent::__construct($options);
    }
    
    /**
     * 
     */
    public function init() {
        $element = new Zend_Form_Element_Submit('add'); // TODO translate depending on $_subFormClass
        $element->setLabel('Hinzufügen');
        $element->setOrder(1000); // TODO only theoretically safe
        $this->addElement($element);
        
        $this->setLegend($this->_fieldName); // TODO prefix translation key
    }
    
    /**
     * Erzeugt Unterformulare abhängig von den Metadaten im Dokument.
     * 
     * @param Opus_Document $document
     */
    public function populateFromModel($document) {
       $this->clearSubForms(); 
        
       $field = $document->getField($this->_fieldName);
       
       $values = $field->getValue();
       
       $maxIndex = 0;
       
       foreach ($values as $index => $value) {
           if ($maxIndex < $index) {
               $maxIndex = $index;
           }
           $subForm = $this->_addSubForm($index);
           $subForm->populateFromModel($value);
       }
       
       // Sicherstellen, daß Button zum Hinzufügen zuletzt angezeigt wird
       $this->getElement('add')->setOrder($maxIndex + 1);
    }
    
    /**
     * Erzeugt Unterformulare basierend auf den Informationen in den POST Daten.
     */
    public function constructFromPost($post) {
        // TODO Zend_Debug::dump($post);
        
        $keys = array_keys($post);
        
        foreach ($keys as $index => $key) {
            // Prüfen ob Unterformluar (array) oder Feld
            if (is_array($post[$key])) {
                $this->_addSubForm($index);
            }
        }
    }
    
    /**
     * Verarbeitet POST Request fuer Formular.
     * 
     * 
     * 
     * @param array $data POST Daten für Unterformular
     * @param array $context POST Daten für gesamtes Formular
     * @return string Ergebnis der Verarbeitung
     */
    public function processPost($data, $context) {
        // Prüfen ob "Hinzufügen" geklickt wurde
        if (array_key_exists('add', $data)) {
            $subform = $this->_appendSubForm();
            $this->_addAnker($subform);
        }
        else {
            // Prüfen ob in einem Unterformular "Entfernen" geklickt wurde
            $keys = array_keys($data);
            
            foreach ($keys as $key) {
                if ($this->getSubForm($key)) {
                    if (array_key_exists('Remove', $data[$key])) {
                        // TODO separate function for getting position?
                        $position = $this->_removeSubForm($key);

                        $this->_addAnker($this->_determineSubFormForAnker($position));
                        
                        return Admin_Form_Document::SHOW;
                    }
                }
                else {
                    // TODO debug output should never happen
                }
            }
            
            // TODO call processPost for all subforms ? (not used yet)
        }
    }
    
    public function updateModel($document) {
        $subforms = $this->getSubForms();
        
        $values = array();
        
        foreach ($subforms as $subform) {
            if (!is_null($subform)) {
                $value = $subform->getModel();
                
                if (!is_null($value)) {
                    $values[] = $value;
                }
            }
        }
        
       $field = $document->getField($this->_fieldName);
       
       $field->setValue($values);
    }
    
    protected function _addSubForm($position) {
        // TODO Zend_Debug::dump('Adding subform at position ' . $position);
        
        $subForm = new $this->_subFormClass();
        $subForm->setOrder($position);

        $element = new Zend_Form_Element_Submit('Remove');
        $element->setValue('Remove');
        $subForm->addElement($element);

        $this->addSubForm($subForm, $this->_fieldName . $position);
        
        return $subForm;
    }

    /**
     * Entfernt Unterformular mit dem übergebenen Namen.
     * @param string $name
     * 
     * TODO what does this function if the name is bad?
     */
    protected function _removeSubForm($name) {
        $order = $this->getSubForm($name)->getOrder();

        $this->removeSubForm($name);
        $this->_removeGapsInSubFormOrder();
        
        return $order;
    }
    
    /**
     * Sorgt für lückenlose Nummerierung der Unterformulare.
     */
    protected function _removeGapsInSubFormOrder() {
        $subforms = $this->getSubForms();

        $pos = 0;

        foreach ($subforms as $index => $subform) {
            $subform->setOrder($pos);
            $subform->setName($this->_fieldName . $pos);
            $pos++;
        }
    }
    
    /**
     * Erzeugt ein weiteres Unterformular an letzter Stelle.
     */
    protected function _appendSubForm() {
        $subforms = $this->getSubForms();
        
        return $this->_addSubForm(count($subforms));
    }
    
    protected function _determineSubFormForAnker($removedPosition) {
        $subforms = $this->getSubForms();

        $index = ($removedPosition > 0) ? $removedPosition - 1 : 0;

        if (count($subforms) > 0) {
            $keys = array_keys($subforms);
            $name = $keys[$index];
            return $this->getSubForm($name);
        }
        else {
            return $this;
        }          
    }
    
    protected function _addAnker($subform) {
        $subform->addDecorator(
                array('currentAnker' => 'HtmlTag'), 
                array('tag' => 'a', 'placement' => 'prepend', 'name' => 'current'));
    }
        
    public function removeValue() {
    }
    
    public function addValue() {
    }
    
}
