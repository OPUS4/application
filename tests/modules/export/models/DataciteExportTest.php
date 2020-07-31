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
 * @package     Module_Export
 * @author      Sascha Szott <opus-development@saschaszott.de>
 * @copyright   Copyright (c) 2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */
class Export_Model_DataciteExportTest extends ControllerTestCase
{

    protected $additionalResources = ['database'];

    public function testExecuteWithMissingDocId()
    {
        $plugin = new Export_Model_DataciteExport();
        $plugin->setRequest($this->getRequest());
        $plugin->setResponse($this->getResponse());

        $this->setExpectedException('Application_Exception');
        $plugin->execute();
    }

    public function testExecuteWithUnknownDocId()
    {
        $plugin = new Export_Model_DataciteExport();
        $request = $this->getRequest();
        $request->setParam('docId', -1);
        $plugin->setRequest($request);
        $plugin->setResponse($this->getResponse());

        $this->setExpectedException('Application_Exception');
        $plugin->execute();
    }

    public function testExecuteWithValidDoc()
    {
        // DOI Präfix setzen
        $oldConfig = Zend_Registry::get('Zend_Config');

        Zend_Registry::set('Zend_Config', Zend_Registry::get('Zend_Config')->merge(
            new Zend_Config([
                'doi' => [
                    'prefix' => '10.2345',
                    'localPrefix' => 'opustest'
                ]
            ])
        ));

        // Testdokument mit allen Pflichtfeldern anlegen
        $doc = new Opus_Document();
        $doc->setType('all');
        $doc->setServerState('published');
        $doc->setPublisherName('Foo Publishing Corp.');
        $doc->setLanguage('deu');
        $docId = $doc->store();

        $doi = new Opus_Identifier();
        $doi->setType('doi');
        $doi->setValue('10.2345/opustest-' . $docId);
        $doc->setIdentifier([$doi]);

        $author = new Opus_Person();
        $author->setFirstName('John');
        $author->setLastName('Doe');
        $doc->setPersonAuthor([$author]);

        $title = new Opus_Title();
        $title->setValue('Meaningless title');
        $title->setLanguage('deu');
        $doc->setTitleMain([$title]);

        $doc->store();

        $plugin = new Export_Model_DataciteExport();
        $request = $this->getRequest();
        $request->setParam('docId', $docId);
        $plugin->setRequest($request);
        $plugin->setResponse($this->getResponse());

        $result = $plugin->execute();

        // Testdokument wieder löschen
        $doc->deletePermanent();
        // Änderungen an Konfiguration zurücksetzen
        Zend_Registry::set('Zend_Config', $oldConfig);

        $this->assertTrue($result);
        $this->assertHeaderContains('Content-Type', 'text/xml; charset=UTF-8');
    }

    public function testExecuteWithInvalidDoc()
    {
        // Testdokument mit fehlenden Pflichtfeldern anlegen
        $doc = new Opus_Document();
        $doc->setServerState('published');
        $docId = $doc->store();

        $plugin = new Export_Model_DataciteExport();
        $request = $this->getRequest();
        $request->setParam('docId', $docId);
        $plugin->setRequest($request);
        $plugin->setResponse($this->getResponse());
        $view = new Zend_View();
        $plugin->setView($view);

        $result = $plugin->execute();

        // Testdokument wieder löschen
        $doc->deletePermanent();

        $this->assertFalse($result);
        $this->assertTrue(is_array($view->requiredFieldsStatus));
        $this->assertTrue(is_array($view->errors));
    }

    public function testExecuteWithInvalidDocAndInvalidValidateParamValue()
    {
        // Testdokument mit fehlenden Pflichtfeldern anlegen
        $doc = new Opus_Document();
        $doc->setServerState('published');
        $docId = $doc->store();

        $plugin = new Export_Model_DataciteExport();
        $request = $this->getRequest();
        $request->setParam('docId', $docId);
        $request->setParam('validate', 'false');
        $plugin->setRequest($request);
        $plugin->setResponse($this->getResponse());

        $view = new Zend_View();
        $plugin->setView($view);

        $result = $plugin->execute();

        // Testdokument wieder löschen
        $doc->deletePermanent();

        $this->assertFalse($result);
        $this->assertTrue(is_array($view->requiredFieldsStatus));
        $this->assertTrue(is_array($view->errors));
    }

    public function testExecuteWithInvalidDocSkipValidation()
    {
        $doc = new Opus_Document();
        $doc->setServerState('published');
        $docId = $doc->store();

        $plugin = new Export_Model_DataciteExport();
        $request = $this->getRequest();
        $request->setParam('docId', $docId);
        $request->setParam('validate', 'no');
        $plugin->setRequest($request);
        $plugin->setResponse($this->getResponse());

        $result = $plugin->execute();

        // Testdokument wieder löschen
        $doc->deletePermanent();

        // XML wird in jedem Fall generiert, auch wenn das DataCite-XML nicht valide ist
        $this->assertTrue($result);
        $this->assertHeaderContains('Content-Type', 'text/xml; charset=UTF-8');
    }
}
