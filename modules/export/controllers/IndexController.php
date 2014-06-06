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
 * @author      Sascha Szott <szott@zib.de>
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @author      Michael Lang <lang@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2014, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Export_IndexController extends Controller_Xml {

    private $stylesheetDirectory;
    private $stylesheet;
    private $exportModel;

    public function init() {
        parent::init();
        $this->exportModel = new Export_Model_XmlExport();
    }    

    /*
     * called by frontdoor or solr search results. returns the results in xml format
     */
    public function indexAction() {
        $exportParam = $this->getRequest()->getParam('export');
        if (is_null($exportParam)) {
            throw new Application_Exception('export format is not specified');
        }

        // currently only xml is supported here
        if ($exportParam !== 'xml' && $exportParam !== 'xmlFd') {
            throw new Application_Exception('export format is not supported' . $exportParam);
        }

        // parameter stylesheet is mandatory (only administrator is able to see raw output)
        // non-administrative users can only reference user-defined stylesheets
        if (is_null($this->getRequest()->getParam('stylesheet')) && !Opus_Security_Realm::getInstance()->checkModule('admin')) {
            throw new Application_Exception('missing parameter stylesheet');
        }

        $this->stylesheet = $this->getRequest()->getParam('stylesheet');
        $this->stylesheetDirectory = 'stylesheets-custom';

        $this->loadStyleSheet($this->exportModel->buildStylesheetPath($this->stylesheet,
            $this->view->getScriptPath('') . $this->stylesheetDirectory));

        if ($exportParam == 'xml') {
            $this->exportModel->prepareXml($this->_xml, $this->_proc, $this->getRequest());
        }
        else {
            $this->exportModel->prepareXmlForFrontdoor($this->_xml, $this->_proc, $this->getRequest());
        }
    }

    /*
     * exports the publication list
     */
    public function publistAction() {
        $config = Zend_Registry::get('Zend_Config');
        if (isset($config->publist->stylesheetDirectory)) {
                $this->stylesheetDirectory = $config->publist->stylesheetDirectory;
        }

        if (isset($config->publist->stylesheet)) {
                $this->stylesheet = $config->publist->stylesheet;
        }
        if (!is_null($this->getRequest()->getParam('stylesheet'))) {
            $this->stylesheet = $this->getRequest()->getParam('stylesheet');
        }

        $roleParam = $this->getRequest()->getParam('role');
        if (is_null($roleParam)) {
            throw new Application_Exception('role is not specified');
        }

        $numberParam = $this->getRequest()->getParam('number');
        if (is_null($numberParam)) {
            throw new Application_Exception('number is not specified');
        }

        $groupBy = 'publishedYear';
        if (isset($config->publist->groupby->completedyear)) {
            $groupBy = 'completedYear';
        }

        $collection = $this->exportModel->mapQuery($roleParam, $numberParam);
        $this->getRequest()->setParam('searchtype', 'collection');
        $this->getRequest()->setParam('id', $collection->getId());
        $this->getRequest()->setParam('export', 'xml');
        $this->_proc->setParameter('', 'collName', $collection->getName());

        $this->_proc->registerPHPFunctions('max');
        $this->_proc->registerPHPFunctions('urlencode');
        $this->_proc->registerPHPFunctions('Export_Model_PublicationList::getMimeTypeDisplayName');
        $this->_proc->setParameter('', 'fullUrl', $this->view->fullUrl());
        $this->_proc->setParameter('', 'groupBy', $groupBy);

        $this->loadStyleSheet($this->exportModel->buildStylesheetPath($this->stylesheet,
            $this->view->getScriptPath('') . $this->stylesheetDirectory));

        $this->exportModel->prepareXml($this->_xml, $this->_proc, $this->getRequest());
    }
}

