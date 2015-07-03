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
 * @package     Application
 * @author      Edouard Simon <e.simon@aufzuneuenseiten.de>
 * @copyright   Copyright (c) 2008-2015, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Class for navigation in search results.
 */
class Application_Search_Navigation {

    /**
     * Builds query for Solr search.
     * @return Opus_SolrSearch_Query|void
     * @throws Application_Exception, Application_Util_BrowsingParamsException, Application_Util_QueryBuilderException
     */
    public static function getQueryUrl($request, $logger) {

        $queryBuilder = new Application_Util_QueryBuilder($logger);

        $queryBuilderInput = null;

        $queryBuilderInput = $queryBuilder->createQueryBuilderInputFromRequest($request);

        if (is_null($request->getParam('sortfield')) &&
                ($request->getParam('browsing') === 'true' || $request->getParam('searchtype') === 'collection')) {
            $queryBuilderInput['sortField'] = 'server_date_published';
        }
        
        if ($request->getParam('searchtype') === Application_Util_Searchtypes::LATEST_SEARCH) {
            return $queryBuilder->createSearchQuery(self::validateInput($queryBuilderInput, $logger, 10, 100));
        }
        
        return $queryBuilder->createSearchQuery(self::validateInput($queryBuilderInput, $logger));
    }

    /**
     * Adjust the actual rows parameter value if it is not between $min
     * and $max (inclusive). In case the actual value is smaller (greater)
     * than $min ($max) it is adjusted to $min ($max).
     *
     * Sets the actual start parameter value to 0 if it is negative.
     *
     * @param array $data An array that contains the request parameters.
     * @param int $lowerBoundInclusive The lower bound.
     * @param int $upperBoundInclusive The upper bound.
     * @return int Returns the actual rows parameter value or an adjusted value if
     * it is not in the interval [$lowerBoundInclusive, $upperBoundInclusive].
     *
     */
    private static function validateInput($input, $logger, $min = 1, $max = 100) {

        if ($input['rows'] > $max) {
            $logger->warn("Values greater than 100 are currently not allowed for the rows paramter.");
            $input['rows'] = $max;
        }
        if ($input['rows'] < $min) {
            $logger->warn("rows parameter is smaller than 1: adjusting to 1.");
            $input['rows'] = $min;
        }
        if ($input['start'] < 0) {
            $logger->warn("A negative start parameter is ignored.");
            $input['start'] = 0;
        }
        return $input;
    }

}
