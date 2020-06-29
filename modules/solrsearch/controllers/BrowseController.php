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
 * @package     Module_Solrsearch
 * @author      Sascha Szott <szott@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Class Solrsearch_BrowseController
 *
 * TODO move list handling into model
 * TODO use one action for different lists, so that a list can be added without changes
 * TODO eliminate standard list rendering PHTML
 */
class Solrsearch_BrowseController extends Application_Controller_Action
{

    /**
     * @var Solrsearch_Model_SeriesUtil
     */
    private $seriesUtil;

    public function init()
    {
        parent::init();
        $this->_helper->mainMenu('browsing');
        $this->seriesUtil = new Solrsearch_Model_SeriesUtil();
        $this->view->robots = 'noindex, nofollow';
    }

    public function indexAction()
    {
        $this->view->baseUrl = $this->getRequest()->getBaseUrl();
        $collectionRoles = new Solrsearch_Model_CollectionRoles();
        $this->view->collectionRoles = $collectionRoles->getAllVisible();
        $this->view->showSeriesBrowsing = $this->seriesUtil->hasDisplayableSeries();
    }

    public function doctypesAction()
    {
        $facetname = 'doctype';
        $query = new Opus\Search\Util\Query(Opus\Search\Util\Query::FACET_ONLY);
        $query->setFacetField($facetname);

        try {
            $searcher = new Opus\Search\Util\Searcher();
            $facets = $searcher->search($query)->getFacets();
        } catch (Opus\Search\Exception $e) {
            $this->getLogger()->err(__METHOD__ . ' : ' . $e);
            throw new Application_SearchException($e);
        }

        $docTypesTranslated = [];
        foreach ($facets[$facetname] as $facetitem) {
            $translation = $this->view->translate($facetitem->getText());
            $docTypesTranslated[$translation] = $facetitem;
        }
        uksort($docTypesTranslated, "strnatcasecmp");
        $this->view->facetitems = $docTypesTranslated;
        $this->view->title = $this->view->translate('solrsearch_browse_doctypes');
    }

    public function yearsAction()
    {
        $facetname = 'year';

        $query = new Opus\Search\Util\Query(Opus\Search\Util\Query::FACET_ONLY);
        $query->setFacetField($facetname);

        try {
            $searcher = new Opus\Search\Util\Searcher();
            $facets = $searcher->search($query)->getFacets();
        } catch (Opus\Search\Exception $ose) {
            $this->getLogger()->err(__METHOD__ . ' : ' . $ose);
            throw new Application_SearchException($ose);
        }

        $years = $facets[$facetname];

        krsort($years);

        $this->view->facetitems = $years;
        $this->view->title = 'solrsearch_browse_years';
    }

    /**
     * Lists all visible series with at least on document.
     */
    public function seriesAction()
    {
        $visibleSeries = $this->seriesUtil->getVisibleSeries();

        if (count($visibleSeries) == 0) {
            $this->_helper->Redirector->redirectToAndExit('index');
        }

        $this->view->series = $visibleSeries;
    }
}
