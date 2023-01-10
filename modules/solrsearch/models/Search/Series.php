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
 * @copyright   Copyright (c) 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Search\Util\Query;

class Solrsearch_Model_Search_Series extends Solrsearch_Model_Search_Abstract
{
    /**
     * @param Zend_Controller_Request_Http $request
     * @return Query|null
     * @throws Zend_Exception
     */
    public function buildQuery($request)
    {
        if (! $this->prepareSeries($request)) {
            return null;
        }

        return parent::buildQuery($request);
    }

    /**
     * @param Zend_Controller_Request_Http $request
     * @return bool
     * @throws Zend_Exception
     */
    public function prepareSeries($request)
    {
        $series = null;

        try {
            $series = new Solrsearch_Model_Series($request->getParam('id'));
        } catch (Solrsearch_Model_Exception $sme) {
            $this->getLogger()->debug($sme->getMessage());
            $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
            $redirector->redirectToAndExit('index', '', 'browse', null, [], true);
            return false;
        }

        $view = $this->getView();

        $view->title        = $series->getTitle();
        $view->seriesId     = $series->getId();
        $view->infobox      = $series->getInfobox();
        $view->logoFilename = $series->getLogoFilename();

        return true;
    }

    /**
     * @param array $input
     * @return Query
     * @throws Zend_Exception
     */
    public function createSearchQuery($input)
    {
        $this->getLogger()->debug("Constructing query for series search.");

        $query = new Query(Query::SIMPLE);
        $query->setStart($input['start']);
        $query->setRows($input['rows']);
        if (
            $input['sortField'] === 'seriesnumber'
            || $input['sortField'] === Opus\Search\Query::getDefaultSortingField()
        ) {
            $query->setSortField('doc_sort_order_for_seriesid_' . $input['seriesId']);
        } else {
            $query->setSortField($input['sortField']);
        }
        $query->setSortOrder($input['sortOrder']);

        $query->setCatchAll('*:*');
        $query->addFilterQuery('series_ids', $input['seriesId']);
        $this->addFiltersToQuery($query, $input);

        if ($this->getExport()) {
            $query->setReturnIdsOnly(true);
        }

        $this->getLogger()->debug("Query $query complete");
        return $query;
    }

    /**
     * @param Zend_Controller_Request_Http $request
     * @return array
     * @throws Application_Search_QueryBuilderException
     * @throws Application_Util_BrowsingParamsException
     * @throws Zend_Exception
     */
    public function createQueryBuilderInputFromRequest($request)
    {
        $input = parent::createQueryBuilderInputFromRequest($request);

        $searchParams      = new Application_Util_BrowsingParams($request, $this->getLogger());
        $input['seriesId'] = $searchParams->getSeriesId();

        return $input;
    }
}
