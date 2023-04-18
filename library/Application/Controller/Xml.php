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
 * Controller for Opus Applications.
 */
class Application_Controller_Xml extends Application_Controller_ModuleAccess
{
    /**
     * Holds xml representation of document information to be processed.
     *
     * @var DOMDocument Defaults to null.
     */
    protected $xml;

    /**
     * Holds the stylesheet for the transformation.
     *
     * @var DOMDocument Defaults to null.
     */
    protected $xslt;

    /**
     * Holds the xslt processor.
     *
     * @var XSLTProcessor Defaults to null.
     */
    protected $proc;

    /**
     * Do some initialization on startup of every action
     */
    public function init()
    {
        // Controller outputs plain Xml, so rendering and layout are disabled.
        $this->disableViewRendering();

        // Initialize member variables.
        $this->xml  = new DOMDocument();
        $this->proc = new XSLTProcessor();
    }

    /**
     * Deliver the (transformed) Xml content
     */
    public function postDispatch()
    {
        if (! isset($this->view->errorMessage)) {
            // Send Xml response.
            $this->getResponse()->setHeader('Content-Type', 'text/xml; charset=UTF-8', true);
            if ($this->xslt !== null) {
                $this->getResponse()->setBody($this->proc->transformToXML($this->xml));
            } else {
                $this->getResponse()->setBody($this->xml->saveXml());
            }
        }
        parent::postDispatch();
    }

    /**
     * Load an xslt stylesheet.
     *
     * @param string $stylesheet
     */
    protected function loadStyleSheet($stylesheet)
    {
        $this->xslt = new DOMDocument();
        $this->xslt->load($stylesheet);
        $this->proc->importStyleSheet($this->xslt);
        if (isset($_SERVER['HTTP_HOST'])) {
            $this->proc->setParameter('', 'host', $_SERVER['HTTP_HOST']);
        }
        $this->proc->setParameter('', 'server', $this->getRequest()->getBaseUrl());
    }

    /**
     * Method called when access to module has been denied.
     */
    public function moduleAccessDeniedAction()
    {
        $response = $this->getResponse();
        $response->setHttpResponseCode(401);

        // setup error document.
        $element = $this->xml->createElement('error', 'Unauthorized: Access to module not allowed.');
        $this->xml->appendChild($element);
    }
}
