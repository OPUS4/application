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
 * @copyright   Copyright (c) 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Provides access to view helper using static functions in XSLT.
 */
class Application_Xslt
{
    /** @var self */
    private static $singleton;

    /**
     * Prevent external construction of this class.
     */
    private function __construct()
    {
    }

    /**
     * Returns singleton instance of this class.
     *
     * @return self
     */
    public static function getInstance()
    {
        if (self::$singleton === null) {
            self::$singleton = new Application_Xslt();
        }

        return self::$singleton;
    }

    /**
     * Catches calls to static functions and redirects them to view helpers.
     *
     * @param string $method Name of function
     * @param array  $arguments Arguments of function
     * @return mixed Result of the function
     */
    public static function __callStatic($method, $arguments)
    {
        $helper = self::getInstance()->findHelper($method);

        if ($helper !== null) {
            return call_user_func_array([$helper, $method], $arguments);
        }

        return self::__call($method, $arguments);
    }

    /**
     * Finds helpers in the set of available view helpers.
     *
     * @param string $method Name of helper
     * @return mixed Zend_View_Helper
     * @throws Zend_Exception
     */
    public function findHelper($method)
    {
        return Zend_Registry::get('Opus_View')->getHelper($method);
    }

    /**
     * Returns the names of the functions that are supported.
     *
     * @param XSLTProcessor $processor
     * @param array         $allowedFunctions
     * @return array Names of supported functions TODO necessary?
     */
    public static function registerViewHelper($processor, $allowedFunctions)
    {
        $functions = array_map(
            function ($value) {
                return 'Application_Xslt::' . $value;
            },
            $allowedFunctions
        );

        $processor->registerPHPFunctions($functions);

        return $functions;
    }
}
