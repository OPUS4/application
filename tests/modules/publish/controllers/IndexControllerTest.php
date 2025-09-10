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

        $body = $this->getResponse()->getBody();
        $this->assertStringContainsString('<h3 class="document-type">Dokumenttyp und Datei wählen</h3>', $body);
        $this->assertStringContainsString('<legend>Dokument(e) hochladen</legend>', $body);
        $this->assertStringContainsString("<input type='hidden' name='MAX_FILE_SIZE' id='MAX_FILE_SIZE' value='1024000' />", $body);
        $this->assertStringContainsString("<label for='fileupload'>Datei wählen</label>", $body);
        $this->assertStringContainsString("<input type='file' name='fileupload' id='fileupload' enctype='multipart/form-data' title='Bitte wählen Sie eine Datei, die Sie hochladen möchten' size='30' />", $body);
        $this->assertStringContainsString("<label for='uploadComment'>Kommentar</label>", $body);
        $this->assertStringContainsString("<textarea name='uploadComment' class='form-textarea' cols='30' rows='5'  title='Fügen Sie hier einen Kommentar hinzu.'  id='uploadComment'></textarea>", $body);
    }

    public function testDoNotShowFileUpload()
    {
        $config                             = $this->getConfig();
        $config->form->first->enable_upload = self::CONFIG_VALUE_FALSE;

        $this->dispatch('/publish');

        $this->assertResponseCode(200);
        $this->assertController('index');
        $this->assertAction('index');

        $body = $this->getResponse()->getBody();
        $this->assertStringContainsString('<h3 class="document-type">Dokumenttyp wählen</h3>', $body);
        $this->assertStringNotContainsString('<legend>Dokument(e) hochladen</legend>', $body);
        $this->assertStringNotContainsString("<input type='hidden' name='MAX_FILE_SIZE' id='MAX_FILE_SIZE' value='10240000' />", $body);
        $this->assertStringNotContainsString("<label for='fileupload'>Datei wählen</label>", $body);
        $this->assertStringNotContainsString("<input type='file' name='fileupload' id='fileupload' enctype='multipart/form-data' title='Bitte wählen Sie eine Datei, die Sie hochladen möchten ' size='30' />", $body);
        $this->assertStringNotContainsString("<label for='uploadComment'>Kommentar</label>", $body);
        $this->assertStringNotContainsString("<textarea name='uploadComment' class='form-textarea' cols='30' rows='5'  title='Fügen Sie hier einen Kommentar hinzu.'  id='uploadComment'></textarea>", $body);
    }

    public function testShowBibliographyCheckbox()
    {
        $config                             = $this->getConfig();
        $config->form->first->bibliographie = self::CONFIG_VALUE_TRUE;

        $this->dispatch('/publish');

        $this->assertResponseCode(200);
        $this->assertController('index');
        $this->assertAction('index');

        $body = $this->getResponse()->getBody();
        $this->assertStringContainsString('<h3 class="document-type">Dokumenttyp und Datei wählen</h3>', $body);
        $this->assertStringContainsString('<legend>Bibliographie</legend>', $body);
        $this->assertStringContainsString("<input type='checkbox' class='form-checkbox' name='bibliographie' id='bibliographie' value='1'  />", $body);
        $this->assertStringContainsString("<label for='bibliographie'>Zur Bibliographie hinzufügen?</label>", $body);
        $this->assertStringContainsString("<input type='hidden' name='bibliographie' value='0' />", $body);
    }

    public function testDoNotShowBibliographyCheckbox()
    {
        $config                             = $this->getConfig();
        $config->form->first->bibliographie = self::CONFIG_VALUE_FALSE;

        $this->dispatch('/publish');

        $this->assertResponseCode(200);
        $this->assertController('index');
        $this->assertAction('index');

        $body = $this->getResponse()->getBody();
        $this->assertStringContainsString('<h3 class="document-type">Dokumenttyp und Datei wählen</h3>', $body);
        $this->assertStringNotContainsString('<legend>Bibliographie</legend>', $body);
        $this->assertStringNotContainsString("<input type='checkbox' class='form-checkbox' name='bibliographie' id='bibliographie' value='1'  />", $body);
        $this->assertStringNotContainsString("<label for='bibliographie'>Zur Bibliographie hinzufügen?</label>", $body);
        $this->assertStringNotContainsString("<input type='hidden' name='bibliographie' value='0' />", $body);
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

        $doctypeAllPos       = strpos($body, '<option value="all" title="Alle Felder (Testdokumenttyp)">Alle Felder (Testdokumenttyp)</option>');
        $doctypeArticlePos   = strpos($body, '<option value="article" title="Wissenschaftlicher Artikel">Wissenschaftlicher Artikel</option>');
        $doctypeWorkingpaper = strpos($body, '<option value="workingpaper" title="Arbeitspapier">Arbeitspapier</option>');
        $doctypeDemodemo     = strpos($body, '<option value="demodemo" title="demodemo">demodemo</option>');

        $this->assertTrue($doctypeAllPos < $doctypeWorkingpaper);
        $this->assertTrue($doctypeDemodemo < $doctypeArticlePos);
        $this->assertTrue($doctypeWorkingpaper < $doctypeDemodemo);
    }
}
