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

use Opus\Common\EnrichmentKey;
use Opus\Common\EnrichmentKeyInterface;
use Opus\Enrichment\AbstractType;
use Opus\Enrichment\TypeInterface;

/**
 * Form for creating and editing an enrichment key.
 */
class Admin_Form_EnrichmentKey extends Application_Form_Model_Abstract
{
    /**
     * Form element name for enrichment key name.
     */
    public const ELEMENT_NAME = 'Name';

    /**
     * Form element for translation of enrichment name.
     */
    public const ELEMENT_DISPLAYNAME = 'DisplayName';

    /**
     * Form element name for associated enrichment type.
     */
    public const ELEMENT_TYPE = 'Type';

    /**
     * Form element name for enrichment type options.
     */
    public const ELEMENT_OPTIONS = 'Options';

    /**
     * Form element name for validation option.
     */
    public const ELEMENT_VALIDATION = 'Validation';

    /**
     * Pattern for checking valid enrichment key names.
     *
     * Enrichment key have to start with a letter and can use letters, numbers and '.' and '_'.
     */
    public const PATTERN = '/^[a-zA-Z][a-zA-Z0-9_\.]+$/';

    /**
     * Initialize form elements.
     *
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this->setLabelPrefix('Opus_EnrichmentKey');
        $this->setUseNameAsLabel(true);
        $this->setModelClass(EnrichmentKey::class);
        $this->setVerifyModelIdIsNumeric(false);

        $nameMaxLength = EnrichmentKey::describeField(EnrichmentKey::FIELD_NAME)->getMaxSize();

        $name = $this->createElement('text', self::ELEMENT_NAME, [
            'required'  => true,
            'label'     => 'admin_enrichmentkey_label_name',
            'maxlength' => $nameMaxLength,
        ]);
        $name->addValidator('regex', false, ['pattern' => self::PATTERN]);
        $name->addValidator('StringLength', false, [
            'min' => 1,
            'max' => $nameMaxLength,
        ]);
        $name->addValidator(new Application_Form_Validate_EnrichmentKeyAvailable());
        $this->addElement($name);

        $this->addElement('translation', self::ELEMENT_DISPLAYNAME, [
            'required' => false,
            'size'     => 70,
            'label'    => 'DisplayName',
        ]);

        $element = $this->createElement(
            'select',
            self::ELEMENT_TYPE,
            [
                'label' => 'admin_enrichmentkey_label_type',
                'id'    => 'admin_enrichmentkey_type',
            ]
        );

        // alle verfügbaren EnrichmentTypes ermitteln und als Auswahlfeld anzeigen
        $availableTypes[''] = ''; // Standardauswahl des Select-Felds soll leer sein
        $availableTypes     = array_merge($availableTypes, AbstractType::getAllEnrichmentTypes());
        $element->setMultiOptions($availableTypes);
        $this->addElement($element);

        $element = $this->createElement(
            'textarea',
            self::ELEMENT_OPTIONS,
            [
                'label'       => 'admin_enrichmentkey_label_options',
                'id'          => 'admin_enrichmentkey_options',
                'description' => $this->getTranslator()->translate('admin_enrichmentkey_options_description'),
            ]
        );
        $this->addElement($element);

        $element = $this->createElement(
            'checkbox',
            self::ELEMENT_VALIDATION,
            [
                'label'       => 'admin_enrichmentkey_label_validation',
                'id'          => 'admin_enrichmentkey_validation',
                'description' => $this->getTranslator()->translate('admin_enrichmentkey_validation_description'),
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
     *
     * @param EnrichmentKeyInterface $enrichmentKey
     */
    public function populateFromModel($enrichmentKey)
    {
        $name = $enrichmentKey->getName();

        if ($name !== null) {
            $this->getElement(self::ELEMENT_DISPLAYNAME)->populateFromTranslations(
                'Enrichment' . $name
            );
        }

        // Enrichment-Keys haben keine numerische ID: hier wirkt der Name als Identifikator
        $this->getElement(self::ELEMENT_MODEL_ID)->setValue($name);
        $this->getElement(self::ELEMENT_NAME)->setValue($name);
        $this->getElement(self::ELEMENT_TYPE)->setValue($enrichmentKey->getType());

        $enrichmentType = $this->initEnrichmentType($enrichmentKey->getType());
        if ($enrichmentType !== null) {
            // TODO this should not be necessary - The EnrichmentType defines the options, but the options are not for
            //      the type, but for the EnrichmentKey.
            $enrichmentType->setOptions($enrichmentKey->getOptions());

            $optionsElement = $this->getElement(self::ELEMENT_OPTIONS);
            $optionsElement->setValue($enrichmentType->getOptionsAsString());
            $optionsElement->setDescription($enrichmentType->getDescription());

            $validationElement = $this->getElement(self::ELEMENT_VALIDATION);
            $validationElement->setValue($enrichmentType->isStrictValidation());
        }

        if ($enrichmentKey->getType() !== null) {
            $this->setTypeFieldAsMandatory();
        }
    }

    /**
     * Setzt einen Wert im Formularlement ELEMENT_NAME.
     *
     * @param string $value Einzutragender Wert
     */
    public function setNameElementValue($value)
    {
        $this->getElement(self::ELEMENT_NAME)->setValue($value);
    }

    /**
     * Aktualisiert Model-Instanz mit Werten im Formular.
     *
     * @param EnrichmentKeyInterface $enrichmentKey
     */
    public function updateModel($enrichmentKey)
    {
        $oldName = $this->getElementValue(self::ELEMENT_MODEL_ID);

        $name = $this->getElementValue(self::ELEMENT_NAME);

        $enrichmentKey->setName($name);

        $enrichmentTypeValue = $this->getElementValue(self::ELEMENT_TYPE);
        $enrichmentType      = $this->initEnrichmentType($enrichmentTypeValue);
        if ($enrichmentType !== null) {
            $enrichmentKey->setType($enrichmentTypeValue);

            $enrichmentType->setOptionsFromString([
                'options'    => $this->getElementValue(self::ELEMENT_OPTIONS),
                'validation' => $this->getElementValue(self::ELEMENT_VALIDATION),
            ]);
            $enrichmentKey->setOptions($enrichmentType->getOptions());
        }

        // update translation keys for enrichment
        $this->getElement(self::ELEMENT_DISPLAYNAME)->updateTranslations(
            "Enrichment$name",
            'default',
            "Enrichment$oldName"
        );

        $helper = new Admin_Model_EnrichmentKeys();
        $helper->createTranslations($name, $oldName);
    }

    /**
     * Erzeugt ein Enrichment-Type Objekt für den übergebenen Typ-Namen bzw. liefert
     * null, wenn der Typ-Name nicht aufgelöst werden kann.
     *
     * @param string $enrichmentTypeName Name des Enrichment-Typs
     * @return TypeInterface|null
     */
    private function initEnrichmentType($enrichmentTypeName)
    {
        if ($enrichmentTypeName === null || $enrichmentTypeName === '') {
            return null;
        }

        // TODO better way? - allow registering namespaces/types like in Zend for form elements?
        $enrichmentTypeName = 'Opus\\Enrichment\\' . $enrichmentTypeName;
        try {
            if (class_exists($enrichmentTypeName, false)) {
                return new $enrichmentTypeName();
            }
            $this->getLogger()->err('could not find class ' . $enrichmentTypeName);
        } catch (Throwable $ex) { // TODO Throwable only available in PHP 7+
            $this->getLogger()->err('could not instantiate class ' . $enrichmentTypeName . ': ' . $ex->getMessage());
        }

        return null;
    }

    /**
     * @return self
     */
    public function populate(array $values)
    {
        if (array_key_exists(parent::ELEMENT_MODEL_ID, $values)) {
            $enrichmentKey = EnrichmentKey::fetchByName($values[parent::ELEMENT_MODEL_ID]);
            if ($enrichmentKey !== null) {
                $enrichmentType = $enrichmentKey->getEnrichmentType();
                if ($enrichmentType !== null) {
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
