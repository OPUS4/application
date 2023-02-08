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

class Solrsearch_Model_Search_Advanced extends Solrsearch_Model_Search_Basic
{
    /**
     * Create form for advanced search.
     *
     * @param Zend_Controller_Request_Http $request
     * @return Solrsearch_Form_AdvancedSearch
     */
    public function createForm($request)
    {
        $searchType = $request->getParam('searchtype'); // TODO FIX where to get searchType

        $form = new Solrsearch_Form_AdvancedSearch($searchType);

        $form->populate($request->getParams());

        $view = $this->getView();

        $form->setAction($view->url([
            'module'     => 'solrsearch',
            'controller' => 'dispatch',
            'action'     => 'index',
        ], null, true));

        return $form;
    }

    /**
     * @param array $input
     * @return Query
     * @throws Zend_Exception
     */
    public function createSearchQuery($input)
    {
        $this->getLogger()->debug("Constructing query for advanced search.");

        $query = new Query(Query::ADVANCED);
        $query->setStart($input['start']);
        $query->setRows($input['rows']);
        $query->setSortField($input['sortField']);
        $query->setSortOrder($input['sortOrder']);

        $facetManager = $this->getFacetManager();

        foreach (['author', 'title', 'persons', 'referee', 'abstract', 'fulltext', 'year'] as $fieldname) {
            if (! empty($input[$fieldname])) {
                $indexField = $fieldname;
                if ($fieldname === 'year') {
                    $facet = $facetManager->getFacet($fieldname);
                    if ($facet !== null) {
                        $indexField = $facet->getIndexField();
                        // do not use inverted field TODO this is a hack - better solution?
                        $indexField = preg_replace('/_inverted/', '', $indexField);
                    }
                }
                $query->setField($indexField, $input[$fieldname], $input[$fieldname . 'modifier']);
            }
        }

        $this->addFiltersToQuery($query, $input);

        //im Falle einer Autorensuche werden Kommas und Semikolons aus dem Suchstring entfernt
        if ($query->getField('author') !== null) {
            $author         = $query->getField('author');
            $authormodifier = $query->getModifier('author');
            $query->setField('author', str_replace([',', ';'], '', $author), $authormodifier);
        }

        if ($this->getExport()) {
            $query->setReturnIdsOnly(true);
        }

        $this->getLogger()->debug("Query $query complete");

        return $query;
    }
}
