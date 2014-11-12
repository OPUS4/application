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
 * @package     Module_Export
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2014, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Class Export_Model_ExportPluginAbstract
 * TODO in the long run should not be a model and cannot extend Application_Model_Abstract
 * TODO configuration should be limited to plugin (and not the global object)
 */
abstract class Export_Model_ExportPluginAbstract extends Application_Model_Abstract
    implements Export_Model_ExportPlugin {

    /**
     * @var Name of plugin.
     * TODO Im Augenblick nur von PublistExport verwendet, um im XSLT zwischen Instanzen unterscheiden zu können.
     */
    private $name;

    /**
     * @var Zend_Config $config Plugin configuration.
     */
    private $config;

    /**
     * @var Zend_Controller_Request_Http Current request.
     */
    private $request;

    /**
     * @var Zend_Controller_Response_Http Response object.
     */
    private $response;

    /**
     * @var Zend_View View object for rendering response.
     */
    private $view;

    /**
     * Returns name of plugin instance.
     * @return Name
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Sets name of plugin instance.
     * @param $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * Sets the plugin configuration.
     * @param Zend_Config $config
     */
    public function setConfig(Zend_Config $config) {
        $this->config = $config;
    }

    /**
     * Returns the plugin configuration.
     * @return mixed|Zend_Config
     * @throws Zend_Exception
     * TODO access to global config just for convenience; should be removed
     */
    public function getConfig() {
        if (is_null($this->config)) {
            $this->config = Zend_Registry::get('Zend_Config');
        }
        return $this->config;
    }

    /**
     * Returns request object.
     * @return Zend_Controller_Request_Http
     */
    public function getRequest() {
        return $this->request;
    }

    /**
     * Sets request object.
     * @param Zend_Controller_Request_Http $request
     */
    public function setRequest(Zend_Controller_Request_Http $request) {
        $this->request = $request;
    }

    /**
     * Returns response object.
     * @return Zend_Controller_Response_Http
     */
    public function getResponse() {
        return $this->response;
    }

    /**
     * Sets response object.
     * @param Zend_Controller_Response_Http $response
     */
    public function setResponse(Zend_Controller_Response_Http $response) {
        $this->response = $response;
    }

    /**
     * Returns view object.
     * @return Zend_View
     */
    public function getView() {
        return $this->view;
    }

    /**
     * Sets view object.
     * @param Zend_View $view
     */
    public function setView(Zend_View $view) {
        $this->view = $view;
    }

    /**
     * Main function performing export.
     *
     * Needs to be implemented by child classes.
     */
    abstract public function execute();

}