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
 * @copyright Copyright (c) 2008, OPUS 4 development team
 * @license   http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Config;
use Opus\Common\LoggingTrait;

/**
 * Abstrakte Basisklasse für OPUS Formulare.
 */
abstract class Application_Form_Abstract extends Zend_Form_SubForm
{
    use LoggingTrait;

    /**
     * Konfiguration Objekt für Applikation.
     *
     * @var Zend_Config
     */
    private $config;

    /**
     * Option für die automatische Verwendung der Element-Namen als Labels.
     *
     * @var bool
     */
    private $useNameAsLabel = false;

    /**
     * Prefix fuer automatische Label.
     *
     * @var string
     */
    private $labelPrefix;

    /**
     * Initialisiert das Formular.
     */
    public function init()
    {
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
     * @return mixed|null
     *
     * TODO Sind alle Fälle abgedeckt?
     * TODO replace with filter or override getValue($name)
     */
    public function getElementValue($name)
    {
        $element = $this->getElement($name);
        if ($element !== null) {
            $value = $element->getValue();

            if (
                $element instanceof Zend_Form_Element_Text || $element instanceof Zend_Form_Element_Textarea
                || $element instanceof Zend_Form_Element_Hidden
            ) {
                return $value === null || trim($value) === '' ? null : $value;
            } else {
                return $value;
            }
        } else {
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
     * @param string                   $name
     * @param array|null               $options
     * @return Zend_Form_Element
     */
    public function createElement($element, $name, $options = null)
    {
        if ($this->isUseNameAsLabel()) {
            $labelOption = ['label' => $this->labelPrefix === null ? $name : $this->labelPrefix . $name];
            $options     = is_array($options) ? array_merge($labelOption, $options) : $labelOption;
        }

        $element = parent::createElement($element, $name, $options);

        if ($element !== null) {
            $this->applyCustomMessages($element);
        }

        return $element;
    }

    /**
     * Fügt angepasste Nachrichten für Validierungen hinzu.
     *
     * @param Zend_Form_Element $element
     */
    protected function applyCustomMessages($element)
    {
        if ($element->isRequired()) {
            // wenn Validator 'notEmpty' bereits gesetzt ist; nicht modifizieren
            if (! $element->getValidator('notEmpty') && $element->autoInsertNotEmptyValidator()) {
                $notEmptyValidator = new Zend_Validate_NotEmpty();
                $notEmptyValidator->setMessage('admin_validate_error_notempty');
                $element->addValidator($notEmptyValidator);
            }
        }
    }

    /**
     * Meldet, ob Element-Namen als Label verwendet werden.
     *
     * @return bool TRUE - Element Namen werden als Label verwendet; FALSE - keine automatischen Label
     */
    public function isUseNameAsLabel()
    {
        return $this->useNameAsLabel;
    }

    /**
     * Setzt Option fuer die automatische Verwendung von Element-Namen als Label.
     *
     * @param bool $useNameAsLabel
     * @return $this
     */
    public function setUseNameAsLabel($useNameAsLabel)
    {
        $this->useNameAsLabel = $useNameAsLabel;
        return $this;
    }

    /**
     * Liefert den gesetzten Prefix fuer automatisch generierte Label.
     *
     * @return string
     */
    public function getLabelPrefix()
    {
        return $this->labelPrefix;
    }

    /**
     * Setzt den Prefix der fuer automatische Label verwendet werden soll.
     *
     * @param string $prefix
     * @return $this
     */
    public function setLabelPrefix($prefix)
    {
        $this->labelPrefix = $prefix;
        return $this;
    }

    /**
     * Returns configuration.
     *
     * @return Zend_Config
     */
    public function getApplicationConfig()
    {
        if ($this->config === null) {
            $this->config = Config::get();
        }

        return $this->config;
    }

    /**
     * Sets configuration.
     *
     * @param Zend_Config $config
     * @return $this
     */
    public function setApplicationConfig($config)
    {
        $this->config = $config;
        return $this;
    }
}
