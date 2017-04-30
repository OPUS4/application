<?php
/*
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
 */

/**
 * Parses values of variables from shell scripts.
 *
 * @category    Application
 * @package     Application_Util
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2016, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */
class Application_Util_ShellScript
{

    /**
     * @var array Key -> Value pairs found in shell script
     */
    private $_properties;

    /**
     * Application_Util_ShellScript constructor.
     * @param $path
     */
    public function __construct($path)
    {
        $this->_properties = self::getPropertiesFromScript($path);
    }

    /**
     * Returns value for property if found in shell script.
     * @param $name Property name
     * @return null|string
     */
    public function getProperty($name)
    {
        if (array_key_exists($name, $this->_properties)) {
            return $this->_properties[$name];
        }
        else {
            return null;
        }
    }

    /**
     * Read property values from shell script.
     *
     * @param $path Path to shell script
     * @return array Map with names and values of properties
     * @throws Exception
     */
    public static function getPropertiesFromScript($path)
    {
        $properties = array();

        if (!is_readable($path))
        {
            throw new Exception('cannot read file');
        }

        $file = fopen($path, 'r');

        if ($file)
        {
            while (($line = fgets($file)) !== false)
            {
                $line = trim($line);

                // ignore comments
                if (preg_match('/^#/', $line))
                {
                    continue;
                }
                else {
                    if (preg_match('/[A-Za-z0-9]+=.+/', $line))
                    {
                        $property = preg_split('/\s*=\s*/', $line, 2);
                        if (count($property) === 2)
                        {
                            $properties[$property[0]] = trim($property[1], '"\'');
                        }
                    }
                }
            }

            fclose($file);
        }
        else {
            throw new Exception('could not open file');
        }

        return $properties;
    }

}
