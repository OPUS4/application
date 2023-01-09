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

/**
 * @covers Publish_IndexController
 */
class Publish_IndexControllerTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'all';

    public function setUp(): void
    {
        parent::setUp();
        $this->useGerman();
    }

    public function testIndexAction()
    {
        $this->dispatch('/publish');
        $this->assertResponseCode(200);
        $this->assertController('index');
        $this->assertAction('index');
    }

    public function testShowFileUpload()
    {
        $config                             = $this->getConfig();
        $config->form->first->enable_upload = self::CONFIG_VALUE_TRUE;

        $this->dispatch('/publish');

        $this->assertResponseCode(200);
        $this->assertController('index');
        $this->assertAction('index');

        $this->assertContains('<h3 class="document-type">Dokumenttyp und Datei wählen</h3>', $this->getResponse()->getBody());
        $this->assertContains('<legend>Dokument(e) hochladen</legend>', $this->getResponse()->getBody());
        $this->assertContains("<input type='hidden' name='MAX_FILE_SIZE' id='MAX_FILE_SIZE' value='1024000' />", $this->getResponse()->getBody());
        $this->assertContains("<label for='fileupload'>Datei wählen</label>", $this->getResponse()->getBody());
        $this->assertContains("<input type='file' name='fileupload' id='fileupload' enctype='multipart/form-data' title='Bitte wählen Sie eine Datei, die Sie hochladen möchten' size='30' />", $this->getResponse()->getBody());
        $this->assertContains("<label for='uploadComment'>Kommentar</label>", $this->getResponse()->getBody());
        $this->assertContains("<textarea name='uploadComment' class='form-textarea' cols='30' rows='5'  title='Fügen Sie hier einen Kommentar hinzu.'  id='uploadComment'></textarea>", $this->getResponse()->getBody());
    }

    public function testDoNotShowFileUpload()
    {
        $config                             = $this->getConfig();
        $config->form->first->enable_upload = self::CONFIG_VALUE_FALSE;

        $this->dispatch('/publish');

        $this->assertResponseCode(200);
        $this->assertController('index');
        $this->assertAction('index');

        $this->assertContains('<h3 class="document-type">Dokumenttyp wählen</h3>', $this->getResponse()->getBody());
        $this->assertNotContains('<legend>Dokument(e) hochladen</legend>', $this->getResponse()->getBody());
        $this->assertNotContains("<input type='hidden' name='MAX_FILE_SIZE' id='MAX_FILE_SIZE' value='10240000' />", $this->getResponse()->getBody());
        $this->assertNotContains("<label for='fileupload'>Datei wählen</label>", $this->getResponse()->getBody());
        $this->assertNotContains("<input type='file' name='fileupload' id='fileupload' enctype='multipart/form-data' title='Bitte wählen Sie eine Datei, die Sie hochladen möchten ' size='30' />", $this->getResponse()->getBody());
        $this->assertNotContains("<label for='uploadComment'>Kommentar</label>", $this->getResponse()->getBody());
        $this->assertNotContains("<textarea name='uploadComment' class='form-textarea' cols='30' rows='5'  title='Fügen Sie hier einen Kommentar hinzu.'  id='uploadComment'></textarea>", $this->getResponse()->getBody());
    }

    public function testShowBibliographyCheckbox()
    {
        $config                             = $this->getConfig();
        $config->form->first->bibliographie = self::CONFIG_VALUE_TRUE;

        $this->dispatch('/publish');

        $this->assertResponseCode(200);
        $this->assertController('index');
        $this->assertAction('index');

        $this->assertContains('<h3 class="document-type">Dokumenttyp und Datei wählen</h3>', $this->getResponse()->getBody());
        $this->assertContains('<legend>Bibliographie</legend>', $this->getResponse()->getBody());
        $this->assertContains("<input type='checkbox' class='form-checkbox' name='bibliographie' id='bibliographie' value='1'  />", $this->getResponse()->getBody());
        $this->assertContains("<label for='bibliographie'>Zur Bibliographie hinzufügen?</label>", $this->getResponse()->getBody());
        $this->assertContains("<input type='hidden' name='bibliographie' value='0' />", $this->getResponse()->getBody());
    }

    public function testDoNotShowBibliographyCheckbox()
    {
        $config                             = $this->getConfig();
        $config->form->first->bibliographie = self::CONFIG_VALUE_FALSE;

        $this->dispatch('/publish');

        $this->assertResponseCode(200);
        $this->assertController('index');
        $this->assertAction('index');

        $this->assertContains('<h3 class="document-type">Dokumenttyp und Datei wählen</h3>', $this->getResponse()->getBody());
        $this->assertNotContains('<legend>Bibliographie</legend>', $this->getResponse()->getBody());
        $this->assertNotContains("<input type='checkbox' class='form-checkbox' name='bibliographie' id='bibliographie' value='1'  />", $this->getResponse()->getBody());
        $this->assertNotContains("<label for='bibliographie'>Zur Bibliographie hinzufügen?</label>", $this->getResponse()->getBody());
        $this->assertNotContains("<input type='hidden' name='bibliographie' value='0' />", $this->getResponse()->getBody());
    }

    /**
     * Regression Test for OPUSVIER-809
     */
    public function testDocumentTypeSelectBoxIsSortedAlphabetically()
    {
        // manipulate list of available document types in application configuration
        $config                         = $this->getConfig();
        $include                        = $config->documentTypes->include;
        $exclude                        = $config->documentTypes->exclude;
        $config->documentTypes->include = 'all, article, workingpaper, demodemo';
        $config->documentTypes->exclude = '';

        $this->dispatch('/publish');

        $config->documentTypes->include = $include;
        $config->documentTypes->exclude = $exclude;

        $this->assertResponseCode(200);

        $body = $this->getResponse()->getBody();

        $doctypeAllPos       = strpos($body, '<option value="all" label="Alle Felder (Testdokumenttyp)">Alle Felder (Testdokumenttyp)</option>');
        $doctypeArticlePos   = strpos($body, '<option value="article" label="Wissenschaftlicher Artikel">Wissenschaftlicher Artikel</option>');
        $doctypeWorkingpaper = strpos($body, '<option value="workingpaper" label="Arbeitspapier">Arbeitspapier</option>');
        $doctypeDemodemo     = strpos($body, '<option value="demodemo" label="demodemo">demodemo</option>');

        $this->assertTrue($doctypeAllPos < $doctypeWorkingpaper);
        $this->assertTrue($doctypeWorkingpaper < $doctypeArticlePos);
        $this->assertTrue($doctypeArticlePos < $doctypeDemodemo);
    }
}
