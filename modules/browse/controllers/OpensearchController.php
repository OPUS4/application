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
 * @package     Module_Search
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id: SearchController.php 2435 2009-04-09 14:03:53Z marahrens $
 */

/**
 * Controller for search operations
 *
 */
class Browse_OpensearchController extends Controller_Xml
{
    /**
     * Perform a get search request with an OpenSearch compliant result set.
     *
     * @return void
     */
    public function queryAction() {
        $requestData = $this->_request->getParams();

        $search = new Browse_Model_OpenSearch($requestData['q']);
        
        if (true === isset($requestData['format'])) $format = $requestData['format'];
        else $format = 'rss';
        if (true === isset($requestData['start'])) $search->startOffset = $requestData['start'];
        if (true === isset($requestData['items'])) $search->itemsPerPage = $requestData['items'];
        
        switch ($format) {
        	case 'atom':
        	    $result = $search->getAtomResult();
        	    break;
        	case 'rss':
        	    $result = $search->getRssResult();
        	    break;
        	default:
        	    die('Result format not supported!');
        	    break;
        }
        $this->_xml->loadXml($result['xml']);
    }

    /**
     * Output the OpenSearch-description file for this host.
     *
     * @return void
     */
    public function descriptionAction() {
        $requestData = $this->_request->getParams();

        $search = new Browse_Model_OpenSearch();

        $result = $search->getDescription();
        $this->_xml->loadXml($result['xml']);
    }
}
