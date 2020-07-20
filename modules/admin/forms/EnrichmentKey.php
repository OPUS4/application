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
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @author      Sascha Szott <opus-development@saschaszott.de>
 * @copyright   Copyright (c) 2008-2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Form for creating and editing an enrichment key.
 *
 * @category    Application
 * @package     Module_Admin
 */
class Admin_Form_EnrichmentKey extends Application_Form_Model_Abstract
{

    /**
     * Form element name for enrichment key name.
     */
    const ELEMENT_NAME = 'Name';

    /**
     * Form element for translation of enrichment name.
     */
    const ELEMENT_DISPLAYNAME = 'DisplayName';

    /**
     * Form element name for associated enrichment type.
     */
    const ELEMENT_TYPE = 'Type';

    /**
     * Form element name for enrichment type options.
     */
    const ELEMENT_OPTIONS = 'Options';

    /**
     * Form element name for validation option.
     */
    const ELEMENT_VALIDATION = 'Validation';

    /**
     * Pattern for checking valid enrichment key names.
     *
     * Enrichment key have to start with a letter and can use letters, numbers and '.' and '_'.
     */
    const PATTERN = '/^[a-zA-Z][a-zA-Z0-9_\.]+$/';

    /**
     * Initialize form elements.
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this->setLabelPrefix('Opus_EnrichmentKey');
        $this->setUseNameAsLabel(true);
        $this->setModelClass('Opus_EnrichmentKey');
        $this->setVerifyModelIdIsNumeric(false);

        $nameMaxLength = Opus_EnrichmentKey::getFieldMaxLength('Name');

        $name = $this->createElement('text', self::ELEMENT_NAME, [
            'required' => true,
            'label' => 'admin_enrichmentkey_label_name',
            'maxlength' => $nameMaxLength
        ]);
        $name->addValidator('regex', false, ['pattern' => self::PATTERN]);
        $name->addValidator('StringLength', false, [
            'min' => 1,
            'max' => $nameMaxLength
        ]);
        $name->addValidator(new Application_Form_Validate_EnrichmentKeyAvailable());
        $this->addElement($name);

        $this->addElement('translation', self::ELEMENT_DISPLAYNAME, [
            'required' => false, 'size' => 70, 'label' => 'DisplayName'
        ]);

        $element = $this->createElement(
            'select',
            self::ELEMENT_TYPE,
            [
                'label' => 'admin_enrichmentkey_label_type',
                'id' => 'admin_enrichmentkey_type'
            ]
        );

        // alle verfügbaren EnrichmentTypes ermitteln und als Auswahlfeld anzeigen
        $availableTypes[''] = ''; // Standardauswahl des Select-Felds soll leer sein
        $availableTypes = array_merge($availableTypes, Opus_Enrichment_AbstractType::getAllEnrichmentTypes());
        $element->setMultiOptions($availableTypes);
        $this->addElement($element);

        $element = $this->createElement(
            'textarea',
            self::ELEMENT_OPTIONS,
            [
                'label' => 'admin_enrichmentkey_label_options',
                'id' => 'admin_enrichmentkey_options',
                'description' => $this->getTranslator()->translate('admin_enrichmentkey_options_description')
            ]
        );
        $this->addElement($element);

        $element = $this->createElement(
            'checkbox',
            self::ELEMENT_VALIDATION,
            [
                'label' => 'admin_enrichmentkey_label_validation',
                'id' => 'admin_enrichmentkey_validation',
                'description' => $this->getTranslator()->translate('admin_enrichmentkey_validation_description')
            ]
        );
        $this->addElement($element);

        /* TODO OPUSVIER-3433 translation of enrichments directly in this form
        $translations = new Admin_Form_TranslationSet();

        $translations->addKey('TranslationLabel');
        $translations->addKey('TranslationDescription');

        $this->addSubForm($translations, 'Translations');
         */
    }

    /**
     * Initialisiert das Formular mit Werten einer Model-Instanz.
     * @param $model Opus_Enrichmentkey
     */
    public function populateFromModel($enrichmentKey)
    {
        $name = $enrichmentKey->getName();
        $this->getElement(self::ELEMENT_DISPLAYNAME)->populateFromTranslations(
            'Enrichment' . $name
        );

        // Enrichment-Keys haben keine numerische ID: hier wirkt der Name als Identifikator
        $this->getElement(self::ELEMENT_MODEL_ID)->setValue($name);
        $this->getElement(self::ELEMENT_NAME)->setValue($name);
        $this->getElement(self::ELEMENT_TYPE)->setValue($enrichmentKey->getType());

        $enrichmentType = $this->initEnrichmentType($enrichmentKey->getType());
        if (! is_null($enrichmentType)) {
            $enrichmentType->setOptions($enrichmentKey->getOptions());

            $optionsElement = $this->getElement(self::ELEMENT_OPTIONS);
            $optionsElement->setValue($enrichmentType->getOptionsAsString());
            $optionsElement->setDescription($enrichmentType->getDescription());

            $validationElement = $this->getElement(self::ELEMENT_VALIDATION);
            $validationElement->setValue($enrichmentType->isStrictValidation());
        }

        if (! is_null($enrichmentKey->getType())) {
            $this->setTypeFieldAsMandatory();
        }
    }

    /**
     * Setzt einen Wert im Formularlement ELEMENT_NAME.
     *
     * @param string value der einzutragende Wert
     */
    public function setNameElementValue($value)
    {
        $this->getElement(self::ELEMENT_NAME)->setValue($value);
    }

    /**
     * Aktualisiert Model-Instanz mit Werten im Formular.
     * @param $enrichmentKey Opus_Enrichmentkey
     */
    public function updateModel($enrichmentKey)
    {
        $name = $this->getElementValue(self::ELEMENT_NAME);

        $enrichmentKey->setName($name);

        $enrichmentTypeValue = $this->getElementValue(self::ELEMENT_TYPE);
        $enrichmentType = $this->initEnrichmentType($enrichmentTypeValue);
        if (! is_null($enrichmentType)) {
            $enrichmentKey->setType($enrichmentTypeValue);

            $enrichmentType->setOptionsFromString([
                'options' => $this->getElementValue(self::ELEMENT_OPTIONS),
                'validation' => $this->getElementValue(self::ELEMENT_VALIDATION)]);
            $enrichmentKey->setOptions($enrichmentType->getOptions());
        }

        // TODO detect if name has changed (change enrichment keys)
        $this->getElement(self::ELEMENT_DISPLAYNAME)->updateTranslations("Enrichment$name", 'default');
    }

    /**
     * Erzeugt ein Enrichment-Type Objekt für den übergebenen Typ-Namen bzw. liefert
     * null, wenn der Typ-Name nicht aufgelöst werden kann.
     *
     * @param $enrichmentTypeName Name des Enrichment-Typs
     *
     * @return mixed
     */
    private function initEnrichmentType($enrichmentTypeName)
    {
        if (is_null($enrichmentTypeName) || $enrichmentTypeName == '') {
            return null;
        }

        $enrichmentTypeName = 'Opus_Enrichment_' . $enrichmentTypeName;
        try {
            // TODO OPUSVIER-4161 is this check necessary?
            if (@class_exists($enrichmentTypeName)) {
                $enrichmentType = new $enrichmentTypeName();
                return $enrichmentType;
            }
            $this->getLogger()->err('could not find class ' . $enrichmentTypeName);
        } catch (\Throwable $ex) { // TODO Throwable only available in PHP 7+
            $this->getLogger()->err('could not instantiate class ' . $enrichmentTypeName . ': ' . $ex->getMessage());
        }

        return null;
    }

    public function populate(array $values)
    {
        if (array_key_exists(parent::ELEMENT_MODEL_ID, $values)) {
            $enrichmentKey = Opus_EnrichmentKey::fetchByName($values[parent::ELEMENT_MODEL_ID]);
            if (! is_null($enrichmentKey)) {
                $enrichmentType = $enrichmentKey->getEnrichmentType();
                if (! is_null($enrichmentType)) {
                    $this->setTypeFieldAsMandatory();
                }
            }
        }

        return parent::populate($values);
    }

    private function setTypeFieldAsMandatory()
    {
        // leere Auswahlmöglichkeit im Select-Feld für Type wird nicht angeboten (Pflichtfeld)
        $element = $this->getElement(self::ELEMENT_TYPE);
        $element->removeMultiOption('');
        $element->setRequired(true);
        $this->applyCustomMessages($element);
    }
}
