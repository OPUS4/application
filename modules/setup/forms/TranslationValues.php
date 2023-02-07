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

/**
 * SubForm used for editing translations for multiple languages.
 */
class Setup_Form_TranslationValues extends Zend_Form_SubForm
{
    public function init()
    {
        parent::init();

        $this->setElementDecorators([
            'ViewHelper',
            [['InputWrapper' => 'HtmlTag'], ['class' => 'col-input']],
            ['Label', ['tag' => 'div', 'tagClass' => 'col-label', 'placement' => 'prepend']],
            [['Wrapper' => 'HtmlTag'], ['class' => 'row']],
        ]);

        $translator = Application_Translate::getInstance();

        $languages = $this->getLanguages();

        foreach ($languages as $language) {
            $this->addElement('textarea', $language, [
                'label' => $translator->translateLanguage($language),
                'rows'  => 10,
            ]);
        }

        $this->setDecorators([
            'FormElements',
        ]);
    }

    /**
     * @return array
     */
    public function getTranslations()
    {
        $elements = $this->getElements();

        $translations = [];

        foreach ($elements as $name => $element) {
            $value               = $element->getValue();
            $translations[$name] = trim($value ?? '');
        }

        return $translations;
    }

    /**
     * @param array $translations
     */
    public function setTranslations($translations)
    {
        foreach ($translations as $lang => $value) {
            $element = $this->getElement($lang);
            if ($element !== null) {
                $element->setValue($value);
            }
            // TODO ELSE deal with missing language
        }
    }

    /**
     * @return array
     */
    protected function getLanguages()
    {
        return Application_Configuration::getInstance()->getSupportedLanguages();
    }
}
