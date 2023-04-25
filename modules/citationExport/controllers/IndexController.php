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

class CitationExport_IndexController extends Application_Controller_Action
{
    /**
     * Helper for handling citation export requests.
     *
     * @var CitationExport_Model_Helper
     */
    private $exportHelper;

    /**
     * Initializes common controller variables.
     */
    public function init()
    {
        parent::init();

        $this->exportHelper = new CitationExport_Model_Helper(
            $this->view->fullUrl(),
            $this->view->getScriptPath('index')
        );
        $this->view->title  = $this->view->translate('citationExport_modulename');
    }

    /**
     * Output data to index view.
     */
    public function indexAction()
    {
        $this->handleRequest();
        $this->view->downloadUrl = $this->view->url(['action' => 'download'], false, null);
    }

    /**
     * Output data as downloadable file.
     */
    public function downloadAction()
    {
        $request = $this->getRequest();

        if (! $this->handleRequest()) {
            return;
        }

        $this->disableViewRendering();

        // Send plain text response.
        $response = $this->getResponse();

        $response->setHeader('Content-Type', 'text/plain; charset=UTF-8', true);

        $outputFormat = $request->getParam('output');

        $extension = $this->exportHelper->getExtension($outputFormat);

        $config = $this->getConfig();

        $download = true;

        if (isset($config->export->download)) {
            $value    = $config->export->download;
            $download = $value !== '0' && $value !== false && $value !== '';
        }

        if ($download) {
            $response->setHeader(
                'Content-Disposition',
                'attachment; filename=' . $outputFormat . '-' . $request->getParam('docId') . '.' . $extension,
                true
            );
        }

        $response->setBody($this->view->output);
    }

    /**
     * @return int
     * @throws Application_Exception
     * @throws Zend_Controller_Response_Exception
     */
    public function handleRequest()
    {
        try {
            $this->view->output = $this->exportHelper->getOutput($this->getRequest());
        } catch (CitationExport_Model_Exception $ceme) {
            $this->view->output = $this->view->translate($ceme->getMessage());
            $this->getResponse()->setHttpResponseCode(400);
            return 0;
        }
        return 1;
    }
}
