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
 * @package     Sword
 * @author      Sascha Szott
 * @copyright   Copyright (c) 2016-2018
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 *
 * @covers Sword_ServicedocumentController
 */
class Sword_ServicedocumentControllerTest extends ControllerTestCase {
    
    private $testHelper;

    private $_credentials = 'sworduser:sworduserpwd';
    
    public function setUp() {
        parent::setUp();
        $this->testHelper = new DepositTestHelper();
    }    
    
    public function testIndexActionWithoutPassword() {
        $this->getRequest()->setMethod('GET');
        $this->dispatch('/sword/servicedocument/index');
        $this->assertResponseCode(403);
    }
    
    public function testGetActionWithoutPassword() {
        $this->getRequest()->setMethod('GET');
        $this->dispatch('/sword/servicedocument');
        $this->assertResponseCode(403);        
    }
    
    public function testIndexActionWithWrongPassword() {
        $authString = base64_encode("sworduser:badpassword");
        $this->getRequest()->setHeader('Authorization','Basic ' . $authString);
        $this->getRequest()->setMethod('GET');
        $this->dispatch('/sword/servicedocument/index');
        $this->assertResponseCode(403);
    }    
    
    public function testGetActionWithWrongPassword() {
        $authString = base64_encode("sworduser:badpassword");
        $this->getRequest()->setHeader('Authorization','Basic ' . $authString);
        $this->getRequest()->setMethod('GET');
        $this->dispatch('/sword/servicedocument');
        $this->assertResponseCode(403);
    }        
    
    public function testIndexActionWithValidPassword() {
        $this->testHelper->addImportCollection();
        $authString = base64_encode($this->_credentials);
        $this->getRequest()->setHeader('Authorization','Basic ' . $authString);
        $this->getRequest()->setMethod('GET');
        $this->dispatch('/sword/servicedocument/index');
        $this->testHelper->removeImportCollection();
        $this->checkValidResponse();        
    }
    
    public function testGetActionWithValidPassword() {
        $this->testHelper->addImportCollection();
        $authString = base64_encode($this->_credentials);
        $this->getRequest()->setHeader('Authorization','Basic ' . $authString);
        $this->getRequest()->setMethod('GET');
        $this->dispatch('/sword/servicedocument');
        $this->testHelper->removeImportCollection();
        $this->checkValidResponse();
    }

    public function testHeadAction() {
        $this->getRequest()->setMethod('HEAD');
        $this->dispatch('/sword/servicedocument');
        $this->assertResponseCode(500);
    }
    
    public function testPostAction() {
        $this->getRequest()->setMethod('POST');
        $this->dispatch('/sword/servicedocument');
        $this->assertResponseCode(500);
    }

    public function testPutAction() {
        $this->getRequest()->setMethod('PUT');
        $this->dispatch('/sword/servicedocument');
        $this->assertResponseCode(500);
    }

    public function testDeleteAction() {
        $this->getRequest()->setMethod('DELETE');
        $this->dispatch('/sword/servicedocument');
        $this->assertResponseCode(500);
    }    
    
    private function checkValidResponse() {
        $this->assertResponseCode(200);
        $this->assertModule('sword');
        $this->assertController('servicedocument');
        $this->assertAction('index');
        
        $responseBody = $this->getResponse()->getBody();
        $this->assertNotEmpty($responseBody);
        
        $doc = new DOMDocument();
        $doc->loadXML($responseBody);
        
        $root = $doc->childNodes;
        $this->checkServiceSubtree($root);
    }
    
    private function checkServiceSubtree($root) {
        $this->assertEquals(1, $root->length);        
        $serviceNode = $root->item(0);
        $this->assertEquals('service', $serviceNode->nodeName);
        
        $root = $serviceNode->childNodes;
        $this->assertEquals(6, $root->length);
        
        $this->testHelper->assertNodeProperties(0, $root, 'sword:version', Sword_Model_ServiceDocument::SWORD_VERSION);
        
        $this->testHelper->assertNodeProperties(1, $root, 'sword:level', Sword_Model_ServiceDocument::SWORD_LEVEL);

        $this->testHelper->assertNodeProperties(2, $root, 'sword:verbose', Sword_Model_ServiceDocument::SWORD_SUPPORT_VERBOSE_MODE);        
        
        $this->testHelper->assertNodeProperties(3, $root, 'sword:noOp', Sword_Model_ServiceDocument::SWORD_SUPPORT_NOOP_MODE);

        $maxUploadSize = new Application_Configuration_MaxUploadSize();
        $this->testHelper->assertNodeProperties(4, $root, 'sword:maxUploadSize', $maxUploadSize->getMaxUploadSizeInKB());        
        
        $workspaceNode = $root->item(5);
        $this->testHelper->assertEquals('workspace', $workspaceNode->nodeName);
        
        $this->checkWorkspaceSubtree($workspaceNode->childNodes);
    }
    
    private function checkWorkspaceSubtree($root) {
        $this->assertEquals(2, $root->length);
        
        $config = Zend_Registry::get('Zend_Config');        
        $this->testHelper->assertNodeProperties(0, $root, 'atom:title', $config->name);
        
        $collectionNode = $root->item(1);
        $this->assertEquals('collection', $collectionNode->nodeName);
        $attributes = $collectionNode->attributes;
        $this->assertEquals(1, $attributes->length);
        $attribute = $attributes->item(0);
        $this->assertEquals('href', $attribute->nodeName);
        $this->assertEquals('http:///sword/index/index/Import/' . $this->testHelper->getCollectionNumber(), $attribute->nodeValue);                
        
        $this->checkCollectionSubtree($collectionNode->childNodes);
    }
    
    private function checkCollectionSubtree($root) {
        $this->assertEquals(8, $root->length);
        
        $this->testHelper->assertNodeProperties(0, $root, 'atom:title', $this->testHelper->getCollectionName());
                
        $this->testHelper->assertNodeProperties(1, $root, 'accept', 'application/zip');        
        $this->testHelper->assertNodeProperties(2, $root, 'accept', 'application/tar');        
        
        $this->testHelper->assertNodeProperties(3, $root, 'sword:collectionPolicy', 'sword.collection.default.collectionPolicy');        

        $this->testHelper->assertNodeProperties(4, $root, 'sword:mediation', 'false');        

        $this->testHelper->assertNodeProperties(5, $root, 'sword:treatment', 'sword.collection.default.treatment');        

        $this->checkAcceptPackagingNode($root->item(6));

        $this->testHelper->assertNodeProperties(7, $root, 'dcterms:abstract', 'sword.collection.default.abstract');        
    }
        
    private function checkAcceptPackagingNode($domNode) {
        $this->assertEquals('sword:acceptPackaging', $domNode->nodeName);
        
        $children = $domNode->childNodes;
        $this->assertEquals(1, $children->length);
        $child = $children->item(0);
        $this->assertEquals('sword.collection.default.acceptPackaging', $child->nodeValue);
        
        $attributes = $domNode->attributes;
        $this->assertEquals(1, $attributes->length);
        $attribute = $attributes->item(0);
        $this->assertEquals('q', $attribute->nodeName);
        $this->assertEquals('1.0', $attribute->nodeValue);
    }     

}
