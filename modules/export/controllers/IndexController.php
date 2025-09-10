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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Controller for export function.
 *
 * The export actions are separate classes implementing the interface Application_Export_ExportPlugin and are
 * dynamically mapped to controller functions.
 */
class Export_IndexController extends Application_Controller_ModuleAccess
{
    /**
     * Manages export plugins.
     *
     * @var Application_Export_ExportService
     */
    private $exportService;

    /**
     * Do some initialization on startup of every action
     *
     * @throws Zend_Exception
     */
    public function init()
    {
        parent::init();

        // Controller outputs plain Xml, so rendering and layout are disabled.
        $this->disableViewRendering(); // TODO there could be plugins requiring rendering

        $this->exportService = Zend_Registry::get('Opus_ExportService');
        $this->exportService->loadPlugins();
    }

    /**
     * Returns small XML error message if access to module has been denied.
     */
    public function moduleAccessDeniedAction()
    {
        $response = $this->getResponse();
        $response->setHttpResponseCode(401);

        $doc = new DOMDocument();
        $doc->appendChild($doc->createElement('error', 'Unauthorized: Access to module not allowed.'));
        $this->getResponse()->setBody($doc->saveXml());
    }

    /**
     * Maps action calls to export plugins or returns an error message.
     *
     * @param string $action The name of the action that was called.
     * @param array  $parameters The parameters passed to the action.
     * @throws Zend_Controller_Action_Exception
     * @throws Application_Exception Wenn keine zugehöriges Plugin-Klasse gefunden werden kann.
     */
    public function __call($action, $parameters)
    {
        // TODO what does this code do
        if (! 'Action' === substr($action, -6)) {
            $this->getLogger()->info(__METHOD__ . ' undefined method: ' . $action);
            parent::__call($action, $parameters);
        }

        $actionName = $this->getRequest()->getActionName();

        $this->getLogger()->debug("Request to export plugin $actionName");

        $plugin = $this->exportService->getPlugin($actionName);

        if ($plugin !== null) {
            if ($plugin->isAccessRestricted()) {
                $this->moduleAccessDeniedAction();
                return;
            }

            $plugin->setRequest($this->getRequest());
            $plugin->setResponse($this->getResponse());
            $plugin->setView($this->view);

            $plugin->init();
            $result = $plugin->execute();
            if ($result !== 0) {
                // nur im Fehlerfall wird eine HTML-Statusseite an den Client zurückgegeben
                $this->_helper->layout->enableLayout();
                $this->_helper->viewRenderer->setNoRender(false);
            }

            $plugin->postDispatch();
        } else {
            throw new Application_Exception('Plugin ' . htmlspecialchars($actionName) . ' not found');
        }
    }
}
