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
 * @version     $Id$
 */

/**
 * Basic unit tests for Admin_EnrichmentkeyController class.
 */
class Admin_EnrichmentkeyControllerTest extends ControllerTestCase {

    private static $protectedEnrichmentkey = 'review.accepted_by';
    private static $protectedUnusedEnrichmentkey = 'SubjectSwd';

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
        $enrichment = $this->_removeEnrichmentAssociation();

        $keyNames = $this->_removeAllEnrichmentKeys();
        if (!is_array($keyNames)) {
            $this->_restoreEnrichmentAssociation($enrichment);
            $this->markTestSkipped("Skipped, because EnrichmentKey '$keyNames' could not be deleted.");
        }

        // call index action of controller
        $this->dispatch('/admin/enrichmentkey');
        $this->assertResponseCode(200);
        $response = $this->getResponse();

        $this->_addEnrichmentKeys($keyNames);
        $this->_restoreEnrichmentAssociation($enrichment);
    }

    protected function _removeEnrichmentAssociation() {
        $d = new Opus_Document(146);
        $enrichment = $d->getEnrichment();
        $this->assertEquals(1, count($enrichment), "Test data has changed.");
        $d->setEnrichment(null);
        $d->store();
        return $enrichment;
    }

    protected function _restoreEnrichmentAssociation($enrichment) {
        $d = new Opus_Document(146);
        $newEnrichment = new Opus_Enrichment();
        $newEnrichment->setKeyName($enrichment[0]->getKeyName())->setValue($enrichment[0]->getValue());
        $d->addEnrichment($newEnrichment);
        $d->store();
    }

    protected function _removeAllEnrichmentKeys() {
        $enrichmentkeys = Opus_EnrichmentKey::getAll();
        $keyNames = array();
        $deletedKeys = array();
        foreach ($enrichmentkeys as $key) {
            array_push($keyNames, $key->getName());
            try {
                Opus_EnrichmentKey::fetchbyName($key->getName())->delete();
                $deletedKeys[] = $key->getName();
            }
            catch (Opus_Model_Exception $e) {
                $this->_addEnrichmentKeys($deletedKeys);
                return $key->getName();
            }
        }
        return $keyNames;
    }

    protected function _addEnrichmentKeys($keyNames) {
        foreach ($keyNames as $key) {
            $ek = new Opus_EnrichmentKey();
            $ek->setName($key);
            $ek->store();
        }
    }

    public function testIndexActionWithProtectedEnrichmentKeys() {
        $ek = Opus_EnrichmentKey::fetchByName(self::$protectedEnrichmentkey);
        $this->assertNotNull($ek);
        $config = Zend_Registry::get('Zend_Config');
        $this->assertTrue(in_array($ek->getName(), explode(',', $config->enrichmentkey->protected->modules)));

        $this->dispatch('/admin/enrichmentkey');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('enrichmentkey');
        $this->assertAction('index');

        $this->assertContains('/admin/enrichmentkey/show/name/' . $ek->getName(), $this->getResponse()->getBody());
        $this->assertNotContains('/admin/enrichmentkey/edit/name/' . $ek->getName(), $this->getResponse()->getBody());
        $this->assertNotContains('/admin/enrichmentkey/delete/name/' . $ek->getName(), $this->getResponse()->getBody());
    }

    /**
     * Test show enrichmentkey information.
     */
    public function testShowAction() {
        $ek = new Opus_EnrichmentKey();
        $ek->setName('testShowAction-foo');
        $ek->store();

        $this->dispatch('/admin/enrichmentkey/show/name/' . $ek->getName());
        $this->assertResponseCode(200);
        $this->assertContains('<td>' . $ek->getName() . '</td>', $this->getResponse()->getBody());

        $ek->delete();
    }

    public function testShowActionWithoutNameParam() {
        $this->dispatch('/admin/enrichmentkey/show');
        $this->assertRedirect();
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/enrichmentkey');
    }

    public function testShowActionWithUnknownNameParam() {
        $this->assertNull(Opus_EnrichmentKey::fetchByName('testShowActionWithUnknownNameParam'));
        $this->dispatch('/admin/enrichmentkey/show/name/testShowActionWithUnknownNameParam');
        $this->assertRedirect();
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/enrichmentkey');
    }

    /**
     * Test showing empty form for new enrichmentkey.
     */
    public function testNewAction() {
        $this->dispatch('/admin/enrichmentkey/new');
        $this->assertResponseCode(200);
        $this->assertContains('<input type="text" name="name" id="name" value="" />', $this->getResponse()->getBody());
    }

    /**
     * Test showing form for editing enrichmentkey.
     */
    public function testEditAction() {
        $ek = new Opus_EnrichmentKey();
        $ek->setName('testEditAction-foo');
        $ek->store();

        $this->dispatch('/admin/enrichmentkey/edit/name/' . $ek->getName());
        $this->assertResponseCode(200);
        $this->assertContains('<input type="text" name="name" id="name" value="' . $ek->getName() . '" />', $this->getResponse()->getBody());

        $ek->delete();
    }

    public function testEditActionWithoutNameParam() {
        $this->dispatch('/admin/enrichmentkey/edit');
        $this->assertRedirect();
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/enrichmentkey');
    }

    public function testEditActionWithUnknownNameParam() {
        $this->assertNull(Opus_EnrichmentKey::fetchByName('testEditActionWithUnknownNameParam'));
        $this->dispatch('/admin/enrichmentkey/edit/name/testEditActionWithUnknownNameParam');
        $this->assertRedirect();
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/enrichmentkey');
    }

    public function testEditActionProtectedEnrichmentKey() {
        $ek = Opus_EnrichmentKey::fetchByName(self::$protectedEnrichmentkey);
        $this->assertNotNull($ek);
        $config = Zend_Registry::get('Zend_Config');
        $this->assertTrue(in_array($ek->getName(), explode(',', $config->enrichmentkey->protected->modules)));

        $this->dispatch('/admin/enrichmentkey/edit/name/' . $ek->getName());
        $this->assertRedirect();
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/enrichmentkey');
    }

    /**
     * Test creating enrichmentkey.
     */
    public function testCreateAction() {
        $this->assertNull(Opus_EnrichmentKey::fetchByName('testCreateAction'));

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'name' => 'testCreateAction',
                    'submit' => 'submit'
                ));

        $this->dispatch('/admin/enrichmentkey/create');
        $this->assertRedirect();
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/enrichmentkey');
        $this->assertNotNull(Opus_EnrichmentKey::fetchByName('testCreateAction'));

        $enrichmentkey = Opus_EnrichmentKey::fetchByName('testCreateAction');
        $this->assertEquals('testCreateAction', $enrichmentkey->getDisplayName());
        $enrichmentkey->delete();
    }

    public function testCreateActionCancel() {
        $this->assertNull(Opus_EnrichmentKey::fetchByName('testCreateActionCancel'));

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'name' => 'testCreateActionCancel',
                    'cancel' => 'cancel'
                ));
        $this->dispatch('/admin/enrichmentkey/create');
        $this->assertRedirect();
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/enrichmentkey');
        $this->assertNull(Opus_EnrichmentKey::fetchByName('testCreateActionCancel'));
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

    public function testCreateActionDuplicateName() {
        $ek = new Opus_EnrichmentKey();
        $ek->setName('testCreateActionDuplicateName');
        $ek->store();

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'name' => 'testCreateActionDuplicateName',
                    'submit' => 'submit'
                ));

        $this->dispatch('/admin/enrichmentkey/create');
        $this->assertResponseCode(200);
        $this->assertContains('<ul class="errors">', $this->getResponse()->getBody());

        $ek->delete();
    }

    public function testCreateActionProtectedEnrichmentKey() {
        $ek = Opus_EnrichmentKey::fetchByName(self::$protectedEnrichmentkey);
        $this->assertNotNull($ek);
        $config = Zend_Registry::get('Zend_Config');
        $this->assertTrue(in_array($ek->getName(), explode(',', $config->enrichmentkey->protected->modules)));

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'name' => $ek->getName(),
                    'submit' => 'submit'
                ));

        $this->dispatch('/admin/enrichmentkey/create');
        $this->assertResponseCode(200);
        $this->assertContains('<ul class="errors">', $this->getResponse()->getBody());
    }

    public function testCreateActionProtectedUnusedEnrichmentKey() {
        $ek = Opus_EnrichmentKey::fetchByName(self::$protectedUnusedEnrichmentkey);
        $this->assertNull($ek);

        $config = Zend_Registry::get('Zend_Config');
        $this->assertTrue(in_array(self::$protectedUnusedEnrichmentkey, explode(',', $config->enrichmentkey->protected->migration)));

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'name' => self::$protectedUnusedEnrichmentkey,
                    'submit' => 'submit'
                ));

        $this->dispatch('/admin/enrichmentkey/create');
        $this->assertResponseCode(200);
        $this->assertContains('<ul class="errors">', $this->getResponse()->getBody());

    }

    public function testCreateActionInvalidInput() {
        $this->assertNull(Opus_EnrichmentKey::fetchByName('foo/bar'));

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'name' => 'foo/bar',
                    'submit' => 'submit'
                ));
        $this->dispatch('/admin/enrichmentkey/create');
        $this->assertResponseCode(200);
        $this->assertContains('<ul class="errors">', $this->getResponse()->getBody());
   }


    /**
     * @depends testCreateAction
     */
    public function testUpdateAction() {
        $ek = new Opus_EnrichmentKey();
        $ek->setName('testUpdateAction');
        $ek->store();

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'name' => $ek->getName() . '_updated',
                    'submit' => 'submit'
                ));

        $this->dispatch('/admin/enrichmentkey/update/name/' . $ek->getName());
        $this->assertRedirect();
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/enrichmentkey');

        $this->assertNull(Opus_EnrichmentKey::fetchByName('testUpdateAction'));
        $this->assertNotNull(Opus_EnrichmentKey::fetchByName('testUpdateAction_updated'));

        $enrichmentkey = Opus_EnrichmentKey::fetchByName('testUpdateAction_updated');
        $this->assertEquals('testUpdateAction_updated', $enrichmentkey->getDisplayName());

        $enrichmentkey->delete();
    }



    /**
     * @depends testUpdateAction
     */
    public function testUpdateActionNoInput() {
        $ek = new Opus_EnrichmentKey();
        $ek->setName('testUpdateActionInvalidInput');
        $ek->store();

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'name' => '',
                    'submit' => 'submit'
                ));

        $this->dispatch('/admin/enrichmentkey/update/name/' . $ek->getName());

        $this->assertResponseCode(200);
        $this->assertContains('<ul class="errors">', $this->getResponse()->getBody());

        $ek->delete();
    }

    /**
     * @depends testUpdateAction
     */
    public function testUpdateActionInvalidInput() {
        $ek = new Opus_EnrichmentKey();
        $ek->setName('testUpdateActionInvalidInput');
        $ek->store();

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'name' => $ek->getName() . '-updated',
                    'submit' => 'submit'
                ));

        $this->dispatch('/admin/enrichmentkey/update/name/' . $ek->getName());

        $this->assertResponseCode(200);
        $this->assertContains('<ul class="errors">', $this->getResponse()->getBody());

        $ek->delete();
    }

    public function testUpdateActionWithUsedName() {
        // create two enrichment keys
        $ek_foo = new Opus_EnrichmentKey();
        $ek_foo->setName('testUpdateActionWithUsedName-foo');
        $ek_foo->store();

        $ek_bar = new Opus_EnrichmentKey();
        $ek_bar->setName('testUpdateActionWithUsedName-bar');
        $ek_bar->store();

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'name' => $ek_foo,
                    'submit' => 'submit'
                ));

        $this->dispatch('/admin/enrichmentkey/update/name/' . $ek_bar);
        $this->assertResponseCode(200);
        $this->assertContains('<ul class="errors">', $this->getResponse()->getBody());

        // cleanup
        $ek_foo->delete();
        $ek_bar->delete();
    }

    public function testUpdateActionCaseSensitiveName() {
        $cs_string = 'testUpdateActionCaseSensitiveKey';
        $cs_string_lc = strtolower($cs_string);

        $ek_foo = new Opus_EnrichmentKey();
        $ek_foo->setName($cs_string);
        $ek_foo->store();

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'name' => $cs_string_lc,
                    'submit' => 'submit'
                ));

        $this->dispatch('/admin/enrichmentkey/update/name/' . $cs_string);
        $this->assertRedirect();
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/enrichmentkey');

        $ek_test = Opus_EnrichmentKey::fetchByName($cs_string_lc);
        $this->assertEquals($cs_string_lc, $ek_test->getDisplayName());
        $ek_test->delete();
    }

    public function testUpdateActionProtectedEnrichmentKey() {
        $ek = Opus_EnrichmentKey::fetchByName(self::$protectedEnrichmentkey);
        $this->assertNotNull($ek);
        $config = Zend_Registry::get('Zend_Config');
        $this->assertTrue(in_array($ek->getName(), explode(',', $config->enrichmentkey->protected->modules)));

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'name' => $ek->getName() .'_update',
                    'submit' => 'submit'
                ));

        $this->dispatch('/admin/enrichmentkey/update/name/' . $ek->getName());
        $this->assertResponseCode(200);
        $this->assertContains('<ul class="errors">', $this->getResponse()->getBody());
    }

    /**
     * @depends testUpdateActionInvalidInput
     */
    public function testDeleteAction() {
        $ek = new Opus_EnrichmentKey();
        $ek->setName('testDeleteAction');
        $ek->store();

        $this->dispatch('/admin/enrichmentkey/delete/name/' . $ek->getName());

        $this->assertRedirect();
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/enrichmentkey');

        $this->assertNull(Opus_EnrichmentKey::fetchByName('testDeleteAction'));

        // cleanup is not needed here
    }

    public function testDeleteActionWithMissingNameParam() {
        $this->dispatch('/admin/enrichmentkey/delete/');
        $this->assertRedirect();
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/enrichmentkey');
    }

    public function testDeleteActionWithUnknownNameParam() {
        $this->assertNull(Opus_EnrichmentKey::fetchByName('testDeleteActionWithUnknownNameParam'));
        $this->dispatch('/admin/enrichmentkey/delete/name/testDeleteActionWithUnknownNameParam');
        $this->assertRedirect();
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/enrichmentkey');
    }

    public function testDeleteActionProtectedEnrichmentKey() {
        $ek = Opus_EnrichmentKey::fetchByName(self::$protectedEnrichmentkey);
        $this->assertNotNull($ek);
        $config = Zend_Registry::get('Zend_Config');
        $this->assertTrue(in_array($ek->getName(), explode(',', $config->enrichmentkey->protected->modules)));

        $this->dispatch('/admin/enrichmentkey/delete/name/' . $ek->getName());
        $this->assertRedirect();
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/enrichmentkey');
    }

}
