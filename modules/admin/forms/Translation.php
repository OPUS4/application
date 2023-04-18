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
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Subform for editing translations for a single translation key.
 *
 * This form is used for the editing of the translation of enrichment keys.
 *
 * The form shows the default translations, if available, and provides input boxes for all supported languages.
 *
 * TODO display existing values (probably outside this class)
 * TODO display default values
 * TODO description for value
 * TODO option for textarea instead of input (i.e. field hints and other longer texts)
 * TODO less distance between label and input - bigger input
 */
class Admin_Form_Translation extends Application_Form_Abstract
{
    public function init()
    {
        parent::init();

        $configHelper = Application_Configuration::getInstance();

        $languages = $configHelper->getSupportedLanguages();
        $translate = $configHelper->getTranslate();

        /* TODO REMOVE
        $text = new Zend_Form_Element_Note('description');
        $text->setValue('Hello, world! This is a slightly longer description in order to test how it is displayed on the page.');
        $this->addElement($text);
        */

        foreach ($languages as $language) {
            $this->addElement('text', $language, [
                'label' => $translate->translateLanguage($language),
                'size'  => 60,
            ]);
        }
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->setLegend($key);
    }
}
