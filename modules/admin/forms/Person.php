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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Person;
use Opus\Common\PersonInterface;

/**
 * Formular zum Editieren einer Person (Person).
 *
 * Dieses Formular beruecksichtigt nicht die Felder, die bei der Verknuepfung einer Person mit einem Dokument in dem
 * Link Objekt hinzukommen.
 */
class Admin_Form_Person extends Admin_Form_AbstractDocumentSubForm
{
    /**
     * Name fuer Formularelement fuer Feld ID von Person.
     */
    public const ELEMENT_PERSON_ID = 'PersonId';

    /**
     * Name fuer Formularelement fuer Feld AcademicTitle.
     */
    public const ELEMENT_ACADEMIC_TITLE = 'AcademicTitle';

    /**
     * Name fuer Formularelement fuer Feld LastName.
     */
    public const ELEMENT_LAST_NAME = 'LastName';

    /**
     * Name fuer Formularelement fuer Feld FirstName.
     */
    public const ELEMENT_FIRST_NAME = 'FirstName';

    /**
     * Name fuer Formularelement fuer Feld Email.
     */
    public const ELEMENT_EMAIL = 'Email';

    /**
     * Name fuer Formularelement fuer Feld PlaceOfBirth.
     */
    public const ELEMENT_PLACE_OF_BIRTH = 'PlaceOfBirth';

    /**
     * Name fuer Formularelement fuer Feld DateOfBirth.
     */
    public const ELEMENT_DATE_OF_BIRTH = 'DateOfBirth';

    /**
     * Name fuer Button zum Speichern.
     */
    public const ELEMENT_SAVE = 'Save';

    /**
     * Name fuer Button zum Abbrechen.
     */
    public const ELEMENT_CANCEL = 'Cancel';

    /**
     * Konstante fuer POST Ergebnis 'abspeichern'.
     */
    public const RESULT_SAVE = 'save';

    /**
     * Konstante fuer POST Ergebnis 'abbrechen'.
     */
    public const RESULT_CANCEL = 'cancel';

    /**
     * Konstante für Identifier Gnd
     */
    public const ELEMENT_IDENTIFIER_GND = 'IdentifierGnd';

    /**
     * Konstante für Identifier OrcId
     */
    public const ELEMENT_IDENTIFIER_ORCID = 'IdentifierOrcid';

    /**
     * Konstante für Identifier Misc
     */
    public const ELEMENT_IDENTIFIER_MISC = 'IdentifierMisc';

    /**
     * Erzeugt die Formularelemente.
     */
    public function init()
    {
        parent::init();

        $this->setDecorators(
            [
                'FormElements',
                'Fieldset',
                [['divWrapper' => 'HtmlTag'], ['tag' => 'div', 'class' => 'subform']],
                'Form',
            ]
        );

        $fieldLastName = Person::describeField(Person::FIELD_LAST_NAME);

        $this->addElement('hidden', self::ELEMENT_PERSON_ID, ['size' => '40']);
        $this->addElement('text', self::ELEMENT_ACADEMIC_TITLE, ['label' => 'AcademicTitle']);
        $this->addElement(
            'text',
            self::ELEMENT_LAST_NAME,
            [
                'label'     => 'LastName',
                'required'  => true,
                'size'      => 50,
                'maxlength' => $fieldLastName->getMaxSize(),
            ]
        );
        $this->addElement('text', self::ELEMENT_FIRST_NAME, ['label' => 'FirstName', 'size' => 50]);
        $this->addElement('Email', self::ELEMENT_EMAIL, ['label' => 'Email']);
        $this->addElement('text', self::ELEMENT_PLACE_OF_BIRTH, ['label' => 'PlaceOfBirth', 'size' => 40]);
        $this->addElement('date', self::ELEMENT_DATE_OF_BIRTH, ['label' => 'DateOfBirth']);
        $this->addElement('text', self::ELEMENT_IDENTIFIER_GND, ['label' => 'IdentifierGnd', 'size' => 40]);
        $this->addElement('text', self::ELEMENT_IDENTIFIER_ORCID, ['label' => 'IdentifierOrcid', 'size' => 40]);
        $this->addElement('text', self::ELEMENT_IDENTIFIER_MISC, ['label' => 'IdentifierMisc', 'size' => 40]);

        $this->getElement(self::ELEMENT_IDENTIFIER_GND)->addValidator(new Application_Form_Validate_Gnd());
        $this->getElement(self::ELEMENT_IDENTIFIER_ORCID)->addValidator(new Application_Form_Validate_Orcid());

        $this->addDisplayGroup(
            $this->getElements(),
            'fields',
            [
                'decorators' => [
                    'FormElements',
                    [['fieldsWrapper' => 'HtmlTag'], ['tag' => 'div', 'class' => 'fields-wrapper']],
                ],
            ]
        );

        $this->addElement(
            'submit',
            self::ELEMENT_SAVE,
            [
                'decorators' => [
                    'ViewHelper',
                    [['liWrapper' => 'HtmlTag'], ['tag' => 'li', 'class' => 'save-element']],
                ],
            ]
        );
        $this->addElement(
            'submit',
            self::ELEMENT_CANCEL,
            [
                'decorators' => [
                    'ViewHelper',
                    [['liWrapper' => 'HtmlTag'], ['tag' => 'li', 'class' => 'cancel-element']],
                ],
            ]
        );
        $this->addDisplayGroup(
            [self::ELEMENT_SAVE, self::ELEMENT_CANCEL],
            'actions',
            [
                'order'      => 100,
                'decorators' => [
                    'FormElements',
                    [['ulWrapper' => 'HtmlTag'], ['tag' => 'ul', 'class' => 'form-action']],
                    [['divWrapper' => 'HtmlTag'], ['id' => 'form-action']],
                ],
            ]
        );
    }

    /**
     * Setzt die Werte der Formularelmente entsprechend der uebergebenen Person Instanz.
     *
     * @param PersonInterface $person
     */
    public function populateFromModel($person)
    {
        $datesHelper = $this->getDatesHelper();

        $this->getElement(self::ELEMENT_PERSON_ID)->setValue($person->getId());
        $this->getElement(self::ELEMENT_ACADEMIC_TITLE)->setValue($person->getAcademicTitle());
        $this->getElement(self::ELEMENT_FIRST_NAME)->setValue($person->getFirstName());
        $this->getElement(self::ELEMENT_LAST_NAME)->setValue($person->getLastName());
        $this->getElement(self::ELEMENT_PLACE_OF_BIRTH)->setValue($person->getPlaceOfBirth());
        $this->getElement(self::ELEMENT_IDENTIFIER_GND)->setValue($person->getIdentifierGnd());
        $this->getElement(self::ELEMENT_IDENTIFIER_ORCID)->setValue($person->getIdentifierOrcid());
        $this->getElement(self::ELEMENT_IDENTIFIER_MISC)->setValue($person->getIdentifierMisc());
        $date = $person->getDateOfBirth();
        $this->getElement(self::ELEMENT_DATE_OF_BIRTH)->setValue($datesHelper->getDateString($date));
        $this->getElement(self::ELEMENT_EMAIL)->setValue($person->getEmail());
    }

    /**
     * Ermittelt bei einem Post welcher Button geklickt wurde, also welche Aktion gewünscht ist.
     *
     * @param array $post
     * @param array $context
     * @return string|null String fuer gewuenschte Operation
     */
    public function processPost($post, $context)
    {
        if (array_key_exists(self::ELEMENT_SAVE, $post)) {
            return self::RESULT_SAVE;
        } elseif (array_key_exists(self::ELEMENT_CANCEL, $post)) {
            return self::RESULT_CANCEL;
        }

        return null;
    }

    /**
     * Setzt die Felder einer Person Instanz entsprechend dem Formularinhalt.
     *
     * @param PersonInterface $model
     */
    public function updateModel($model)
    {
        if ($model instanceof PersonInterface) {
            $model->setAcademicTitle($this->getElementValue(self::ELEMENT_ACADEMIC_TITLE));
            $model->setLastName($this->getElementValue(self::ELEMENT_LAST_NAME));
            $model->setFirstName($this->getElementValue(self::ELEMENT_FIRST_NAME));
            $model->setEmail($this->getElementValue(self::ELEMENT_EMAIL));
            $model->setPlaceOfBirth($this->getElementValue(self::ELEMENT_PLACE_OF_BIRTH));
            $model->setIdentifierGnd($this->getElementValue(self::ELEMENT_IDENTIFIER_GND));
            $model->setIdentifierOrcid($this->getElementValue(self::ELEMENT_IDENTIFIER_ORCID));
            $model->setIdentifierMisc($this->getElementValue(self::ELEMENT_IDENTIFIER_MISC));
            $datesHelper = $this->getDatesHelper();
            $model->setDateOfBirth($datesHelper->getOpusDate($this->getElementValue(self::ELEMENT_DATE_OF_BIRTH)));
        } else {
            $this->getLogger()->err(__METHOD__ . ' called with object that is not instance of PersonInterface');
        }
    }

    /**
     * Liefert Instanz von Person zurueck.
     *
     * @return PersonInterface
     */
    public function getModel()
    {
        $personId = $this->getElementValue(self::ELEMENT_PERSON_ID);

        if (is_numeric($personId)) {
            $person = Person::get($personId);
        } else {
            $person = Person::new();
        }

        $this->updateModel($person);

        return $person;
    }
}
