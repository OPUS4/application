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
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Translate\UnknownTranslationKeyException;

/**
 * Form for adding or editing translations.
 *
 * This form is independent of the classes used to integrate translation editing into other forms. This currently makes
 * sense, because the requirements are different.
 * - Application_Form_Element_Translation
 * - Application_Form_Translations
 *
 * TODO support validation for adding new keys (don't exist yet)
 * TODO support changing keys when editing for added keys
 * TODO do not allow modification of module and key when editing values
 *
 * TODO review separation and refactor redundant code
 * TODO use this form to test out new HTML structure and styling (responsive)
 * TODO at least one language should have a translation (make EN und DE required) - Special treatment?
 */
class Setup_Form_Translation extends Application_Form_Abstract
{
    public const ELEMENT_ID = 'Id';

    /**
     * @var string Name of translation key.
     */
    public const ELEMENT_KEY = 'Key';

    /**
     * @var string Name of module for translation key.
     */
    public const ELEMENT_MODULE = 'KeyModule';

    public const SUBFORM_TRANSLATION = 'Translation';

    public const ELEMENT_SAVE = 'Save';

    public const ELEMENT_CANCEL = 'Cancel';

    public const RESULT_SAVE = 'Save';

    public const RESULT_CANCEL = 'Cancel';

    public function init()
    {
        parent::init();

        $this->setElementDecorators([
            'ViewHelper',
            'Errors',
            [['InputWrapper' => 'HtmlTag'], ['class' => 'col-input']],
            ['Label', ['tag' => 'div', 'tagClass' => 'col-label', 'placement' => 'prepend']],
            [['Wrapper' => 'HtmlTag'], ['class' => 'row']],
        ]);

        $this->addElement('hidden', self::ELEMENT_ID);

        $this->addElement('text', self::ELEMENT_KEY, [
            'label'     => 'setup_language_key',
            'size'      => 80,
            'maxlength' => 100,
            'required'  => true,
        ]);

        $lengthValidator = new Zend_Validate_StringLength(['max' => 100]);
        $lengthValidator->setMessage('setup_translation_error_key_too_long', $lengthValidator::TOO_LONG);
        // TODO test customized message

        $this->getElement(self::ELEMENT_KEY)->addValidator(
            new Setup_Form_Validate_TranslationKeyAvailable()
        )->addValidator(
            new Setup_Form_Validate_TranslationKeyFormat()
        )->addValidator(
            $lengthValidator
        );

        // TODO maybe "virtual modules"
        // TODO automatically use module name as prefix (?)
        $this->addElement('Modules', self::ELEMENT_MODULE, [
            'label'    => 'setup_language_module',
            'required' => true,
        ]);

        // TODO add input element for every supported language (separate function)
        $values = new Setup_Form_TranslationValues(self::SUBFORM_TRANSLATION);
        $this->addSubForm($values, self::SUBFORM_TRANSLATION);

        $this->addElement('submit', self::ELEMENT_SAVE, [
            'decorators' => [
                'ViewHelper',
            ],
        ]);
        $this->addElement('submit', self::ELEMENT_CANCEL, [
            'decorators' => [
                'ViewHelper',
            ],
        ]);

        $this->addDisplayGroup(
            [self::ELEMENT_SAVE, self::ELEMENT_CANCEL],
            'actions',
            [
                'order'      => 1000,
                'decorators' => [
                    'FormElements',
                    [['divWrapper' => 'HtmlTag'], ['id' => 'form-action']],
                ],
            ]
        );

        $this->setDecorators([
            'FormElements',
            'Form',
        ]);
    }

    /**
     * Processes POST requests for this form.
     *
     * @param array $post POST data for this form
     * @param array $context POST data for entire request
     * @return string|null Result of processing
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
     * Stores the translation.
     *
     * TODO form class is also model class (refactor! But not just to follow "rules". Why?)
     * TODO do not use DAO class directly (refactor!)
     * TODO throw some exceptions
     * TODO review responsibilities of Translate and TranslationManager
     */
    public function updateTranslation()
    {
        $keyId        = $this->getElement(self::ELEMENT_ID)->getValue();
        $key          = $this->getElement(self::ELEMENT_KEY)->getValue();
        $module       = $this->getElement(self::ELEMENT_MODULE)->getValue();
        $translations = $this->getSubForm(self::SUBFORM_TRANSLATION)->getTranslations();

        $manager   = new Application_Translate_TranslationManager();
        $translate = $this->getTranslationManager();

        if ($keyId === null || strlen(trim($keyId)) === 0) {
            // create new key
            $translate->setTranslations($key, $translations, $module);
        } else {
            // update key
            $old = $manager->getTranslation($keyId);

            if ($module === null) {
                $module = $old['module'];
            }

            if ($keyId !== $key || $old['module'] !== $module) {
                // change name of key
                $manager->updateTranslation($key, $translations, $module, $keyId);
            } elseif ($translations !== $old['translations']) {
                if (isset($old['translationsTmx']) && $translations === $old['translationsTmx']) {
                    // New values match TMX file (reset instead of updating)
                    $manager->reset($key);
                } else {
                    $translate->setTranslations($key, $translations, $module);
                }
            }
        }
    }

    /**
     * Populates form for a translation key.
     *
     * @param string $key
     *
     * TODO throw some exceptions :-)
     */
    public function populateFromKey($key)
    {
        $manager = new Application_Translate_TranslationManager();

        // TODO get all keys (in case of multiple entries in database and deal with it)
        $translation = $manager->getTranslation($key);

        if ($translation === null) {
            throw new UnknownTranslationKeyException("Unknown key '$key'.");
        }

        $idElement = $this->getElement(self::ELEMENT_ID);
        $idElement->setValue($key);
        $idElement->setRequired(true);

        $keyElement = $this->getElement(self::ELEMENT_KEY);
        $keyElement->setValue($key);

        $module        = $translation['module'];
        $moduleElement = $this->getElement(self::ELEMENT_MODULE);
        $moduleElement->setValue($module);

        // disable editing of key and module for keys defined in TMX files
        if (! isset($translation['state']) || $translation['state'] !== 'added') {
            $this->disableKeyEditing();
        }

        $this->getSubForm(self::SUBFORM_TRANSLATION)->setTranslations($translation['translations']);
    }

    public function disableKeyEditing()
    {
        $keyElement = $this->getElement(self::ELEMENT_KEY);
        $keyElement->setAttrib('disabled', true);
        $keyElement->removeValidator('Setup_Form_Validate_TranslationKeyFormat');
        $moduleElement = $this->getElement(self::ELEMENT_MODULE);
        $moduleElement->setAttrib('disabled', true);
    }

    /**
     * @return Application_Translate|null
     * @throws Zend_Exception
     */
    protected function getTranslationManager()
    {
        return Application_Translate::getInstance();
    }
}
