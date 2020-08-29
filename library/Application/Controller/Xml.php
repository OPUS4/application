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
 * @category    Application
 * @package     Controller
 * @author      Felix Ostrowski <ostrowski@hbz-nrw.de>
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Controller for Opus Applications.
 *
 * @category    Application
 * @package     Controller
 */
class Application_Controller_Xml extends Application_Controller_ModuleAccess
{

    /**
     * Holds xml representation of document information to be processed.
     *
     * @var DomDocument  Defaults to null.
     */
    protected $_xml = null;

    /**
     * Holds the stylesheet for the transformation.
     *
     * @var DomDocument  Defaults to null.
     */
    protected $_xslt = null;

    /**
     * Holds the xslt processor.
     *
     * @var XSLTProcessor  Defaults to null.
     */
    protected $_proc = null;

    /**
     * Do some initialization on startup of every action
     *
     * @return void
     */
    public function init()
    {
        // Controller outputs plain Xml, so rendering and layout are disabled.
        $this->disableViewRendering();

        // Initialize member variables.
        $this->_xml = new DomDocument;
        $this->_proc = new XSLTProcessor;
    }

    /**
     * Deliver the (transformed) Xml content
     *
     * @return void
     */
    public function postDispatch()
    {
        if (! isset($this->view->errorMessage)) {
            // Send Xml response.
            $this->getResponse()->setHeader('Content-Type', 'text/xml; charset=UTF-8', true);
            if (false === is_null($this->_xslt)) {
                $this->getResponse()->setBody($this->_proc->transformToXML($this->_xml));
            } else {
                $this->getResponse()->setBody($this->_xml->saveXml());
            }
        }
        parent::postDispatch();
    }

    /**
     * Load an xslt stylesheet.
     *
     * @return void
     */
    protected function loadStyleSheet($stylesheet)
    {
        $this->_xslt = new DomDocument;
        $this->_xslt->load($stylesheet);
        $this->_proc->importStyleSheet($this->_xslt);
        if (isset($_SERVER['HTTP_HOST'])) {
            $this->_proc->setParameter('', 'host', $_SERVER['HTTP_HOST']);
        }
        $this->_proc->setParameter('', 'server', $this->getRequest()->getBaseUrl());
    }

    /**
     * Method called when access to module has been denied.
     */
    public function moduleAccessDeniedAction()
    {
        $response = $this->getResponse();
        $response->setHttpResponseCode(401);

        // setup error document.
        $element = $this->_xml->createElement('error', 'Unauthorized: Access to module not allowed.');
        $this->_xml->appendChild($element);
    }
}
