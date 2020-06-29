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
 * @category    Tests
 * @package     Solrsearch_Model_Search
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2017-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Solrsearch_Model_Search_BasicTest extends ControllerTestCase
{

    protected $configModifiable = true;

    public function testCreateQueryBuilderInputFromRequest()
    {
        $request = $this->getRequest();
        $request->setParams(['searchtype' => 'all',
            'start' => '0',
            'rows' => '1337',
            'sortOrder' => 'desc']);

        $queryBuilder = new Solrsearch_Model_Search_Basic();

        $result = $queryBuilder->createQueryBuilderInputFromRequest($request);

        $this->assertEquals($result['start'], 0);
        $this->assertEquals($result['rows'], 1337);
        $this->assertEquals($result['sortOrder'], 'desc');
    }

    /**
     * Test für OPUSVIER-2708.
     */
    public function testGetRowsFromConfig()
    {
        $config = Zend_Registry::get('Zend_Config');
        $oldParamRows = $config->searchengine->solr->numberOfDefaultSearchResults;
        $config->searchengine->solr->numberOfDefaultSearchResults = '1337';

        $request = $this->getRequest();
        $request->setParams(['searchtype' => 'all']);

        $queryBuilder = new Solrsearch_Model_Search_Basic();
        $result = $queryBuilder->createQueryBuilderInputFromRequest($request);

        //clean-up
        $config->searchengine->solr->numberOfDefaultSearchResults = $oldParamRows;

        $this->assertEquals($result['rows'], 1337);
    }
}
