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

use Opus\Common\Date;
use Opus\Common\Model\ModelInterface;
use Opus\Common\Person;

/**
 * Form for editing a person across multiple objects.
 *
 * This form can show multiple values per field. The values are available for selection or a new value can be provided.
 * Each field is paired with a checkbox to enable or disable it for the update.
 *
 * The form renders additional checkbox inputs for each field. When populating the form those values are used to set
 * the attribute 'active' for each element. That attribute is used to determine if the field should be updated in all
 * matching objects when the form is saved.
 */
class Admin_Form_Persons extends Application_Form_Model_Abstract
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
     * Konstante für Hash, der Formular identifiziert
     */
    public const ELEMENT_FORM_ID = 'FormId';

    /** @var array Identity values for person */
    private $person;

    /**
     * Erzeugt die Formularelemente.
     */
    public function init()
    {
        parent::init();

        $this->setDecorators(
            [
                ['FormErrors', ['onlyCustomFormErrors' => true, 'ignoreSubForms' => true]],
                'FormElements',
                'Fieldset',
                'Form',
                ['FormHelp', ['message' => 'admin_person_edit_help']],
            ]
        );

        $this->addElement('combobox', self::ELEMENT_ACADEMIC_TITLE, ['label' => 'AcademicTitle']);

        $fieldLastName = Person::describeField(Person::FIELD_LAST_NAME);

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

        $email = $this->createElement('combobox', self::ELEMENT_EMAIL, ['label' => 'Email']);
        $email->addValidator(new Application_Form_Validate_EmailAddress());
        $this->addElement($email);

        $this->addElement('combobox', self::ELEMENT_PLACE_OF_BIRTH, ['label' => 'PlaceOfBirth', 'size' => 40]);

        $dateOfBirth = $this->createElement('combobox', self::ELEMENT_DATE_OF_BIRTH, ['label' => 'DateOfBirth']);
        $dateOfBirth->addValidator(new Application_Form_Validate_Date());
        $this->addElement($dateOfBirth);

        $this->addElement('text', self::ELEMENT_IDENTIFIER_GND, ['label' => 'IdentifierGnd', 'size' => 40]);
        $this->addElement('text', self::ELEMENT_IDENTIFIER_ORCID, ['label' => 'IdentifierOrcid', 'size' => 40]);
        $this->addElement('text', self::ELEMENT_IDENTIFIER_MISC, ['label' => 'IdentifierMisc', 'size' => 40]);

        $this->getElement(self::ELEMENT_IDENTIFIER_GND)->addValidator(new Application_Form_Validate_Gnd());
        $this->getElement(self::ELEMENT_IDENTIFIER_ORCID)->addValidator(new Application_Form_Validate_Orcid());

        $this->removeElement(self::ELEMENT_MODEL_ID); // form represents multiple objects (ids)

        $this->addUpdateFieldDecorator();

        $save = $this->getElement('Save');
        $save->setLabel('form_button_next');

        $formId = $this->createElement('hidden', self::ELEMENT_FORM_ID);
        $formId->setValue(uniqid());
        $this->addElement($formId);
    }

    /**
     * Set decorators for all input elements to add 'UpdateField'.
     *
     * Decorator 'UpdateField' will render a checkbox for a element. The checkbox can be used to enable or disable the
     * updating of the database for this field.
     */
    public function addUpdateFieldDecorator()
    {
        $elements = $this->getElements();

        foreach ($elements as $key => $element) {
            $decorators = $element->getDecorators();
            $index      = array_search('Zend_Form_Decorator_Errors', array_keys($decorators));

            // array_splice($decorators, $index + 1, 0, array('UpdateField' => 'Test'));

            if ($index !== false) {
                $element->setDecorators([
                    'ViewHelper',
                    'Description',
                    'ElementHint',
                    'Errors',
                    'UpdateField',
                    'ElementHtmlTag',
                    ['LabelNotEmpty', ['tag' => 'div', 'tagClass' => 'label', 'placement' => 'prepend']],
                    [['dataWrapper' => 'HtmlTagWithId'], ['tag' => 'div', 'class' => 'data-wrapper']],
                ]);
            }
        }
    }

    /**
     * Looks at UpdateEnabled values to set active attribute of elements.
     */
    public function populate(array $values)
    {
        parent::populate($values);

        foreach ($values as $key => $value) {
            if (strpos($key, 'UpdateEnabled')) {
                if (strtolower($value) === 'on') {
                    $elemName = preg_filter('/(.*)UpdateEnabled/', '$1', $key);

                    $element = $this->getElement($elemName);

                    if ($element !== null) {
                        $element->setAttrib('active', true);
                    }
                }
            }
        }
    }

    /**
     * @param array $person
     */
    public function setPerson($person)
    {
        $this->person = $person;
    }

    /**
     * @return array
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * Setzt die Werte der Formularelmente entsprechend der uebergebenen Person Instanz.
     *
     * @param array $values
     */
    public function populateFromModel($values)
    {
        // make sure all keys exist
        $validNames = [
            'first_name',
            'last_name',
            'identifier_gnd',
            'identifier_orcid',
            'identifier_misc',
            'place_of_birth',
            'date_of_birth',
            'academic_title',
            'email',
        ];

        $defaults = array_fill_keys($validNames, null);
        $values   = array_merge($defaults, $values);

        // set elements with single values (normally)
        // TODO will change for first and last name (once only IDs count)
        $this->setIdentityValue($this->getElement(self::ELEMENT_FIRST_NAME), $values['first_name']);
        $this->setIdentityValue($this->getElement(self::ELEMENT_LAST_NAME), $values['last_name']);
        $this->setIdentityValue($this->getElement(self::ELEMENT_IDENTIFIER_GND), $values['identifier_gnd']);
        $this->setIdentityValue($this->getElement(self::ELEMENT_IDENTIFIER_ORCID), $values['identifier_orcid']);
        $this->setIdentityValue($this->getElement(self::ELEMENT_IDENTIFIER_MISC), $values['identifier_misc']);

        // set elements that can have multiple values
        $this->getElement(self::ELEMENT_PLACE_OF_BIRTH)->setAutocompleteValues($values['place_of_birth']);
        $this->getElement(self::ELEMENT_ACADEMIC_TITLE)->setAutocompleteValues($values['academic_title']);
        $this->getElement(self::ELEMENT_EMAIL)->setAutocompleteValues($values['email']);

        $dates = $values['date_of_birth'];

        if ($dates !== null) {
            if (! is_array($dates)) {
                $dates = [$dates];
            }

            $formattedDates = [];

            $datesHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('dates');

            foreach ($dates as $date) {
                $opusDate = new Date($date);
                array_push($formattedDates, $datesHelper->getDateString($opusDate));
            }

            $this->getElement(self::ELEMENT_DATE_OF_BIRTH)->setAutocompleteValues($formattedDates);
        }
    }

    /**
     * Sets value for identity field, adding a note if multiple values are present.
     *
     * @param Zend_Form_Element $element
     * @param string|array      $value
     *
     * TODO handle $value !== $person[] - can it happen?
     */
    public function setIdentityValue($element, $value)
    {
        if (is_array($value)) {
            $element->setHint($this->getTranslator()->translate('admin_persons_values_not_trimmed'));
            $displayValue = $value[0];
        } else {
            $displayValue = $value;
        }

        $person = $this->getPerson();

        if ($person !== null) {
            $persons = Person::new();
            // TODO code should not depend on convertFieldnameToColumn (Framework internals)
            $columnName = $persons->convertFieldnameToColumn($element->getName());
            if (array_key_exists($columnName, $person)) {
                $displayValue = $person[$columnName];
            }
        }

        $element->setValue($displayValue);
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
     * @param array $values
     */
    public function updateModel($values)
    {
    }

    /**
     * Returns array with changed values for person objects.
     *
     * @return array
     */
    public function getChanges()
    {
        $elements = $this->getElements();

        $changes = [];

        foreach ($elements as $element) {
            if ($element->getAttrib('active')) {
                $value = $element->getValue();

                if ($element->getName() === self::ELEMENT_DATE_OF_BIRTH) {
                    // TODO this date conversion stuff is still too complicated
                    $dateHelper = new Application_Controller_Action_Helper_Dates();
                    $date       = $dateHelper->getOpusDate($value); // get a date with time
                    if ($date !== null) {
                        $date->setDateOnly($date->getDateTime()); // remove time
                        $value = $date->__toString(); // get properly formatted string
                    } else {
                        $value = null;
                    }
                }

                if ($value === null || strlen(trim($value)) === 0) {
                    $value = null;
                }

                $changes[$element->getName()] = $value;
            }
        }

        return $changes;
    }

    /**
     * Liefert Instanz von Person zurueck.
     *
     * @return null|ModelInterface
     */
    public function getModel()
    {
        return null;
    }

    /**
     * @param array      $data
     * @param array|null $context
     * @return bool
     * @throws Zend_Form_Exception
     */
    public function isValid($data, $context = null)
    {
        $result = parent::isValid($data, $context);

        $update = false;

        foreach ($data as $fieldName => $value) {
            if (strpos($fieldName, 'UpdateEnabled') !== false && stripos($value, 'on') !== false) {
                $update = true;
                break;
            }
        }

        if (! $update) {
            $this->addErrorMessage('admin_person_error_no_update');
        }

        return $result && $update;
    }
}
