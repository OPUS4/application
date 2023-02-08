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

class Application_Form_Element_SupportedLanguages extends Application_Form_Element_MultiCheckbox
{
    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this->addPrefixPath('Application_Form_Decorator', 'Application/Form/Decorator', Zend_Form::DECORATOR);

        $this->setMultiOptions($this->getLanguageOptions());

        $this->setRequired(true);
        $this->setAutoInsertNotEmptyValidator(false);

        // custom error message
        $this->setRegisterInArrayValidator(false);
        $options = $this->getMultiOptions();
        $this->addValidator(
            'InArray',
            true,
            [
                'messages' => [
                    Zend_Validate_InArray::NOT_IN_ARRAY => 'validation_error_language_not_supported',
                ],
                'haystack' => array_keys($options),
            ]
        );

        $this->setAllowEmpty(false); // there must be a value
    }

    public function loadDefaultDecorators()
    {
        if (! $this->loadDefaultDecoratorsIsDisabled() && count($this->getDecorators()) === 0) {
            $this->setDecorators(
                [
                    'ViewHelper',
                    'Description',
                    'Errors',
                    'ElementHtmlTag',
                    [
                        'LabelNotEmpty',
                        [
                            'tag'        => 'div',
                            'tagClass'   => 'label',
                            'placement'  => 'prepend',
                            'disableFor' => true,
                        ],
                    ],
                    [['dataWrapper' => 'HtmlTagWithId'], ['tag' => 'div', 'class' => 'data-wrapper']],
                ]
            );
        }
    }

    /**
     * Returns available language options determined by translation resources.
     *
     * @return array
     * @throws Zend_Exception
     */
    public function getLanguageOptions()
    {
        $translator = Application_Translate::getInstance();

        $currentLocale = new Zend_Locale($translator->getLocale());

        $translations = $translator->getList();

        $options = [];

        foreach ($translations as $language) {
            $options[$language] = $currentLocale->getTranslation($language, 'language', $currentLocale->getLanguage());
        }

        return $options;
    }

    /**
     * Sets value from comma separated values or array.
     *
     * @param mixed $value
     */
    public function setValue($value)
    {
        if (! is_array($value)) {
            $values = array_map('trim', explode(',', $value));
        } else {
            $values = $value;
        }
        parent::setValue($values);
    }
}
