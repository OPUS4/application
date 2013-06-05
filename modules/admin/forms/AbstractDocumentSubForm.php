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
 * Abstraktes Unterformular (SubForm) fuer Metadaten-Formular.
 */
abstract class Admin_Form_AbstractDocumentSubForm extends Zend_Form_SubForm {
    
    /**
     * Initialisiert das Formular mit den Werten des Models.
     * 
     * @param $model
     */
    abstract function populateFromModel($model);
    
    /**
     * 
     * @param array $post
     */
    public function constructFromPost($post) {
    }
    
    /**
     * Verarbeitet POST Request vom Formular.
     * 
     * @param $data POST Daten fuer Unterformular
     * @param $context POST Daten vom gesamten Request
     */
    public function processPost($data, $context) {
    }
    
    /**
     * Aktualisiert die Instanz von Opus_Document durch Formularwerte.
     * 
     * TODO consider options for ChangeLog
     */
    public function updateModel($model) {
    }
    
    public function continueEdit($request) {
    }
    
    public function printValues() {
        $elements = $this->getElements();
        
        foreach ($elements as $name => $element) {
            Zend_Debug::dump($element->getValue(), $name);
        }
        
        $subforms = $this->getSubForms();
        
        foreach ($subforms as $name => $subform) {
            Zend_Debug::dump('Subform', $name);
            $subform->printValues();
        }
    }
    
    public function isEmpty() {
        return false;
    }
    
    public function prepareRenderingAsView() {
        $this->_removeElements();
        
        $subforms = $this->getSubForms();
        
        foreach ($subforms as $subform) {
            $subform->prepareRenderingAsView();
            if ($subform->isEmpty()) {
                $this->removeSubForm($subform->getName());
            }
        }
    }
    
    /**
     * Bereitet Formularelemente fuer statische Ausgabe in Metadaten-Übersicht vor.
     * 
     * TODO rename function 
     */
    protected function _removeElements() {
        $elements = $this->getElements();
        
        foreach ($elements as $element) {
            $value = $element->getValue();
        
            if ($element instanceof Zend_Form_Element_Button 
                    || $element instanceof Zend_Form_Element_Submit 
                    || empty($value)
                    ) {
                $this->removeElement($element->getName());
            }
            else if ($element instanceof Zend_Form_Element_Text 
                    || $element instanceof Zend_Form_Element_Textarea
                    ) {
                $element->setDecorators(array(array(
                    'ViewScript', array('viewScript' => 'form/staticElement.phtml'))));
            }
            else if ($element instanceof Zend_Form_Element_Select) {
                $element->setDecorators(array(array(
                    'ViewScript', array('viewScript' => 'form/staticSelect.phtml'))));
            }
            else if ($element instanceof Zend_Form_Element_Checkbox) {
                $element->setDecorators(array(array(
                    'ViewScript', array('viewScript' => 'form/staticCheckbox.phtml'))));
            }
        }
        
    }
    
    
}
