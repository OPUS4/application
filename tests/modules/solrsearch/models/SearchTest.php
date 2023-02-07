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

class Solrsearch_Model_SearchTest extends ControllerTestCase
{
    /** @var bool */
    protected $configModifiable = true;

    public function testCreateSimpleSearchUrlParams()
    {
        $request = $this->getRequest();

        $request->setParams([
            'searchtype' => 'all',
            'start'      => '30',
            'rows'       => '15',
            'query'      => 'test',
            'sortfield'  => 'year',
            'sortorder'  => 'asc',
        ]);

        $model = new Solrsearch_Model_Search();

        $params = $model->createSimpleSearchUrlParams($request);

        $this->assertCount(6, $params);

        $this->assertArrayHasKey('searchtype', $params);
        $this->assertEquals(Application_Util_Searchtypes::ALL_SEARCH, $params['searchtype']);

        $this->assertArrayHasKey('start', $params);
        $this->assertEquals(30, $params['start']);

        $this->assertArrayHasKey('rows', $params);
        $this->assertEquals(15, $params['rows']);

        $this->assertArrayHasKey('query', $params);
        $this->assertEquals('test', $params['query']);

        $this->assertArrayHasKey('sortfield', $params);
        $this->assertEquals('year', $params['sortfield']);

        $this->assertArrayHasKey('sortorder', $params);
        $this->assertEquals('asc', $params['sortorder']);
    }

    public function testCreateSimpleSearchUrlParamsWithDefaults()
    {
        $request = $this->getRequest();

        $model = new Solrsearch_Model_Search();

        $params = $model->createSimpleSearchUrlParams($request);

        $this->assertCount(6, $params);

        $this->assertArrayHasKey('searchtype', $params);
        $this->assertEquals(Application_Util_Searchtypes::SIMPLE_SEARCH, $params['searchtype']);

        $this->assertArrayHasKey('start', $params);
        $this->assertEquals(0, $params['start']);

        $this->assertArrayHasKey('rows', $params);
        $this->assertEquals(10, $params['rows']);

        $this->assertArrayHasKey('query', $params);
        $this->assertEquals('*:*', $params['query']);

        $this->assertArrayHasKey('sortfield', $params);
        $this->assertEquals('score', $params['sortfield']);

        $this->assertArrayHasKey('sortorder', $params);
        $this->assertEquals('desc', $params['sortorder']);
    }

    public function testCreateSimpleSearchUrlParamsWithFilter()
    {
        $request = $this->getRequest();

        $request->setParam('institutefq', 'Technische+Univeristät+Hamburg-Harburg');

        $model = new Solrsearch_Model_Search();

        $params = $model->createSimpleSearchUrlParams($request);

        $this->assertArrayHasKey('institutefq', $params);
        $this->assertEquals('Technische+Univeristät+Hamburg-Harburg', $params['institutefq']);
    }

    public function testCreateSimpleSearchUrlParamsWithCustomRows()
    {
        $request = $this->getRequest();

        $this->adjustConfiguration([
            'searchengine' => ['solr' => ['numberOfDefaultSearchResults' => '25']],
        ]);

        $model = new Solrsearch_Model_Search();

        $params = $model->createSimpleSearchUrlParams($request);

        $this->assertArrayHasKey('rows', $params);
        $this->assertEquals(25, $params['rows']);
    }

    public function testCreateAdvancedSearchUrlParams()
    {
        $request = $this->getRequest();

        $request->setParams([
            'searchtype'     => 'all',
            'start'          => '30',
            'rows'           => '15',
            'sortfield'      => 'year',
            'sortorder'      => 'asc',
            'author'         => 'TestAuthor',
            'authormodifier' => 'contains_all',
            'title'          => 'TestTitle',
//            'persons' => 'TestPerson',
            'referee'          => 'TestReferee',
            'refereemodifier'  => 'contains_any',
            'abstract'         => 'TestAbstract',
            'fulltext'         => 'TestWord',
            'fulltextmodifier' => 'contains_none',
            'year'             => '2008',
        ]);

        $model = new Solrsearch_Model_Search();

        $params = $model->createAdvancedSearchUrlParams($request);

        $this->assertCount(17, $params);

        $this->assertArrayHasKey('searchtype', $params);
        $this->assertEquals(Application_Util_Searchtypes::ALL_SEARCH, $params['searchtype']);

        $this->assertArrayHasKey('start', $params);
        $this->assertEquals(30, $params['start']);

        $this->assertArrayHasKey('rows', $params);
        $this->assertEquals(15, $params['rows']);

        $this->assertArrayHasKey('sortfield', $params);
        $this->assertEquals('year', $params['sortfield']);

        $this->assertArrayHasKey('sortorder', $params);
        $this->assertEquals('asc', $params['sortorder']);

        $this->assertArrayHasKey('author', $params);
        $this->assertEquals('TestAuthor', $params['author']);
        $this->assertArrayHasKey('authormodifier', $params);
        $this->assertEquals('contains_all', $params['authormodifier']);

        $this->assertArrayHasKey('title', $params);
        $this->assertEquals('TestTitle', $params['title']);
        $this->assertArrayHasKey('titlemodifier', $params);
        $this->assertEquals('contains_all', $params['titlemodifier']);

        $this->assertArrayNotHasKey('persons', $params);
        // $this->assertEquals('TestPerson', $params['persons']);
        $this->assertArrayNotHasKey('personsmodifier', $params);
        // $this->assertEquals('contains_all', $params['personsmodifier']);

        $this->assertArrayHasKey('referee', $params);
        $this->assertEquals('TestReferee', $params['referee']);
        $this->assertArrayHasKey('refereemodifier', $params);
        $this->assertEquals('contains_any', $params['refereemodifier']);

        $this->assertArrayHasKey('abstract', $params);
        $this->assertEquals('TestAbstract', $params['abstract']);
        $this->assertArrayHasKey('abstractmodifier', $params);
        $this->assertEquals('contains_all', $params['abstractmodifier']);

        $this->assertArrayHasKey('fulltext', $params);
        $this->assertEquals('TestWord', $params['fulltext']);
        $this->assertArrayHasKey('fulltextmodifier', $params);
        $this->assertEquals('contains_none', $params['fulltextmodifier']);

        $this->assertArrayHasKey('year', $params);
        $this->assertEquals('2008', $params['year']);
        $this->assertArrayHasKey('yearmodifier', $params);
        $this->assertEquals('contains_all', $params['yearmodifier']);
    }

    public function testIsSimpleSearchRequestValidTrue()
    {
        $model = new Solrsearch_Model_Search();

        $request = $this->getRequest();

        $request->setParam('query', 'test');

        $this->assertTrue($model->isSimpleSearchRequestValid($request));
    }

    public function testIsSimpleSearchRequestValidFalse()
    {
        $model = new Solrsearch_Model_Search();

        $request = $this->getRequest();

        $this->assertFalse($model->isSimpleSearchRequestValid($request));

        $request->setParam('query', '');

        $this->assertFalse($model->isSimpleSearchRequestValid($request));

        $request->setParam('query', '   ');

        $this->assertFalse($model->isSimpleSearchRequestValid($request));
    }

    public function testIsAdvancedSearchRequestValidTrue()
    {
        $model = new Solrsearch_Model_Search();

        $request = $this->getRequest();

        $request->setParam('title', 'test');

        $this->assertTrue($model->isAdvancedSearchRequestValid($request));

        $request->setParams([
            'author'   => 'TestAuthor',
            'title'    => 'TestTitle',
            'persons'  => '    ',
            'referee'  => '',
            'abstract' => 'TestAbstract',
            'fulltext' => 'TestWord',
            'year'     => '2008',
        ]);

        $this->assertTrue($model->isAdvancedSearchRequestValid($request));
    }

    public function testIsAdvancedSearchRequestValidFalse()
    {
        $model = new Solrsearch_Model_Search();

        $request = $this->getRequest();

        $this->assertFalse($model->isAdvancedSearchRequestValid($request));

        $request->setParam('persons', '   ');

        $this->assertFalse($model->isAdvancedSearchRequestValid($request));
    }

    public function testGetFilterParams()
    {
        $request = $this->getRequest();
        $request->setParam('institutefq', 'ZIB');
        $request->setParam('searchtype', 'simple');
        $request->setParam('unknown', 'param');
        $request->setParam('has_fulltextfq', 'true');

        $model = new Solrsearch_Model_Search();

        $params = $model->getFilterParams($request);

        $this->assertCount(2, $params);
        $this->assertEquals([
            'institutefq'    => 'ZIB',
            'has_fulltextfq' => 'true',
        ], $params);
    }
}
