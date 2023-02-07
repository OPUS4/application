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
 * @copyright   Copyright (c) 2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Form for editing translations.
 *
 * This form allows editing translations for one or more key.
 *
 * TODO use translation manager instead of Translate
 */
class Application_Form_Translations extends Application_Form_Abstract
{
    public const ELEMENT_SAVE = 'Save';

    public const ELEMENT_CANCEL = 'Cancel';

    public const RESULT_SAVE = 'save';

    public const RESULT_CANCEL = 'cancel';

    public function init()
    {
        parent::init();

        $this->setDecorators([
            'FormElements',
            'Form',
        ]);

        $this->addElement('submit', self::ELEMENT_SAVE, [
            'decorators' => [
                'ViewHelper',
                [['liWrapper' => 'HtmlTag'], ['tag' => 'li', 'class' => 'save-element']],
            ],
        ]);

        $this->addElement('submit', self::ELEMENT_CANCEL, [
            'decorators' => [
                'ViewHelper',
                [['liWrapper' => 'HtmlTag'], ['tag' => 'li', 'class' => 'cancel-element']],
            ],
        ]);

        $this->addDisplayGroup(
            [self::ELEMENT_SAVE, self::ELEMENT_CANCEL],
            'actions',
            [
                'order'      => 1000,
                'decorators' => [
                    'FormElements',
                    [['ulWrapper' => 'HtmlTag'], ['tag' => 'ul', 'class' => 'form-action']],
                    [['divWrapper' => 'HtmlTag'], ['id' => 'form-action']],
                ],
            ]
        );
    }

    /**
     * @param string     $key
     * @param bool       $textarea
     * @param array|null $customOptions
     * @throws Zend_Form_Exception
     *
     * TODO support label parameter (and other parameters?)
     */
    public function addKey($key, $textarea = false, $customOptions = null)
    {
        $options = ['label' => "setup_$key", 'textarea' => $textarea];

        $width = 90;

        $name = $this->normalizeKey($key);

        if ($textarea) {
            $options = array_merge($options, ['cols' => $width, 'rows' => 12]);
        } else {
            $options = array_merge($options, ['size' => $width]);
        }

        if ($customOptions !== null) {
            $options = array_merge($options, $customOptions);
        }

        $element = $this->createElement('translation', $name, $options);
        $element->setKey($key);
        $this->addElement($element);
    }

    /**
     * Removes dashes from key names.
     *
     * Keys are used as identifier for form elements. However a dash is a special character for Zend when processing
     * the POST data. Therefore the identifiers cannot contain dashes.
     *
     * @param string $key
     * @return string|string[]|null
     *
     * TODO verify and document why it works! Zend normalizes the name anyway - however we now store the real key in the
     *      object, so we don't use the name for accessing translations. This is a bit fragile. Somebody who doesn't
     *      understand the connections might break it.
     * TODO there could be a collision with two translations where one is identical except for dashes between words,
     *      e.g. 'admin-error' and 'adminerror'. These two keys would get the same form element name.
     */
    public function normalizeKey($key)
    {
        return $key; // preg_replace('/-/', '-', $key);
    }

    /**
     * @return array
     */
    public function getTranslationElements()
    {
        $elements = $this->getElements();

        return array_filter($elements, function ($value) {
            return $value instanceof Application_Form_Element_Translation;
        });
    }

    public function populateFromTranslations()
    {
        $elements = $this->getTranslationElements();

        $translate = Application_Translate::getInstance();

        foreach ($elements as $name => $element) {
            // TODO handle no translation
            $key          = $element->getKey();
            $translations = $translate->getTranslations($key);
            $element->setValue($translations);
        }
    }

    public function updateTranslations()
    {
        $elements = $this->getTranslationElements();

        foreach ($elements as $name => $element) {
            $key = $element->getKey();
            $element->updateTranslations($key);
        }

        $translate = Application_Translate::getInstance();
        $translate->clearCache();
        Zend_Translate::clearCache();
    }

    /**
     * Verarbeitet POST.
     *
     * @param array $post POST Daten fuer dieses Formular
     * @param array $context POST Daten fuer gesamten Request
     * @return string Ergebnis der POST Verarbeitung
     */
    public function processPost($post, $context)
    {
        if (array_key_exists(self::ELEMENT_SAVE, $post)) {
            return self::RESULT_SAVE;
        } elseif (array_key_exists(self::ELEMENT_CANCEL, $post)) {
            return self::RESULT_CANCEL;
        }
    }
}
