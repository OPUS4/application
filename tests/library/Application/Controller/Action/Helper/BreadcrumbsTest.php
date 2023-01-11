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

use Opus\Common\Document;
use Opus\Common\Title;

class Application_Controller_Action_Helper_BreadcrumbsTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['view', 'database', 'navigation'];

    /** @var Zend_Controller_Action_Helper_Abstract */
    private $helper;

    /** @var Zend_View_Helper_Navigation */
    private $navigation;

    public function setUp(): void
    {
        parent::setUp();

        $this->helper     = Zend_Controller_Action_HelperBroker::getStaticHelper('breadcrumbs');
        $this->navigation = $this->getView()->navigation();
        $this->helper->setNavigation($this->navigation);
        $this->helper->setView($this->getView());
    }

    /**
     * @param string $label
     * @return mixed
     */
    private function getPage($label)
    {
        return $this->navigation->findOneBy('label', $label);
    }

    public function testAvailable()
    {
        $this->assertNotNull($this->helper);
        $this->assertInstanceOf('Application_Controller_Action_Helper_Breadcrumbs', $this->helper);
    }

    public function testDirect()
    {
        $this->assertEquals($this->helper, $this->helper->direct());

        $this->helper->setParameters('admin_filemanager_index', ['id' => 146, 'test' => 'true']);

        $page = $this->getPage('admin_filemanager_index');

        $this->assertNotNull($page);
        $this->assertEquals(146, $page->getParam('id'));
        $this->assertEquals('true', $page->getParam('test'));
    }

    public function testSetDocumentBreadcrumb()
    {
        $document = Document::get(146);

        // Seite zuerst holen, da das Label nach dem Aufruf von setDocumentBreadcrumb nicht mehr stimmt
        $page = $this->getPage('admin_document_index');

        $this->helper->setDocumentBreadcrumb($document);

        $this->assertEquals('KOBV', $page->getLabel());
        $this->assertEquals(146, $page->getParam('id'));
    }

    public function testSetParameters()
    {
        $this->helper->setParameters('admin_filemanager_index', ['id' => 146, 'test' => 'true']);

        $page = $this->getPage('admin_filemanager_index');

        $this->assertNotNull($page);
        $this->assertEquals(146, $page->getParam('id'));
        $this->assertEquals('true', $page->getParam('test'));
    }

    public function testSetGetNavigation()
    {
        $navigation = new Zend_Navigation();

        $this->helper->setNavigation($navigation);
        $this->assertEquals($navigation, $this->helper->getNavigation());
    }

    public function testSetDocumentBreadcrumbNoDocument()
    {
        $logger = new MockLogger();

        $this->helper->setLogger($logger);
        $this->helper->setDocumentBreadcrumb(null);

        $messages = $logger->getMessages();

        $this->assertEquals(1, count($messages));
        $this->assertContains('No document provided.', $messages[0]);
    }

    public function testSetParametersPageNotFound()
    {
        $logger = new MockLogger();

        $this->helper->setLogger($logger);
        $this->helper->setParameters('admin_filemanager_index2', []);

        $messages = $logger->getMessages();

        $this->assertEquals(1, count($messages));
        $this->assertContains('Page with label \'admin_filemanager_index2\' not found.', $messages[0]);
    }

    public function testGetDocumentTitle()
    {
        $document = $this->createTestDocument();

        $document->setLanguage('deu');

        $title = Title::new();
        $title->setLanguage('deu');
        $title->setValue('01234567890123456789012345678901234567890123456789'); // 50 Zeichen lang

        $document->addTitleMain($title);

        $title = $this->helper->getDocumentTitle($document);

        $this->assertTrue(ctype_print($title));
        $this->assertEquals('0123456789012345678901234567890123456789 ...', $title);
    }

    public function testGetDocumentTitleWithMultiByteChars()
    {
        $document = $this->createTestDocument();
        $document->setLanguage('deu');

        $title = Title::new();
        $title->setLanguage('deu');
        $title->setValue('012345678901234567890123456789012345678ü123'); // 50 Zeichen lang

        $document->addTitleMain($title);

        $title = $this->helper->getDocumentTitle($document);

        $this->assertTrue(mb_check_encoding($title));
        $this->assertEquals('012345678901234567890123456789012345678ü ...', $title);
    }

    /**
     * Testet die Funktion Application_Controller_Action_Helper_Breadcrumbs::setLabelFor().
     */
    public function testSetLabelFor()
    {
        $page = $this->getPage('admin_doctype_show');
        $this->assertEquals('admin_doctype_show', $page->getLabel());

        $this->helper->setLabelFor('admin_doctype_show', 'hallo');

        $this->assertNotNull($page);
        $this->assertEquals('hallo', $page->getLabel());
    }
}
