<?php
/*
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
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id: EnrichmentkeyControllerTest.php 9263 2011-12-20 18:06:14Z gmaiwald $
 */

/**
 * Basic unit tests for Admin_EnrichmentkeyController class.
 */
class Admin_EnrichmentkeyControllerTest extends ControllerTestCase {

    /**
     * Test showing index page.
     */
    public function testIndexAction() {
        $this->dispatch('/admin/enrichmentkey');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('enrichmentkey');
        $this->assertAction('index');
    }

    public function testIndexActionWithoutEnrichmentkeys() {
        $enrichmentkeys = Opus_EnrichmentKey::getAll();
        $keyNames = array();
        foreach ($enrichmentkeys as $key) {
            array_push($keyNames, $key->getName());
            Opus_EnrichmentKey::fetchbyName($key->getName())->delete();
        }

        $this->dispatch('/admin/enrichmentkey');
        $this->assertResponseCode(200);
        $response = $this->getResponse();
 
        foreach ($keyNames as $key) {
            $ek = new Opus_EnrichmentKey();
            $ek->setName($key);
            $ek->store();
        }
    }

    /**
     * Test show enrichmentkey information.
     */
    public function testShowAction() {
        $this->dispatch('/admin/enrichmentkey/show/name/validtestkey');
        $this->assertResponseCode(200);
        $response = $this->getResponse();
        $this->assertContains('<td>validtestkey</td>', $response->getBody());
    }

    public function testShowActionWithoutId() {
        $this->dispatch('/admin/enrichmentkey/show');
        $this->assertRedirect();
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/enrichmentkey');
    }



    /**
     * Test showing form for new enrichmentkey.
     */
    public function testNewAction() {
        $this->dispatch('/admin/enrichmentkey/new');
        $this->assertResponseCode(200);
        $response = $this->getResponse();
        $this->assertContains('<input type="text" name="name" id="name" value="" />', $response->getBody());
    }

    /**
     * Test showing form for editing enrichmentkey.
     */
    public function testEditAction() {
        $this->dispatch('/admin/enrichmentkey/edit/name/validtestkey');
        $this->assertResponseCode(200);
        $response = $this->getResponse();
        $this->assertContains('<input type="text" name="name" id="name" value="validtestkey" />', $response->getBody());
    }

    public function testEditActionWithoutId() {
        $this->dispatch('/admin/enrichmentkey/edit');
        $this->assertRedirect();
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/enrichmentkey');
    }

    /**
     * Test creating enrichmentkey.
     */
    public function testCreateAction() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'name' => 'testkey',
                    'submit' => 'submit'
                ));

        $this->dispatch('/admin/enrichmentkey/create');
        $this->assertRedirect();
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/enrichmentkey');
        $this->assertNotNull(Opus_EnrichmentKey::fetchByName('testkey'));
    }

    public function testCreateActionCancel() {
         $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'name' => 'testkey2',
                    'cancel' => 'cancel'
                ));
        $this->dispatch('/admin/enrichmentkey/create');
        $this->assertRedirect();
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/enrichmentkey');
        $this->assertNull(Opus_EnrichmentKey::fetchByName('testkey2'));
    }

    public function testCreateActionMissingInput() {
         $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'submit' => 'submit'
                ));

        $this->dispatch('/admin/enrichmentkey/create');
        $this->assertModule('admin');
        $this->assertController('enrichmentkey');
        $this->assertAction('create');
        $this->assertResponseCode(200);
    }

    /**
     * @depends testCreateAction
     */

    public function testUpdateAction() {
        $enrichmentkey = Opus_EnrichmentKey::fetchByName('testkey');

         $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'name' => 'testkey2',
                    'submit' => 'submit'
                ));

        $this->dispatch('/admin/enrichmentkey/update/name/' . $enrichmentkey->getName());
        $this->assertRedirect();
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/enrichmentkey');
        $this->assertNull(Opus_EnrichmentKey::fetchByName('testkey'));
        $this->assertNotNull(Opus_EnrichmentKey::fetchByName('testkey2'));
        $enrichmentkey = Opus_EnrichmentKey::fetchByName('testkey2');
        $this->assertEquals('testkey2', $enrichmentkey->getDisplayName());
    }

    /**
     * @depends testUpdateAction
     */

    public function testUpdateActionInvalidInput() {
         $enrichmentkey = Opus_EnrichmentKey::fetchByName('testkey2');

         $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'name' => '',
                    'submit' => 'submit'
                ));

        $this->dispatch('/admin/enrichmentkey/update/name/' . $enrichmentkey->getName());
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('enrichmentkey');
        $this->assertAction('update');
    }

    public function testUpdateActionWithUsedName() {
         $enrichmentkey = Opus_EnrichmentKey::fetchByName('testkey2');

         $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'name' => 'validtestkey',
                    'submit' => 'submit'
                ));

        $this->dispatch('/admin/enrichmentkey/update/name/' . $enrichmentkey->getName());
        $this->assertResponseCode(200);
        $response = $this->getResponse();
        $this->assertContains('<ul class="errors">', $response->getBody());
    }

    /**
     * @depends testUpdateActionInvalidInput
     */
    public function testDeleteAction() {
        $enrichmentkey = Opus_EnrichmentKey::fetchByName('testkey2');
        $this->assertNotNull($enrichmentkey);
        $this->dispatch('/admin/enrichmentkey/delete/name/' . $enrichmentkey->getName());
        $this->assertRedirect();
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/enrichmentkey');
        $this->assertNull(Opus_EnrichmentKey::fetchByName('testkey2'));
    }

}

