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
 * @author      Julian Heise <heise@zib.de>
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Solrsearch_IndexControllerTest extends ControllerTestCase {

    private function doStandardControllerTest($url, $controller, $action) {
        $this->dispatch($url);
        $this->assertResponseCode(200);
        if($controller != null)
            $this->assertController($controller);
        if($action != null)
            $this->assertAction($action);
    }

    public function testIndexAction() {
        $this->doStandardControllerTest('/solrsearch', 'index', 'index');
    }

    public function testAdvancedAction() {
        $this->doStandardControllerTest('/solrsearch/index/advanced', 'index', 'advanced');
        $response = $this->getResponse();
        $this->checkForBadStringsInHtml($response->getBody());
    }

    public function testNohitsAction() {
        $this->doStandardControllerTest('/solrsearch/index/nohits', 'index', 'nohits');
        $response = $this->getResponse();
        $this->checkForBadStringsInHtml($response->getBody());
    }

    public function testLatestAction() {
        $this->markTestIncomplete("Test waiting for completion.");

        $this->doStandardControllerTest('/solrsearch/index/latest', 'index', 'latest');
        $response = $this->getResponse();
        $this->checkForBadStringsInHtml($response->getBody());
    }

    public function testSearchdispatchAction() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'searchtype' => 'simple',
                    'query'=>'*:*'
                ));
        $this->dispatch('/solrsearch/index/searchdispatch');
        $this->assertRedirect();
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'searchtype' => 'advanced',
                    'author'=>'a*'
                ));
        $this->dispatch('/solrsearch/index/searchdispatch');
        $this->assertRedirect();
    }

    public function testSearchAction() {
        $this->markTestIncomplete("Test waiting for completion.");

        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/simple/query/*:*', null, null);
        $response = $this->getResponse();
        $body = strtolower($response->getBody());
        $this->assertContains('results_title', $body);
        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/advanced/author/doe', null, null);
        $response = $this->getResponse();
        $body = strtolower($response->getBody());
        $this->assertContains('results_title', $body);
    }

    public function testInvalidsearchtermAction() {
        $searchtypeParams = array ('', 'searchtype/simple', 'searchtype/advanced', 'searchtype/foo');
        foreach ($searchtypeParams as $searchtypeParam) {
            $this->dispatch('/solrsearch/index/invalidsearchterm/' . $searchtypeParam);
            $this->assertResponseCode(200);
            $responseBody = $this->getResponse()->getBody();
            $this->assertContains('<div class="invalidsearchterm">', $responseBody);
        }
    }

    public function testEmptySimpleQuery() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'searchtype' => 'simple',
                    'query' => ''
                ));
        $this->dispatch('/solrsearch/index/searchdispatch');
        $this->assertRedirect();
        //$this->assertRedirectTo('/solrsearch/index/invalidsearchterm');
    }

    public function testEmptyAdvancedQuery() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'searchtype' => 'advanced'
                ));
        $this->dispatch('/solrsearch/index/searchdispatch');
        $this->assertRedirect();
        //$this->assertRedirectTo('/solrsearch/index/invalidsearchterm');
    }

}
?>