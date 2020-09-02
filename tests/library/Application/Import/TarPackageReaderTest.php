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
 * @category    Application Unit Tests
 * @package     Application
 * @author      Sascha Szott <opus-development@saschaszott.de>
 * @copyright   Copyright (c) 2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Application_Import_TarPackageReaderTest extends ControllerTestCase
{

    protected $additionalResources = 'database';

    public function setUp()
    {
        parent::setUp();
        $this->makeConfigurationModifiable();
    }

    public function testReadPackageWithXmlFile()
    {
        Zend_Registry::get('Zend_Config')->merge(new Zend_Config([
            'filetypes' => ['xml' => ['mimeType' => [
                'text/xml', 'application/xml'
            ]]]
        ]));

        $reader = new Application_Import_TarPackageReader();

        $tmpDir = APPLICATION_PATH . '/tests/workspace/tmp/TarPackageReaderTest_ReadPackageWithXmlFile';
        mkdir($tmpDir);

        copy(
            APPLICATION_PATH . '/tests/resources/sword-packages/single-doc-pdf-xml.tar',
            $tmpDir . DIRECTORY_SEPARATOR . 'package.tar'
        );

        $status = $reader->readPackage($tmpDir);

        $this->assertFalse($status->noDocImported());
        $this->assertCount(1, $status->getDocs());

        $document = $status->getDocs()[0];

        $this->addTestDocument($document); // for cleanup

        $files = $document->getFile();

        $this->assertCount(2, $files);

        Application_Import_PackageReaderTest::cleanupTmpDir($tmpDir);
    }
}
