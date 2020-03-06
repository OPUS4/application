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

class Setup_Form_Translation extends Application_Form_Translations
{

    const ELEMENT_KEY = 'Key';

    public function init()
    {
        parent::init();

        $this->addElement('text', self::ELEMENT_KEY, [
            'label' => 'Key', 'size' => 80, 'disabled' => true
        ]);
    }

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

    public function addTranslationElement($textarea = true, $customOptions = null)
    {
        $options = ['label' => "setup_translation", 'textarea' => $textarea];

        $width = 90;

        if ($textarea) {
            $options = array_merge($options, ['cols' => $width, "rows" => 12]);
        } else {
            $options = array_merge($options, ['size' => $width]);
        }

        if (! is_null($customOptions)) {
            $options = array_merge($options, $customOptions);
        }

        $element = $this->createElement('translation', 'translations', $options);
        $this->addElement($element);
    }
}