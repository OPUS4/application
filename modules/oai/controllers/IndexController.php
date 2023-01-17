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
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * TODO move all processing into model classes for testing and reuse
 * TODO refactor code for returning multiple errors
 */
class Oai_IndexController extends Application_Controller_ModuleAccess
{
    /**
     * Do some initialization on startup of every action
     */
    public function init()
    {
        // Controller outputs plain Xml, so rendering and layout are disabled.
        $this->disableViewRendering();
    }

    /**
     * Method called when access to module has been denied.
     */
    public function moduleAccessDeniedAction()
    {
        $response = $this->getResponse();
        $response->setHttpResponseCode(401);

        $this->_xml = new DOMDocument();

        // setup error document.
        $element = $this->_xml->createElement('error', 'Unauthorized: Access to module not allowed.');
        $this->_xml->appendChild($element);
    }

    /**
     * Entry point for all OAI-PMH requests.
     */
    public function indexAction()
    {
        $request = $this->getRequest();

        // to handle POST and GET Request, take any given parameter
        $parameters = $request->getParams();

        // remove parameters which are "safe" to remove
        $safeRemoveParameters = ['module', 'controller', 'action', 'role'];

        foreach ($safeRemoveParameters as $name) {
            unset($parameters[$name]);
        }

        $server = new Oai_Model_Server(); // TODO needs factory
        $server->setScriptPath($this->view->getScriptPath('index'));
        $server->setBaseUrl($this->view->fullUrl());
        $server->setBaseUri($request->getBaseUrl());
        $server->setResponse($this->getResponse()); // TODO temporary hack

        $this->getResponse()->setBody($server->handleRequest($parameters, $request->getRequestUri()));
        $this->getResponse()->setHeader('Content-Type', 'text/xml; charset=UTF-8', true);
    }
}
