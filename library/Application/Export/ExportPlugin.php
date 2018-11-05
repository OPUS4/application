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
 * Interface for export plugins.
 *
 * The plugins are dynamically registered as actions in the export controller.
 *
 * TODO The export mechanism should/could be separated from the request/response handling.
 */
interface Application_Export_ExportPlugin {

    /**
     * Returns name of plugin.
     * @return mixed
     */
    public function getName();

    /**
     * Sets the plugin configuration.
     * @param Zend_Config $config
     */
    public function setConfig(Zend_Config $config = null);

    /**
     * Sets the HTTP request being processed.
     * @param Zend_Controller_Request_Http $request
     */
    public function setRequest(Zend_Controller_Request_Http $request);

    /**
     * Sets the HTTP response.
     * @param Zend_Controller_Response_Http $response
     */
    public function setResponse(Zend_Controller_Response_Http $response);

    /**
     * Sets the view objekt for rendering the response.
     * @param Zend_View $view
     */
    public function setView(Zend_View $view);

    /**
     * Main function performing export.
     */
    public function execute();

}

