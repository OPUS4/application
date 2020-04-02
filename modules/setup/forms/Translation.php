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
 * @package     Setup_Form
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Form for adding or editing translations.
 *
 * This form is independent of the classes used to integrate translation editing into other forms. This currently makes
 * sense, because the requirments are different.
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

    /**
     * @var string Name of translation key.
     */
    const ELEMENT_KEY = 'Key';

    /**
     * @var string Name of module for translation key.
     */
    const ELEMENT_MODULE = 'KeyModule';

    /**
     *
     */
    const SUBFORM_TRANSLATION = 'Translation';

    const ELEMENT_SAVE = 'Save';

    const ELEMENT_CANCEL = 'Cancel';

    const RESULT_SAVE = 'Save';

    const RESULT_CANCEL = 'Cancel';

    public function init()
    {
        parent::init();

        $this->setElementDecorators([
            'ViewHelper',
            'Errors',
            [['InputWrapper' => 'HtmlTag'], ['class' => 'col-input']],
            ['Label', ['tag' => 'div', 'tagClass' => 'col-label', 'placement' => 'prepend']],
            [['Wrapper' => 'HtmlTag'], ['class' => 'row']]
        ]);

        $this->addElement('text', self::ELEMENT_KEY, [
            'label' => 'Key', 'size' => 80, 'required' => true
        ]);

        $this->getElement(self::ELEMENT_KEY)->addValidator(new Setup_Form_Validate_TranslationKeyAvailable());

        // TODO no 'all' option
        // TODO always all modules
        // TODO maybe "virtual modules"
        // TODO automatically use module name as prefix (?)
        $this->addElement('Modules', self::ELEMENT_MODULE, [
            'label' => 'Module', 'required' => true
        ]);

        // TODO add input element for every supported language (separate function)
        $values = new Setup_Form_TranslationValues(self::SUBFORM_TRANSLATION);
        $this->addSubForm($values, self::SUBFORM_TRANSLATION);

        $this->addElement('submit', self::ELEMENT_SAVE, [
            'decorators' => [
                'ViewHelper'
            ]
        ]);
        $this->addElement('submit', self::ELEMENT_CANCEL, [
            'decorators' => [
                'ViewHelper'
            ]
        ]);

        $this->addDisplayGroup(
            [self::ELEMENT_SAVE, self::ELEMENT_CANCEL],
            'actions',
            ['order' => 1000, 'decorators' => [
                'FormElements',
                [['divWrapper' => 'HtmlTag'], ['id' => 'form-action']]
            ]]
        );


        $this->setDecorators([
            'FormElements',
            'Form'
        ]);
    }

    /**
     * Processes POST requests for this form.
     * @param $post POST data for this form
     * @param $context POST data for entire request
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

    /*
    public function addKey($key, $textarea = true, $options = null)
    {
        if (is_null($options)) {
            $options = [];
        }

        $options['label'] = 'setup_translation_values';

        parent::addKey($key, $textarea, $options);

        $this->getElement(self::ELEMENT_KEY)->setValue($key);
    }

    public function setKeyEditable($enabled)
    {
        $this->getElement(self::ELEMENT_KEY)->setAttrib('disabled', $enabled ? null : true);
    }
    */
    protected function addTranslationElement($language, $textarea = true, $customOptions = null)
    {
        $options = ['label' => $language, 'textarea' => $textarea];

        $width = 90;

        if ($textarea) {
            $options = array_merge($options, ['cols' => $width, "rows" => 12]);
        } else {
            $options = array_merge($options, ['size' => $width]);
        }

        if (! is_null($customOptions)) {
            $options = array_merge($options, $customOptions);
        }

        $element = $this->createElement('text', 'translation', $options);
        $this->addElement($element);
    }
}
