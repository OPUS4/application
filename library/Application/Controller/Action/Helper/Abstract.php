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
 */

/**
 * Basisklasse fuer OPUS 4 Controller Action Helper.
 *
 * @category    Application
 * @package     Application_Controller_Action_Helper
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */
abstract class Application_Controller_Action_Helper_Abstract extends Zend_Controller_Action_Helper_Abstract {

    /**
     * Logger for controller classes.
     * @var Zend_Log
     */
    private $_logger = null;

    private $_view = null;

    private $_config = null;

    /**
     * Returns logger.
     * @return Zend_Log
     */
    public function getLogger() {
        if (is_null($this->_logger)) {
            $this->_logger = Zend_Registry::get('Zend_Log');
        }
        return $this->_logger;
    }

    /**
     * Sets logger for class.
     * @param $logger Zend_Log
     */
    public function setLogger($logger) {
        $this->_logger = $logger;
    }

    /**
     * Returns view if it has been set or tries to retrieve view from action controller.
     *
     * NOTE: This helps with unit tests.
     *
     * @return null|Zend_View_Interface
     */
    public function getView()
    {
        if (is_null($this->_view))
        {
            $controller = $this->getActionController();
            if (!is_null($controller))
            {
                $this->_view = $controller->view;
            }
        }
        return $this->_view;
    }

    /**
     * Allows manually setting the view.
     *
     * NOTE: This helps with unit tests.
     *
     * @param $view
     */
    public function setView($view)
    {
        $this->_view = $view;
    }

    /**
     * Returns application configuration.
     */
    public function getConfig()
    {
        if (is_null($this->_config))
        {
            $this->_config = Application_Configuration::getInstance()->getConfig();
        }

        return $this->_config;
    }

}
