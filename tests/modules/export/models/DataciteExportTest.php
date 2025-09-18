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
 * @copyright   Copyright (c) 2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Document;
use Opus\Common\Identifier;
use Opus\Common\Person;
use Opus\Common\Title;

class Export_Model_DataciteExportTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database'];

    public function testExecuteWithMissingDocId()
    {
        $plugin = new Export_Model_DataciteExport();
        $plugin->setRequest($this->getRequest());
        $plugin->setResponse($this->getResponse());

        $this->expectException(Application_Exception::class);
        $plugin->execute();
    }

    public function testExecuteWithUnknownDocId()
    {
        $plugin  = new Export_Model_DataciteExport();
        $request = $this->getRequest();
        $request->setParam('docId', -1);
        $plugin->setRequest($request);
        $plugin->setResponse($this->getResponse());

        $this->expectException(Application_Exception::class);
        $plugin->execute();
    }

    public function testExecuteWithValidDoc()
    {
        $this->adjustConfiguration([
            'doi' => [
                'prefix'      => '10.2345',
                'localPrefix' => 'opustest',
            ],
        ]);

        // Testdokument mit allen Pflichtfeldern anlegen
        $doc = Document::new();
        $doc->setType('all');
        $doc->setServerState('published');
        $doc->setPublisherName('Foo Publishing Corp.');
        $doc->setLanguage('deu');
        $docId = $doc->store();

        $doi = Identifier::new();
        $doi->setType('doi');
        $doi->setValue('10.2345/opustest-' . $docId);
        $doc->setIdentifier([$doi]);

        $author = Person::new();
        $author->setFirstName('John');
        $author->setLastName('Doe');
        $doc->setPersonAuthor([$author]);

        $title = Title::new();
        $title->setValue('Meaningless title');
        $title->setLanguage('deu');
        $doc->setTitleMain([$title]);

        $doc->store();

        $plugin  = new Export_Model_DataciteExport();
        $request = $this->getRequest();
        $request->setParam('docId', $docId);
        $plugin->setRequest($request);
        $plugin->setResponse($this->getResponse());

        $result = $plugin->execute();

        // Testdokument wieder löschen
        $doc->delete();

        $this->assertEquals(0, $result);
        $this->assertHeaderContains('Content-Type', 'text/xml; charset=UTF-8');
    }

    public function testExecuteWithInvalidDoc()
    {
        // Testdokument mit fehlenden Pflichtfeldern anlegen
        $doc = Document::new();
        $doc->setServerState('published');
        $docId = $doc->store();

        $plugin  = new Export_Model_DataciteExport();
        $request = $this->getRequest();
        $request->setParam('docId', $docId);
        $plugin->setRequest($request);
        $plugin->setResponse($this->getResponse());
        $view = new Zend_View();
        $plugin->setView($view);

        $result = $plugin->execute();

        // Testdokument wieder löschen
        $doc->delete();

        $this->assertGreaterThan(0, $result);
        $this->assertTrue(is_array($view->requiredFieldsStatus));
        $this->assertTrue(is_array($view->errors));
    }

    public function testExecuteWithInvalidDocAndInvalidValidateParamValue()
    {
        // Testdokument mit fehlenden Pflichtfeldern anlegen
        $doc = Document::new();
        $doc->setServerState('published');
        $docId = $doc->store();

        $plugin  = new Export_Model_DataciteExport();
        $request = $this->getRequest();
        $request->setParam('docId', $docId);
        $request->setParam('validate', 'false');
        $plugin->setRequest($request);
        $plugin->setResponse($this->getResponse());

        $view = new Zend_View();
        $plugin->setView($view);

        $result = $plugin->execute();

        // Testdokument wieder löschen
        $doc->delete();

        $this->assertGreaterThan(0, $result);
        $this->assertTrue(is_array($view->requiredFieldsStatus));
        $this->assertTrue(is_array($view->errors));
    }

    public function testExecuteWithInvalidDocSkipValidation()
    {
        $doc = Document::new();
        $doc->setServerState('published');
        $docId = $doc->store();

        $plugin  = new Export_Model_DataciteExport();
        $request = $this->getRequest();
        $request->setParam('docId', $docId);
        $request->setParam('validate', 'no');
        $plugin->setRequest($request);
        $plugin->setResponse($this->getResponse());

        $result = $plugin->execute();

        // Testdokument wieder löschen
        $doc->delete();

        // XML wird in jedem Fall generiert, auch wenn das DataCite-XML nicht valide ist
        $this->assertEquals(0, $result);
        $this->assertHeaderContains('Content-Type', 'text/xml; charset=UTF-8');
    }
}
