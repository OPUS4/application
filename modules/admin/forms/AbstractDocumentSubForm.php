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
    
    private $viewMode = false;
    
    private $logger;
    
    public function init() {
        parent::init();
        
        $this->addPrefixPath('Form_Decorator', 'Form/Decorator', Zend_Form::DECORATOR);
        // $this->addElementPrefixPath('Form_Decorator', 'Form/Decorator', Zend_Form::DECORATOR);
        $this->addPrefixPath('Form', 'Form'); // '_Element' wird anscheinend automatisch dran gehängt
        
        $this->setDisableLoadDefaultDecorators(true);
        $this->setDecorators(array(
            'FormElements',
            array(array('fieldsWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'fields-wrapper')),
            array('FormErrors', array('placement' => 'prepend', 'ignoreSubForms' => true)),
            'Fieldset',
            array(array('divWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'subform'))
        ));
    }
    
    /**
     * Initialisiert das Formular mit den Werten des Models.
     * 
     * @param $model
     */
    abstract function populateFromModel($model);
    
    /**
     * Erzeugt Unterformularstruktur anhand der POST Hierarchy.
     * 
     * @param array $post
     * 
     * TODO Möglich mit populate() zu verschmelzen?
     */
    public function constructFromPost($post, $document = null) {
    }
        
    /**
     * Verarbeitet POST Request vom Formular.
     * 
     * Das Defaultverhalten ist das weiterleiten des POST an die Unterformulare.
     * 
     * @param $data POST Daten fuer Unterformular
     * @param $context POST Daten vom gesamten Request
     * 
     * TODO Modifiziere zu $context = null um context optional zu machen?
     */
    public function processPost($data, $context) {
        $subforms = $this->getSubForms();

        foreach ($subforms as $name => $subform) {
            if (array_key_exists($name, $data)) {
                $result = $subform->processPost($data[$name], $context);

                if (!is_null($result)) {
                    return $result;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Aktualisiert die Instanz von Opus_Document durch Formularwerte.
     * 
     * TODO consider options for ChangeLog
     * @param Opus_Document $document
     */
    public function updateModel($model) {
        $subforms = $this->getSubForms();
        
        foreach ($subforms as $form) {
            $form->updateModel($model);
        }
    }
    
    public function continueEdit($request, $session = null) {
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
    
    /**
     * Zusätzlich Validierungsfunktion für Prüfungen über mehrere Unterformulare hinweg.
     * 
     * Bei dieser Funktion werden die POST Daten für das gesamte Formular mit heruntergereicht, um dann einen Abgleich
     * mit beliebigen Teilen des gesamten Formulars durchführen zu können.
     * 
     * @param array $data
     * @param array $globalContext
     * @return boolean true - wenn alle Abhängigkeiten erfüllt sind
     */
    public function isDependenciesValid($data, $globalContext) {
        $result = true;
        
        foreach ($this->getSubForms() as $name => $subform) {
            if (array_key_exists($name, $data) && !$subform->isDependenciesValid($data[$name], $globalContext)) {
                $result = false; // trotzdem Validierung über alle Unterformulare um auch mehrere Meldungen anzuzeigen
            }
        }
        
        return $result;
    }
    
    public function isEmpty() {
        return (count($this->getElements()) == 0 && count($this->getSubforms()) == 0);
    }
    
    public function prepareRenderingAsView() {
        $this->setViewModeEnabled();
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
    
    /**
     * Liefert den Wert eines Formularelements zurück.
     * 
     * Wenn das Formularelement einen leeren String enthält wird für Text und Textarea Elemente der Wert null zurück 
     * geliefert.
     * 
     * @param string $name
     * @return mixed
     * 
     * TODO Sind alle Fälle abgedeckt?
     */
    public function getElementValue($name) {
        $element = $this->getElement($name);
        if (!is_null($element)) {
            $value = $element->getValue();
            
            if ($element instanceof Zend_Form_Element_Text || $element instanceof Zend_Form_Element_Textarea
                    || $element instanceof Zend_Form_Element_Hidden) {
                return (trim($value) === '') ? null : $value;
            }
            else {
                return $value;
            }
        }
        else {
            // Sollte nie passieren - Schreibe Fehlermeldung ins Log
            $this->getLog()->err('Element \'' . $name . '\' in form \'' . $this->getName() .
                    '\' not found.');
            return null;
        }
    }
        
    /**
     * Liefert Helper fuer die Handhabung von Datumsangaben.
     * 
     * @return \Controller_Helper_Dates
     */
    public function getDatesHelper() {
        return Zend_Controller_Action_HelperBroker::getStaticHelper('Dates');
    }
    
    /**
     * Liefert Zend_Log Objekt zum Schreiben von Logeintraegen.
     */
    public function getLog() {
        if (is_null($this->logger)) {
            $this->logger = Zend_Registry::get('Zend_Log');
        }
        
        return $this->logger;
    }
    
    public function setLog($logger) {
        $this->logger = $logger;
    }
    
    public function isViewModeEnabled() {
        return $this->viewMode;
    }
    
    public function setViewModeEnabled() {
        $this->viewMode = true;
    }
    
    public function addElement($element, $name = null, $options = null) {
        parent::addElement($element, $name, $options);
        if (!is_null($name) && !is_null($options) && array_key_exists('required', $options) && $options['required']) {
            $notEmptyValidator = new Zend_Validate_NotEmpty();
            $notEmptyValidator->setMessage('admin_validate_error_notempty');
            $element = $this->getElement($name);
            $element->addValidator($notEmptyValidator);
        }
        
        if ($element == 'Email' && !is_null($name)) {
            $element = $this->getElement($name);
            $element->addErrorMessage('admin_validate_error_email');
        }
        
        if ($element == 'date' && !is_null($name)) {
            $element = $this->getElement($name);
            $element->addErrorMessage('admin_validate_error_date');
        }
    }    
            
}
