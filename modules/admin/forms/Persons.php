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
 * @author      Michael Lang <lang@zib.de>
 * @copyright   Copyright (c) 2008-2014, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

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
     * Name fuer Formularelement fuer Feld ID von Opus_Person.
     */
    const ELEMENT_PERSON_ID = 'PersonId';

    /**
     * Name fuer Formularelement fuer Feld AcademicTitle.
     */
    const ELEMENT_ACADEMIC_TITLE = 'AcademicTitle';

    /**
     * Name fuer Formularelement fuer Feld LastName.
     */
    const ELEMENT_LAST_NAME = 'LastName';

    /**
     * Name fuer Formularelement fuer Feld FirstName.
     */
    const ELEMENT_FIRST_NAME = 'FirstName';

    /**
     * Name fuer Formularelement fuer Feld Email.
     */
    const ELEMENT_EMAIL = 'Email';

    /**
     * Name fuer Formularelement fuer Feld PlaceOfBirth.
     */
    const ELEMENT_PLACE_OF_BIRTH = 'PlaceOfBirth';

    /**
     * Name fuer Formularelement fuer Feld DateOfBirth.
     */
    const ELEMENT_DATE_OF_BIRTH = 'DateOfBirth';

    /**
     * Konstante für Identifier Gnd
     */
    const ELEMENT_IDENTIFIER_GND = 'IdentifierGnd';

    /**
     * Konstante für Identifier OrcId
     */
    const ELEMENT_IDENTIFIER_ORCID = 'IdentifierOrcid';

    /**
     * Konstante für Identifier Misc
     */
    const ELEMENT_IDENTIFIER_MISC = 'IdentifierMisc';

    /**
     * Erzeugt die Formularelemente.
     */
    public function init() {
        parent::init();

        $this->setDecorators(
            array(
            'FormElements',
            'Fieldset',
            array(array('divWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'subform')),
            'Form'
            )
        );

        $this->addElement('combobox', self::ELEMENT_ACADEMIC_TITLE, array('label' => 'AcademicTitle'));

        $this->addElement(
            'text', self::ELEMENT_LAST_NAME,
            array('label' => 'LastName', 'required' => true, 'size' => 50)
        );
        $this->addElement('text', self::ELEMENT_FIRST_NAME, array('label' => 'FirstName', 'size' => 50));

        $email = $this->createElement('combobox', self::ELEMENT_EMAIL, array('label' => 'Email'));
        $email->addValidator('EmailAddress');
        $this->addElement($email);

        $this->addElement('combobox', self::ELEMENT_PLACE_OF_BIRTH, array('label' => 'PlaceOfBirth', 'size' => 40));

        $dateOfBirth = $this->createElement('combobox', self::ELEMENT_DATE_OF_BIRTH, array('label' => 'DateOfBirth'));
        $dateOfBirth->addValidator(new Application_Form_Validate_Date());
        $this->addElement($dateOfBirth);

        $this->addElement('text', self::ELEMENT_IDENTIFIER_GND, array('label' => 'IdentifierGnd', 'size' => 40));
        $this->addElement('text', self::ELEMENT_IDENTIFIER_ORCID, array('label' => 'IdentifierOrcid', 'size' => 40));
        $this->addElement('text', self::ELEMENT_IDENTIFIER_MISC, array('label' => 'IdentifierMisc', 'size' => 40));

        $this->getElement(self::ELEMENT_IDENTIFIER_GND)->addValidator(new Application_Form_Validate_Gnd());
        $this->getElement(self::ELEMENT_IDENTIFIER_ORCID)->addValidator(new Application_Form_Validate_Orcid());

        $this->removeElement(self::ELEMENT_MODEL_ID); // form represents multiple objects (ids)

        $this->addUpdateFieldDecorator();

        $save = $this->getElement('Save');
        $save->setLabel('form_button_next');
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

        foreach ($elements as $key => $element)
        {
            $decorators = $element->getDecorators();
            $index = array_search('Zend_Form_Decorator_Errors', array_keys($decorators));

            // array_splice($decorators, $index + 1, 0, array('UpdateField' => 'Test'));

            if ($index !== false)
            {
                $element->setDecorators(array(
                    'ViewHelper',
                    'Description',
                    'Errors',
                    'UpdateField',
                    'ElementHtmlTag',
                    array('LabelNotEmpty', array('tag' => 'div', 'tagClass' => 'label', 'placement' => 'prepend')),
                    array(array('dataWrapper' => 'HtmlTagWithId'), array('tag' => 'div', 'class' => 'data-wrapper'))
                ));

            }
        }
    }

    /**
     * Looks at UpdateEnabled values to set active attribute of elements.
     *
     * @param array $post
     */
    public function populate(array $values)
    {
        parent::populate($values);

        foreach ($values as $key => $value)
        {
            if (strpos($key, 'UpdateEnabled'))
            {
                if (strtolower($value) == 'on')
                {
                    $elemName = preg_filter('/(.*)UpdateEnabled/', '$1', $key);

                    $element = $this->getElement($elemName);

                    if (!is_null($element))
                    {
                        $element->setAttrib('active', true);
                    }
                }
            }
        }
    }

    /**
     * Setzt die Werte der Formularelmente entsprechend der uebergebenen Opus_Person Instanz.
     * @param Opus_Person $model
     */
    public function populateFromModel($values) {
        // set elements with single values
        // TODO will change for first and last name (once only IDs count)
        $this->getElement(self::ELEMENT_FIRST_NAME)->setValue($values['first_name']);
        $this->getElement(self::ELEMENT_LAST_NAME)->setValue($values['last_name']);
        $this->getElement(self::ELEMENT_IDENTIFIER_GND)->setValue($values['identifier_gnd']);
        $this->getElement(self::ELEMENT_IDENTIFIER_ORCID)->setValue($values['identifier_orcid']);
        $this->getElement(self::ELEMENT_IDENTIFIER_MISC)->setValue($values['identifier_misc']);

        // set elements that can have multiple values
        $this->getElement(self::ELEMENT_PLACE_OF_BIRTH)->setAutocompleteValues($values['place_of_birth']);
        $this->getElement(self::ELEMENT_ACADEMIC_TITLE)->setAutocompleteValues($values['academic_title']);
        $this->getElement(self::ELEMENT_EMAIL)->setAutocompleteValues($values['email']);

        $dates = $values['date_of_birth'];

        if (!is_null($dates) && is_array($dates))
        {
            $formattedDates = array();

            $datesHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('dates');

            foreach ($dates as $date)
            {
                $opusDate = new Opus_Date($date);
                array_push($formattedDates, $datesHelper->getDateString($opusDate));
            }

            $this->getElement(self::ELEMENT_DATE_OF_BIRTH)->setAutocompleteValues($formattedDates);
        }
        else
        {
            $this->getElement(self::ELEMENT_DATE_OF_BIRTH)->setValue($dates);
        }
    }

    /**
     * Ermittelt bei einem Post welcher Button geklickt wurde, also welche Aktion gewünscht ist.
     * @param array $post
     * @param array $context
     * @return string String fuer gewuenschte Operation
     */
    public function processPost($post, $context) {
        if (array_key_exists(self::ELEMENT_SAVE, $post)) {
            return self::RESULT_SAVE;
        }
        else if (array_key_exists(self::ELEMENT_CANCEL, $post)) {
            return self::RESULT_CANCEL;
        }

        return null;
    }

    /**
     * Setzt die Felder einer Opus_Person Instanz entsprechend dem Formularinhalt.
     * @param Opus_Person $model
     */
    public function updateModel($model) {
    }

    /**
     * Returns array with changed values for person objects.
     */
    public function getChanges()
    {
        $elements = $this->getElements();

        $changes = array();

        foreach ($elements as $element)
        {
            if ($element->getAttrib('active'))
            {
                $value = $element->getValue();

                if (strlen(trim($value)) == 0)
                {
                    $value = null;
                }

                $changes[$element->getName()] = $value;
            }
        }

        return $changes;
    }

    /**
     * Liefert Instanz von Opus_Person zurueck.
     * @return \Opus_Person
     */
    public function getModel() {
    }

}
