<?php
/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the North Rhine-Westphalian Library Service Center,
 * the Cooperative Library Network Berlin-Brandenburg, the Saarland University
 * and State Library, the Saxon State Library - Dresden State and University
 * Library, the Bielefeld University Library and the University Library of
 * Hamburg University of Technology with funding from the German Research
 * Foundation and the European Regional Development Fund.
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
 * @category   Application
 * @package    View
 * @author     Henning Gerhardt (henning.gerhardt@slub-dresden.de)
 * @copyright  Copyright (c) 2009, OPUS 4 development team
 * @license    http://www.gnu.org/licenses/gpl.html General Public License
 * @version    $Id$
 */

/**
 * View helper for displaying a model
 *
 * @category    Application
 * @package     View
 *
 * FIXME reuse helpers
 * FIXME check file_exists only once
 */
class View_Helper_ShowModel extends Zend_View_Helper_Abstract {

    const HELPER_PATH = '/library/View/Helper/ShowModel/';

    const HELPER_PREFIX = 'View_Helper_ShowModel_';

    private $config;

    private $fieldHelperMap;

    /**
     * Supress all empty fields
     *
     * @var boolean
     */
    private $__saef = false;

    /**
     * Supress personal informations
     *
     * @var boolean
     */
    private $__spi = false;


    public function __construct() {
        $this->config = Zend_Registry::get('Zend_Config');
        if (isset($this->config->view->helper->showmodel->field)) {
            $this->fieldHelperMap = $this->config->view->helper->showmodel->field;
        }
        else {
            $this->fieldHelperMap = array();
        }
    }

    /**
     * View helper for displaying a model
     *
     * @param array   &$modeldata Contains model data
     * @param boolean $saef       (Optional) Supress all empty fields.
     * @param boolean $spi        (Optional) Supress personal informations.
     * @return string
     */
    public function showModel(array $modeldata, $saef = false, $spi = false) {
        if (is_bool($saef) === true) {
            $this->__saef = $saef;
        }
        if (is_bool($spi) === true) {
            $this->__spi = $spi;
        }
        $result = '';
        foreach ($modeldata as $field => $value) {
            if (true === empty($value) 
                    || (count($value) === 1
                    && is_array($value) === true
                    && is_string($value[0]) === true
                    && strlen($value[0]) === 0)) {
                continue;
            }

            $helper = $this->getHelper($field);
            $result .= $helper->display($field, $value);
        }
        return $result;
    }

    /**
     * Returns a helper instance for field name.
     * @param clazz $field
     * @return clazzName
     */
    protected function getHelper($field) {
        $clazzName = self::HELPER_PREFIX;

        $helperName = $this->getHelperName($field);

        if (!empty($helperName) && $this->isHelperExists($helperName)) {
            $clazzName .= $helperName;
        }
        else {
            $clazzName .= 'General';
        }

        // FIXME suppress PHP warning if class not found
        if (!class_exists($clazzName)) {
            $clazzName = self::HELPER_PREFIX . 'General';
        }

        Zend_Registry::get('Zend_Log')->debug('ShowModel ' . $field . ' => ' . $clazzName);

        $helper = new $clazzName($this->view, $this->__saef, $this->__spi);

        return $helper;
    }

    /**
     * Returns name of helper for field name.
     *
     * @param string $field
     * @return string
     */
    protected function getHelperName($field) {
        if (isset($this->fieldHelperMap->$field)) {
            return $this->fieldHelperMap->$field;
        }
        else if (stripos($field, 'date') !== false) {
            return 'Date';
        }
        else if (stripos($field, 'person') !== false) {
            return 'Person';
        }
        else if (stripos($field, 'subject') !== false) {
            return 'Subject';
        }
        else if (stripos($field, 'title') !== false) {
            return 'Title';
        }
        else {
            return $field;
        }
    }

    /**
     * Checks if PHP file for a helper name exists.
     *
     * This method is currently necessary because *class_exists* will produce
     * a PHP warning if a class does not exist. Depending on the PHP settings
     * this warning will show up in the user interface. In production is does
     * not show because warnings are suppressed, but in the other modes like
     * *development* and *testing*.
     *
     * So far no better solution has presented itself.
     *
     * @param <type> $name
     * @return <type>
     */
    protected function isHelperExists($name) {
        $path = APPLICATION_PATH . self::HELPER_PATH . $name . '.php';

        return file_exists($path) ? true : false;
    }

}