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
 *
 * @category    Application
 * @package     Application
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Provides access to view helper using static functions in XSLT.
 */
class Application_Xslt
{

    /**
     * @var Application_Xslt
     */
    private static $_singleton = null;

    /**
     * Prevent external construction of this class.
     */
    private function __construct() {
    }

    /**
     * Returns singleton instance of this class.
     * @return Application_Xslt
     */
    public static function getInstance()
    {
        if (is_null(self::$_singleton))
        {
            self::$_singleton = new Application_Xslt();
        }

        return self::$_singleton;
    }

    /**
     * Catches calls to static functions and redirects them to view helpers.
     * @param $method Name of function
     * @param $arguments Arguments of function
     * @return mixed Result of the function
     */
    public static function __callStatic($method, $arguments)
    {
        $helper = self::getInstance()->findHelper($method);

        if (!is_null($helper)) {
            return call_user_func_array(array($helper, $method), $arguments);
        }

        return parent::__call($method, $arguments);
    }

    /**
     * Finds helpers in the set of available view helpers.
     * @param $method Name of helper
     * @return mixed Zend_View_Helper
     * @throws Zend_Exception
     */
    public function findHelper($method) {
        $helper = Zend_Registry::get('Opus_View')->getHelper($method);

        return $helper;
    }

    /**
     * Returns the names of the functions that are supported.
     * @param $processor XSLTProcessor
     * @return array Names of supported functions
     */
    public static function registerViewHelper($processor, $allowedFunctions)
    {
        $functions = array_map(
            function($value)
            {
                return 'Application_Xslt::' . $value;
            },
            $allowedFunctions
        );

        $processor->registerPHPFunctions($functions);
    }

}