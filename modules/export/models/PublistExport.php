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
 * Export plugin for exporting collections based on role name and collection number.
 *
 * This plugin is used to export publication lists for authors managed as collections
 * in a collection role. An XSLT is applied to format the output.
 *
 * TODO stylesheet stuff moves to XsltExport
 * TODO output is not xml, but rather text/html (DIV, HTML snippet)
 */
class Export_Model_PublistExport extends Export_Model_XsltExport {

    /**
     * @throws Application_Exception if parameters are not sufficient
     */
    public function execute() {
        $config = $this->getConfig();
        $request = $this->getRequest();
        $view = $this->getView(); // TODO

        // TODO Xslt stuff
        if (isset($config->publist->stylesheetDirectory)) {
            $this->stylesheetDirectory = $config->publist->stylesheetDirectory;
        }

        if (isset($config->publist->stylesheet)) {
            $this->stylesheet = $config->publist->stylesheet;
        }
        if (!is_null($request->getParam('stylesheet'))) {
            $this->stylesheet = $request->getParam('stylesheet');
        }

        // TODO params
        $roleParam = $request->getParam('role');
        if (is_null($roleParam)) {
            throw new Application_Exception('role is not specified');
        }

        $numberParam = $request->getParam('number');
        if (is_null($numberParam)) {
            throw new Application_Exception('number is not specified');
        }

        // TODO config
        $groupBy = 'publishedYear';
        if (isset($config->publist->groupby->completedyear)) {
            $groupBy = 'completedYear';
        }

        $collection = $this->mapQuery($roleParam, $numberParam);
        $request->setParam('searchtype', 'collection');
        $request->setParam('id', $collection->getId());
        $request->setParam('export', 'xml');
        $this->_proc->setParameter('', 'collName', $collection->getName());

        $this->_proc->registerPHPFunctions('max');
        $this->_proc->registerPHPFunctions('urlencode');
        $this->_proc->registerPHPFunctions('Export_Model_PublicationList::getMimeTypeDisplayName');
        $this->_proc->setParameter('', 'fullUrl', $view->fullUrl());
        $this->_proc->setParameter('', 'groupBy', $groupBy);

        $this->loadStyleSheet($this->buildStylesheetPath($this->stylesheet,
            $view->getScriptPath('') . $this->stylesheetDirectory));

        $this->prepareXml();
    }

}