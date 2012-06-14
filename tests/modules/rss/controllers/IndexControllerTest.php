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
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Rss_IndexControllerTest extends ControllerTestCase {

    public function setUp($applicationEnv = APPLICATION_ENV) {
        parent::setUp($applicationEnv);
    }

    public function testIndexAction() {
        $this->dispatch('/rss/index/index');
        $this->assertResponseCode(200, $this->getResponse()->getBody());
        $response = $this->getResponse();
        $this->assertContains('<?xml version="1.0" encoding="utf-8"?>', $response->getBody());
        $this->assertContains('<rss version="2.0">', $response->getBody());
    }

    /**
     * Regression test for OPUSVIER-2337
     */
    public function testUnavailableSolrServerReturns503() {
        $this->setUp('production');

        // manipulate solr configuration
        $config = Zend_Registry::get('Zend_Config');
        $host = $config->searchengine->index->host;
        $port = $config->searchengine->index->port;
        $oldValue = $config->searchengine->index->app;
        $config->searchengine->index->app = 'solr/corethatdoesnotexist';
        Zend_Registry::set('Zend_Config', $config);

        $this->dispatch('/rss/index/index/searchtype/all');

        $body = $this->getResponse()->getBody();
        $this->assertNotContains("http://${host}:${port}/solr/corethatdoesnotexist", $body);
        $this->assertContains('search server is not responding -- try again later', $body);
        $this->assertResponseCode(503);

        // restore configuration
        $config = Zend_Registry::get('Zend_Config');
        $config->searchengine->index->app = $oldValue;
        Zend_Registry::set('Zend_Config', $config);
        
    }
}

