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
 * Controller for redirecting search requests in order to create bookmarkable URLs for searches.
 *
 * TODO eliminate controller (merge with IndexController, move code to model for testing)
 */
class Solrsearch_DispatchController extends Application_Controller_Action
{
    public function indexAction()
    {
        $this->getLogger()->debug('Received new search request. Redirecting to search action of IndexController.');

        $params = [];
        $action = 'search';

        $searchModel = new Solrsearch_Model_Search();

        $request = $this->getRequest();

        $searchType = $request->getParam('searchtype', 'invalid searchtype');

        if (in_array($searchType, ['advanced', 'authorsearch']) && $this->getParam('Reset') !== null) {
            $this->_helper->Redirector->redirectTo('advanced', null, 'index', 'solrsearch');
            return;
        }

        switch ($searchType) {
            case Application_Util_Searchtypes::SIMPLE_SEARCH:
                if (! $searchModel->isSimpleSearchRequestValid($request)) {
                    $action = 'invalidsearchterm';
                    $params = ['searchtype' => Application_Util_Searchtypes::SIMPLE_SEARCH];
                } else {
                    $params = $searchModel->createSimpleSearchUrlParams($request);
                }
                break;
            case Application_Util_Searchtypes::ADVANCED_SEARCH:
            case Application_Util_Searchtypes::AUTHOR_SEARCH:
                if (! $searchModel->isAdvancedSearchRequestValid($request)) {
                    $action = 'invalidsearchterm';
                    $params = ['searchtype' => $searchType];
                } else {
                    $params = $searchModel->createAdvancedSearchUrlParams($request);
                }
                break;
            default:
                break;
        }

        $this->_helper->Redirector->redirectToPermanentAndExit($action, null, 'index', null, $params);
    }
}
