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
 */

/**
 * Abstrakte Basisklasse für OPUS Formulare.
 *
 * @category    Application
 * @package     Application_Form
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
abstract class Application_Form_Abstract extends Zend_Form_SubForm {

    /**
     * Konfiguration Objekt für Applikation.
     * @var Zend_Config
     */
    private $_config;


    /**
     * Logger für Formularklasse.
     * @var Zend_Log
     */
    private $_logger;

    /**
     * Option für die automatische Verwendung der Element-Namen als Labels.
     * @var bool
     */
    private $_useNameAsLabel = false;

    /**
     * Prefix fuer automatische Label.
     * @var string
     */
    private $_labelPrefix;

    /**
     * Initialisiert das Formular.
     */
    public function init() {
        parent::init();

        $this->addPrefixPath('Application_Form_Decorator', 'Application/Form/Decorator', Zend_Form::DECORATOR);
        // $this->addElementPrefixPath('Form_Decorator', 'Form/Decorator', Zend_Form::DECORATOR);
        $this->addPrefixPath('Form', 'Form'); // '_Element' wird anscheinend automatisch dran gehängt
        $this->addPrefixPath('Application_Form', 'Application/Form');
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
     * TODO replace with filter or override getValue($name)
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
            $this->getLogger()->err("Element '$name' in form '" . $this->getName() . "' not found.");
            return null;
        }
    }

    /**
     * Fügt ein Element zum Formular hinzu.
     *
     * Ist das Element als 'required' markiert, werden die Nachrichten für den entsprechenden Validator gesetzt.
     *
     * Wenn die Option 'useNameAsLabel' auf true gesetzt ist wird automatisch der Name des Elements als Label verwendet.
     * Bei vielen Opus Model Formularen stimmt der Element-Name mit dem Übersetzungsschlüssel überein.
     *
     * @param string|Zend_Form_Element $element
     * @param null $name
     * @param null $options
     * @return void|Zend_Form
     */
    public function createElement($element, $name , $options = null) {
        if ($this->isUseNameAsLabel()) {
            $labelOption = array('label' => is_null($this->_labelPrefix) ? $name : $this->_labelPrefix . $name);
            $options = (is_array($options)) ? array_merge($labelOption, $options) : $labelOption;
        }

        $element = parent::createElement($element, $name, $options);

        if (!is_null($element)) {
            $this->applyCustomMessages($element);
        }

        return $element;
    }

    /**
     * Fügt angepasste Nachrichten für Validierungen hinzu.
     * @param Zend_Form_Element $element
     */
    protected function applyCustomMessages($element) {
        if ($element->isRequired()) {
            // wenn Validator 'notEmpty' bereits gesetzt ist; nicht modifizieren
            if (!$element->getValidator('notEmpty') && $element->autoInsertNotEmptyValidator()) {
                $notEmptyValidator = new Zend_Validate_NotEmpty();
                $notEmptyValidator->setMessage('admin_validate_error_notempty');
                $element->addValidator($notEmptyValidator);
            }
        }
    }

    /**
     * TODO Verwendung entfernen und dann löschen
     * @deprecated wir sollten einheitlich get/setLogger verwenden
     */
    public function getLog() {
        return $this->getLogger();
    }

    /**
     * TODO Verwendung entfernen und dann löschen
     * @deprecated wir sollten einheitlich get/setLogger verwenden
     */
    public function setLog($logger) {
        $this->setLogger($logger);
    }

    /**
     * Liefert den Logger für diese Klasse.
     *
     * Wenn für die Klasse kein Logger gesetzt wurde, wird der Wert von 'Zend_Log' in Zend_Registry zurueck geliefert.
     *
     * @return Zend_Log
     */
    public function getLogger() {
        if (is_null($this->_logger)) {
            $this->_logger = Zend_Registry::get('Zend_Log');
        }

        return $this->_logger;
    }

    /**
     * Setzt den Logger für diese Klasse
     * @param $logger
     */
    public function setLogger($logger) {
        $this->_logger = $logger;
    }

    /**
     * Meldet, ob Element-Namen als Label verwendet werden.
     * @return bool TRUE - Element Namen werden als Label verwendet; FALSE - keine automatischen Label
     */
    public function isUseNameAsLabel() {
        return $this->_useNameAsLabel;
    }

    /**
     * Setzt Option fuer die automatische Verwendung von Element-Namen als Label.
     * @param bool $useNameAsLabel
     */
    public function setUseNameAsLabel($useNameAsLabel) {
        $this->_useNameAsLabel = $useNameAsLabel;
    }

    /**
     * Liefert den gesetzten Prefix fuer automatisch generierte Label.
     * @return string
     */
    public function getLabelPrefix() {
        return $this->_labelPrefix;
    }

    /**
     * Setzt den Prefix der fuer automatische Label verwendet werden soll.
     *
     * @param $prefix
     */
    public function setLabelPrefix($prefix) {
        $this->_labelPrefix = $prefix;
    }

    /**
     * Returns configuration.
     * @return Zend_Config
     */
    public function getApplicationConfig() {
        if (is_null($this->_config)) {
            $this->_config = Zend_Registry::get('Zend_Config');
        }

        return $this->_config;
    }

    /**
     * Sets configuration.
     * @param $config Zend_Config
     */
    public function setApplicationConfig($config) {
        $this->_config = $config;
    }

}
