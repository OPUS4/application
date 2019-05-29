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
 */

/**
 * Wurde für die Diagnose von Abhängigkeiten zwischen den Tests verwendet.
 *
 * @category    Application Unit Test
 * @package     Support
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class AssumptionChecker {

    private $testCase;

    public function __construct($testCase) {
        $this->testCase = $testCase;
    }

    /**
     * Kann verwendet werden wenn es Probleme mit
     *
     * Solrsearch_IndexControllerTest;;testFacetSortForYearInverted
     *
     * gibt. Einfache am Anfang des tearDown in ControllerTestCase aufrufen.
     *
     * $assumption = new AssumptionChecker($this);
     * $assumption->checkYearFacetAssumption();
     */
    public function checkYearFacetAssumption() {
        $this->testCase->resetRequest();
        $this->testCase->resetResponse();

        Zend_Registry::get('Zend_Config')->merge(new Zend_Config(
            array('searchengine' => array('solr' => array(
                'facetlimit' => array('year' => 10, 'year_inverted' => 10),
                'facets' => 'year,doctype,author_facet,language,has_fulltext,belongs_to_bibliography,subject,institute'
            )))
        ));

        $this->testCase->dispatch('/solrsearch/index/search/searchtype/all');

        $searchStrings = array(
            '2011',
            '2009',
            '2010',
            '1978',
            '2008',
            '2012',
            '1979',
            '1962',
            '1963',
            '1975');

        $response = $this->testCase->getResponse()->getBody();
        $startString = 'id="year_facet"';

        $startPos = strpos($response, $startString);
        $this->testCase->assertFalse($startPos === false, "'$startString' not found, instead: $response");
        $lastPos = $startPos;
        $loopComplete = true;
        for ($i = 0; $i < 10; $i++) {
            $lastPos = strpos($response, '>' . $searchStrings[$i] . '</a>', $lastPos);
            if ($lastPos === false) {
                Zend_Debug::dump("'" . $searchStrings[$i] . '\' not found in year facet list (iteration ' . $i . ')');
            }
            $this->testCase->assertFalse($lastPos === false, "'" . $searchStrings[$i] . '\' not found in year facet list (iteration ' . $i . ')');
            if ($lastPos === false) {
                break;
                $loopComplete = false;
            }
        }
        $this->testCase->assertTrue($loopComplete);
    }

}

