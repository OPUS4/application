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
 * @package     Tests
 * @author      Michael Lang <lang@zib.de>
 * @copyright   Copyright (c) 2014, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */


class Solrsearch_Model_FacetMenuTest extends ControllerTestCase {

    public function testGetFacetLimitsFromConfig() {
        $model = new Solrsearch_Model_FacetMenu();
        $config = Zend_Registry::get('Zend_Config');
        $config->merge(new Zend_Config(array('searchengine' =>
            array('solr' =>
                array('facetlimit' =>
                    array('author_facet' => 3,
                          'year'         => 15))))));

        $facetLimits = Opus_Search_Config::getFacetLimits();

        $this->assertEquals(3, $facetLimits['author_facet']);
        $this->assertEquals(15, $facetLimits['year']);
        $this->assertEquals(10, $facetLimits['doctype']);
        $this->assertEquals(10, $facetLimits['has_fulltext']);
        $this->assertEquals(10, $facetLimits['belongs_to_bibliography']);
        $this->assertEquals(10, $facetLimits['subject']);
        $this->assertEquals(10, $facetLimits['institute']);
    }

    public function testGetFacetLimitsFromConfigWithYearInverted() {
        $model = new Solrsearch_Model_FacetMenu();
        $config = Zend_Registry::get('Zend_Config');
        if (isset($config->searchengine->solr->facets)) {
            $config->searchengine->solr->facets = 'year_inverted,doctype,author_facet,language,has_fulltext,belongs_to_bibliography,subject,institute';
        }
        else {
            $testConfig = new Zend_Config(array(
                'searchengine' => array(
                    'solr' => array(
                        'facets' => 'year_inverted,doctype,author_facet,language,has_fulltext,belongs_to_bibliography,subject,institute'))), true);
            // Include the above made configuration changes in the application configuration.
            $testConfig->merge($config);
        }
        $config->merge(new Zend_Config(array('searchengine' =>
            array('solr' =>
                array('facetlimit' =>
                    array('author_facet'  => 3,
                          'year_inverted' => 15))))));

        $facetLimits = Opus_Search_Config::getFacetLimits();

        $this->assertEquals(3, $facetLimits['author_facet']);
        $this->assertEquals(15, $facetLimits['year']);
        $this->assertEquals(10, $facetLimits['doctype']);
        $this->assertEquals(10, $facetLimits['has_fulltext']);
        $this->assertEquals(10, $facetLimits['belongs_to_bibliography']);
        $this->assertEquals(10, $facetLimits['subject']);
        $this->assertEquals(10, $facetLimits['institute']);
    }

    public function testBuildFacetArray() {
        $model = new Solrsearch_Model_FacetMenu();
        $paramSet = array(
            'facetNumber_author_facet' => 'all',
            'facetNumber_year' => 'all',
            'facetNumber_subject' => 'all'
        );
        $facetArray = $model->buildFacetArray($paramSet);
        $this->assertEquals(10000, $facetArray['author_facet']);
        $this->assertEquals(10000, $facetArray['year']);
        $this->assertEquals(10000, $facetArray['subject']);
        $this->assertNotContains('institute', $facetArray);
        $this->assertNotContains('doctype', $facetArray);
        $this->assertNotContains('language', $facetArray);
        $this->assertNotContains('year_inverted', $facetArray);
    }

    /**
     * If 'year_inverted' is set in config, buildFacetArray should contain both entries ('year' & 'year_inverted'), because,
     * in framework, 'year_inverted' is expected and changed to 'year'. Hence, as result from framework 'year' is expected.
     */
    public function testBuildFacetArrayWithYearInverted() {
        $model = new Solrsearch_Model_FacetMenu();
        $config = Zend_Registry::get('Zend_Config');
        if (isset($config->searchengine->solr->facets)) {
            $config->searchengine->solr->facets = 'year_inverted,doctype,author_facet,language,has_fulltext,belongs_to_bibliography,subject,institute';
        }
        else {
            $testConfig = new Zend_Config(array(
                'searchengine' => array(
                    'solr' => array(
                        'facets' => 'year_inverted,doctype,author_facet,language,has_fulltext,belongs_to_bibliography,subject,institute'))), true);
            // Include the above made configuration changes in the application configuration.
            $testConfig->merge($config);
        }
        $paramSet = array('facetNumber_year' => 'all');
        $facetArray = $model->buildFacetArray($paramSet);
        $this->assertEquals(10000, $facetArray['year']);
        $this->assertEquals(10000, $facetArray['year_inverted']);
    }

    public function testBuildEmptyFacetArray() {
        $model = new Solrsearch_Model_FacetMenu();
        $this->assertNull($model->buildFacetArray(array()));
    }

    public function testPrepareViewFacetHideInstitutes()
    {
        $this->markTestIncomplete('not implemented');
    }

}
