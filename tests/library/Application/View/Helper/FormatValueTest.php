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
 * @category    Application Unit Tests
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Unit tests for FormatValue view helper.
 */
class Application_View_Helper_FormatValueTest extends ControllerTestCase
{

    protected $additionalResources = ['database', 'view', 'translation'];

    private $__helper;

    public function setUp()
    {
        parent::setUp();
        $this->__helper = new Application_View_Helper_FormatValue();
        $this->__helper->setView(Zend_Registry::get('Opus_View'));
    }

    public function testViewHelperReturnsItself()
    {
        $this->assertEquals($this->__helper, $this->__helper->formatValue());
    }

    public function testFormatValueForNull()
    {
        $ouput = $this->__helper->format(null);

        $this->assertTrue(empty($output));
    }

    public function testFormatValueForString()
    {
        $value = "Test";

        $output = $this->__helper->format($value);

        $this->assertEquals($value, $output);
    }

    public function testFormatValueForSelectField()
    {
        $doc = $this->createTestDocument();

        $field = $doc->getField('Language');

        $field->setValue('deu');

        $output = $this->__helper->format($field, 'Opus_Document');

        $this->assertTrue(in_array($output, ['German', 'Deutsch']));
    }

    public function testFormatValueForYear()
    {
        $doc = $this->createTestDocument();

        $field = $doc->getField('PublishedYear');

        $field->setValue(2010);

        $output = $this->__helper->format($field);

        $this->assertEquals('2010', $output);
    }

    /**
     * This unit test requires the locale to be 'en' because otherwise the date
     * is formatted differently.
     *
     * TODO figure out unit test that checks all locales
     */
    public function testFormatValueForDate()
    {
        $doc = new Opus_Document(3);

        $field = $doc->getField('ThesisDateAccepted');

        $output = $this->__helper->format($field);

        $this->assertTrue(in_array($output, ['2002/06/17', '17.06.2002']));
    }

    public function testFormatValueForInvalidDate()
    {
        $doc = $this->createTestDocument();

        $doc->setPublishedDate(new Opus_Date('2005'));

        $field = $doc->getField('PublishedDate');

        $output = $this->__helper->format($field);

        $this->assertEquals(null, $output);
    }

    public function testFormatValueForPublicationState()
    {
        $doc = new Opus_Document(3);

        $field = $doc->getField('PublicationState');

        $output = $this->__helper->format($field, 'Opus_Document');

        // PublicationState is not translated right now
        $this->assertEquals('draft', $output);
    }
}
