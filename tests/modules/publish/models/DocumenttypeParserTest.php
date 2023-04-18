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

class Publish_Model_DocumenttypeParserTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['view', 'translation'];

    /** @var Zend_Log */
    protected $logger;

    public function setUp(): void
    {
        $writer       = new Zend_Log_Writer_Null();
        $this->logger = new Zend_Log($writer);
        parent::setUp();
    }

    public function testConstructorWithWrongDom()
    {
        $this->expectException(Application_Exception::class);
        $dom = Zend_Controller_Action_HelperBroker::getStaticHelper('DocumentTypes')->getDocument('irgendwas');

        $model = new Publish_Model_DocumenttypeParser($dom, null);
        $this->assertNull($model->dom);
    }

    public function testConstructorWithCorrectDom()
    {
        $dom   = Zend_Controller_Action_HelperBroker::getStaticHelper('DocumentTypes')->getDocument('preprint');
        $model = new Publish_Model_DocumenttypeParser($dom, null);
        $this->assertInstanceOf('DOMDocument', $model->dom);
    }

    public function testConstructorWithCorrectDomAndWrongForm()
    {
        $session               = new Zend_Session_Namespace('Publish');
        $session->documentType = 'irgendwas';
        $dom                   = Zend_Controller_Action_HelperBroker::getStaticHelper('DocumentTypes')->getDocument('preprint');

        $this->expectException(Application_Exception::class);
        $form = new Publish_Form_PublishingSecond($this->logger);

        $model = new Publish_Model_DocumenttypeParser($dom, $form);
        $this->assertInstanceOf('DOMDocument', $model->dom);
    }

    public function testConstructorWithCorrectDomAndCorrectForm()
    {
        $session               = new Zend_Session_Namespace('Publish');
        $session->documentType = 'preprint';
        $dom                   = Zend_Controller_Action_HelperBroker::getStaticHelper('DocumentTypes')->getDocument('preprint');
        $form                  = new Publish_Form_PublishingSecond($this->logger);
        $model                 = new Publish_Model_DocumenttypeParser($dom, $form);
        $this->assertInstanceOf('DOMDocument', $model->dom);
        $this->assertInstanceOf('Publish_Form_PublishingSecond', $model->form);
    }

    public function testInccorectFieldName()
    {
        $session               = new Zend_Session_Namespace('Publish');
        $session->documentType = 'all';

        /** @var DOMDocument $dom */
        $dom = Zend_Controller_Action_HelperBroker::getStaticHelper('DocumentTypes')->getDocument('all');
        $this->assertInstanceOf('DOMDocument', $dom);

        foreach ($dom->getElementsByTagname('documenttype') as $rootNode) {
            $domElement   = $dom->createElement('field');
            $domAttribute = $dom->createAttribute('name');

            // Value for the created attribute
            $domAttribute->value = 'wrong.name';

            // Don't forget to append it to the element
            $domElement->appendChild($domAttribute);

            // Append it to the document itself
            $rootNode->appendChild($domElement);
            $dom->saveXML();
        }

        $model = new Publish_Model_DocumenttypeParser($dom, new Publish_Form_PublishingSecond($this->logger));

        $this->expectException(Publish_Model_FormIncorrectFieldNameException::class);
        $model->parse();
    }

    public function testIncorrectEnrichmentKey()
    {
        $session               = new Zend_Session_Namespace('Publish');
        $session->documentType = 'all';

        /** @var DOMDocument $dom */
        $dom = Zend_Controller_Action_HelperBroker::getStaticHelper('DocumentTypes')->getDocument('all');
        $this->assertInstanceOf('DOMDocument', $dom);

        foreach ($dom->getElementsByTagname('documenttype') as $rootNode) {
            $domElement = $dom->createElement('field');

            $domAttribute        = $dom->createAttribute('name');
            $domAttribute->value = 'IncorrectEnrichmentKey';

            $domAttribute2        = $dom->createAttribute('datatype');
            $domAttribute2->value = 'Enrichment';

            // Don't forget to append it to the element
            $domElement->appendChild($domAttribute);
            $domElement->appendChild($domAttribute2);

            // Append it to the document itself
            $rootNode->appendChild($domElement);
            $dom->saveXML();
        }

        $model = new Publish_Model_DocumenttypeParser($dom, new Publish_Form_PublishingSecond($this->logger));

        $this->expectException(Publish_Model_FormIncorrectEnrichmentKeyException::class);
        $model->parse();
    }
}
