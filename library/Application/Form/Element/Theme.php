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
 * @package     Form_Element
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2014, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Application_Form_Element_Theme extends Application_Form_Element_SelectWithNull
{

    public function init()
    {
        parent::init();

        $values = $this->getThemes();

        $this->addMultiOption('Null', '-');

        foreach ($values as $value) {
            $this->addMultiOption($value, $value);
        }
    }

    public function setValue($value)
    {
        if (! array_key_exists($value, $this->getMultiOptions())) {
            parent::setValue('Null');
        } else {
            parent::setValue($value);
        }
    }

    /**
     * Path to location of available themes.
     *
     * @var string
     */
    private static $_themesPath = '';

    /**
     * Available themes from directory self::$_themesPath.
     *
     * @var array
     */
    private static $_themes = null;

    private function getThemes()
    {
        if (is_null(self::$_themes)) {
            $this->findThemes(APPLICATION_PATH . '/public/layouts'); // TODO configurable
        }

        return self::$_themes;
    }

    /**
     * Set location of available themes.
     *
     * @param  string $path
     */
    public static function findThemes($path)
    {
        if (is_dir($path) === false) {
            throw new InvalidArgumentException("Argument should be a valid path.");
        }

        $themes = [];
        foreach (glob($path . '/*') as $entry) {
            if (true === is_dir($entry)) {
                $theme = basename($entry);
                $themes[$theme] = $theme;
            }
        }

        self::$_themesPath = $path;
        self::$_themes = $themes;
    }
}
