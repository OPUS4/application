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

use Opus\Common\Config;

/**
 * Formular für Bestätigungsabfragen an den Nutzer, z.B. beim Löschen von Modellen.
 */
class Setup_Form_AppearanceForm extends Application_Form_Abstract
{
    /** @var string Name of element for selecting theme. */
    public const ELEMENT_THEME = 'Theme';

    /** @var string Name of apply/save button. */
    public const ELEMENT_APPLY = 'Apply';

    /**
     * Initialisiert die Formularelement.
     */
    public function init()
    {
        parent::init();

        $this->addElement('themeSelect', self::ELEMENT_THEME);
        $this->addElement('submit', self::ELEMENT_APPLY, ['label' => 'button_apply']);

        $this->setDecorators(
            [
                'FormElements',
                'Form',
            ]
        );
    }

    /**
     * Sets the form element values based on post or configuration.
     */
    public function populate(array $data)
    {
        $config = Config::get();

        // TODO encapsulate in separate class (perhaps ThemeManager)
        //      similar code already exists in the ThemeSelect form element
        $selectedTheme = 'default';

        if (isset($data[self::ELEMENT_THEME])) {
            $selectedTheme = $data[self::ELEMENT_THEME];
        } else if (isset($config->themes->selectedTheme)) {
            $selectedTheme = $config->themes->selectedTheme;
        }

        $this->getElement(self::ELEMENT_THEME)->setValue($selectedTheme);
    }

    /**
     * Verarbeitet POST und stellt fest welcher Button geklickt wurde.
     *
     * @param array $post POST data
     * @return string|null
     */
    public function processPost($post)
    {
        if (array_key_exists(self::ELEMENT_APPLY, $post)) {
            $selectedTheme = $this->getElementValue(self::ELEMENT_THEME);
            $config        = ['themes' => ['selectedTheme' => $selectedTheme]];
            Application_Configuration::save(new Zend_Config($config));
        }

        return null;
    }
}
