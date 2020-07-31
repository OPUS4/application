<?php
/*
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
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Unterformular fuer das Editieren eines Stichwortes.
 *
 * @category    Application
 * @package     Module_Admin
 */
class Admin_Form_Document_Subject extends Admin_Form_AbstractModelSubForm
{

    /**
     * Name von Formularelement für Schlagwort-ID in Datenbank.
     */
    const ELEMENT_ID = 'Id';

    /**
     * Name von Formularelement für Sprache von Schlagwort.
     */
    const ELEMENT_LANGUAGE = 'Language';

    /**
     * Name von Formularelement für Wert von Schlagwort.
     */
    const ELEMENT_VALUE = 'Value';

    /**
     * Name von Formularelement für externen Schlüssel für Schlagwort.
     */
    const ELEMENT_EXTERNAL_KEY = 'ExternalKey';

    /**
     * Typ des angezeigten Schlagworts.
     *
     * Der Typ eines Schlagworts kann nachträglich nicht mehr geändert werden, deshalb gibt es dafür kein
     * Formularelement.
     *
     * @var string
     */
    private $_subjectType;

    /**
     * Sprache des Schlagworts.
     *
     * GND Schlagwörter sind immer Deutsch. Für sie wird die Sprache in dieser Variable gespeichert und kein
     * Formularelement angezeigt.
     *
     * @var string
     */
    private $_language;

    /**
     * Konstruiert das Formular.
     *
     * Der Typ kommt vom übergeordneten Formular und muss daher auch nicht beim POST mit übermittelt werden.
     *
     * @param string $type Typ des Schlagwortes
     * @param string $language Sprache für das Schlagwort, wenn nicht editierbar
     * @param array $options Weitere Optionen (für Zend_Form_SubForm)
     */
    public function __construct($type, $language = null, $options = null)
    {
        $this->_subjectType = $type;
        $this->_language = $language;
        parent::__construct($options);
    }

    /**
     * Initialsiert die Formularelemente.
     */
    public function init()
    {
        parent::init();

        $this->addElement('Hidden', self::ELEMENT_ID);

        // wenn die Sprache gesetzt wurde wird kein sichtbares Formularelement erzeugt
        if (is_null($this->_language)) {
            $element = $this->createElement('Language', self::ELEMENT_LANGUAGE);
        } else {
            $element = $this->createElement('Hidden', self::ELEMENT_LANGUAGE, ['value' => $this->_language]);
        }
        $this->addElement($element);

        $this->addElement('Text', self::ELEMENT_VALUE, [
            'required' => true, 'size' => 30, 'class' => 'subject ui-autocomplete-input'
        ]);
        $this->addElement('Text', self::ELEMENT_EXTERNAL_KEY);
    }

    /**
     * Initialisiert das Formular mit den Werten in einem Opus_Subject Objekt.
     * @param \Opus_Subject $subject
     */
    public function populateFromModel($subject)
    {
        $this->getElement(self::ELEMENT_ID)->setValue($subject->getId());
        $this->getElement(self::ELEMENT_LANGUAGE)->setValue($subject->getLanguage());
        $this->getElement(self::ELEMENT_VALUE)->setValue($subject->getValue());
        $this->getElement(self::ELEMENT_EXTERNAL_KEY)->setValue($subject->getExternalKey());
    }

    /**
     * Überträgt die Werte im Formular in ein Opus_Subject Objekt.
     * @param \Opus_Subject $subject
     */
    public function updateModel($subject)
    {
        $subject->setLanguage($this->getElementValue(self::ELEMENT_LANGUAGE));
        $subject->setValue($this->getElementValue(self::ELEMENT_VALUE));
        $subject->setExternalKey($this->getElementValue(self::ELEMENT_EXTERNAL_KEY));
        $subject->setType($this->_subjectType);
    }

    /**
     * Liefert das angezeigt Model zurück.
     *
     * Wenn ein neues Subject zum Formular hinzugefügt wurde wird ein new Opus_Subject Objekt ohne ID zurückgeliefert.
     *
     * @return \Opus_Subject
     */
    public function getModel()
    {
        $subjectId = $this->getElement(self::ELEMENT_ID)->getValue();

        if (strlen(trim($subjectId)) == 0 || ! is_numeric($subjectId)) {
            $subjectId = null;
        }

        try {
            $subject = new Opus_Subject($subjectId);
        } catch (Opus_Model_NotFoundException $omnfe) {
            $this->getLogger()->err(__METHOD__ . " Unknown subject ID = '$subjectId'.");
            $subject = new Opus_Subject();
        }

        $this->updateModel($subject);

        return $subject;
    }

    /**
     * Lädt die notwendigen Dekoratoren.
     *
     * Der 'FieldSet' Dekorator wird entfernt, damit nicht um jedes einzelne Subject ein Fieldset angezeigt wird.
     */
    public function loadDefaultDecorators()
    {
        parent::loadDefaultDecorators();

        $this->removeDecorator('Fieldset');
    }

    /**
     * Liefert den Schlagworttyp für dieses Unterformular zurück.
     * @return string Schlagworttyp
     */
    public function getSubjectType()
    {
        return $this->_subjectType;
    }

    /**
     * Liefert die festgelegte Sprache (bei SWD/GND) für dieses Unterformular zurück.
     * @return null|string Sprache
     */
    public function getLanguage()
    {
        return $this->_language;
    }

    protected function _removeElements()
    {
        $this->removeElement(Admin_Form_Document_MultiSubForm::ELEMENT_REMOVE);
    }
}
