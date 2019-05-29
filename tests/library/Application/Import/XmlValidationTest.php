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
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Application_Import_XmlValidationTest extends ControllerTestCase
{

    /**
     * Check if all 'import*.xml' files are valid.
     */
    public function testValidation()
    {
        foreach (new DirectoryIterator(APPLICATION_PATH . '/tests/resources/import') as $fileInfo) {
            if ($fileInfo->getExtension() !== 'xsd' && !$fileInfo->isDot()
                    && strpos($fileInfo->getBasename(), 'import') === 0) {
                $xml = file_get_contents($fileInfo->getRealPath());
                $this->_checkValid($xml, $fileInfo->getBasename());
            }
        }
    }

    public function testValidation2()
    {
        $xml = file_get_contents(APPLICATION_PATH . '/tests/resources/import/import2.xml');
        $this->_checkValid($xml, 'import2.xml');
    }

    /**
     * TODO Check if all 'invalid-import*.xml' files are invalid.
     */
    public function testInvalid()
    {
        $validator = new Application_Import_XmlValidation();

        $xml = file_get_contents(APPLICATION_PATH . '/tests/resources/import/invalid-import1.xml');

        $this->assertFalse($validator->validate($xml));

        $errors = $validator->getErrors();

        $this->assertCount(1, $errors);
    }

    public function testEnrichmentWithoutValueValid()
    {
        $validator = new Application_Import_XmlValidation();

        $xml = file_get_contents(APPLICATION_PATH . '/tests/resources/import/enrichment-without-value.xml');

        $this->assertTrue($validator->validate($xml));

        $errors = $validator->getErrors();

        $this->assertCount(0, $errors);
    }

    private function _checkValid($xml, $name)
    {
        $validator = new Application_Import_XmlValidation();

        $this->assertTrue($validator->validate($xml), "Import XML file '$name' not valid.");
    }
}
