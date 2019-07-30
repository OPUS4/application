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
 * @category    Application Unit Test
 * @package     Form_Validate
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Unit Test for class Application_Form_Validate_Date.
 *
 * TODO TRANSLATION TestCase
 */
class Application_Form_Validate_DateTest extends ControllerTestCase
{

    protected $additionalResources = 'translation';

    const DE_FORMAT = 'dd.MM.yyyy';

    const DE_PATTERN = '#^[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,4}$#';

    const EN_FORMAT = 'yyyy/MM/dd';

    const EN_PATTERN = '#^[0-9]{1,4}/[0-9]{1,2}/[0-9]{1,2}$#';

    private $__validator;

    public function setUp()
    {
        parent::setUp();

        $this->__validator = new Application_Form_Validate_Date();
    }

    public function useGerman()
    {
        parent::useGerman();
        $this->__validator->setLocale('de');
    }

    public function useEnglish()
    {
        parent::useEnglish();
        $this->__validator->setLocale('en');
    }

    public function testGetDateFormatGerman()
    {
        $this->useGerman();
        $this->assertEquals(self::DE_FORMAT, $this->__validator->getDateFormat());
    }

    public function testGetDateFormatEnglish()
    {
        $this->useEnglish();
        $this->assertEquals(self::EN_FORMAT, $this->__validator->getDateFormat());
    }

    public function testGetDateFormatDe()
    {
        $this->assertEquals(self::DE_FORMAT, $this->__validator->getDateFormat('de'));
    }

    public function testGetDateFormatEn()
    {
        $this->assertEquals(self::EN_FORMAT, $this->__validator->getDateFormat('en'));
    }

    public function testIsValidForEmptyString()
    {
        $this->assertFalse($this->__validator->isValid(''));
    }

    public function testIsValidForEmptyStringWithWhitespaces()
    {
        $this->assertFalse($this->__validator->isValid('     '));
    }

    public function testIsValidForNull()
    {
        $this->assertFalse($this->__validator->isValid(null));
    }

    public function testIsValidFor2000()
    {
        $this->assertFalse($this->__validator->isValid('2000'));
    }

    public function testIsValidFor2001German()
    {
        $this->useGerman();
        $this->assertFalse($this->__validator->isValid('2001'));
    }

    public function testIsValidFor2001English()
    {
        $this->useEnglish();
        $this->assertFalse($this->__validator->isValid('2001'));
    }

    public function testIsValidFor2011German()
    {
        $this->useGerman();
        $this->assertFalse($this->__validator->isValid('2011'));
    }

    public function testIsValidFor2011English()
    {
        $this->useEnglish();
        $this->assertFalse($this->__validator->isValid('2011'));
    }

    public function testIsValidForValidDate1German()
    {
        $this->useGerman();
        $this->assertTrue($this->__validator->isValid('12.5.1999'));
    }

    public function testIsValidForValidDate1English()
    {
        $this->useEnglish();
        $this->assertTrue($this->__validator->isValid('1999/05/15'));
    }

    public function testIsValidForValidDate2German()
    {
        $this->useGerman();
        $this->assertTrue($this->__validator->isValid('12.5.99'));
    }

    public function testIsValidForValidDate2English()
    {
        $this->useEnglish();
        $this->assertTrue($this->__validator->isValid('99/05/15'));
    }

    public function testIsValidForValidDate3German()
    {
        $this->useGerman();
        $this->assertTrue($this->__validator->isValid('1.1.1'));
    }

    public function testIsValidForValidDate3English()
    {
        $this->useEnglish();
        $this->assertTrue($this->__validator->isValid('1/1/1'));
    }

    public function testIsValidForYear10000German()
    {
        $this->useGerman();
        $this->assertFalse($this->__validator->isValid('1.1.10000'));
    }

    public function testIsValidForYear10000English()
    {
        $this->useEnglish();
        $this->assertFalse($this->__validator->isValid('10000/1/1'));
    }

    public function testIsValidForInvalidInputGerman()
    {
        $this->useGerman();
        $this->assertFalse($this->__validator->isValid('1. Jan 2002'));
    }

    public function testIsValidForInvalidInputEnglish()
    {
        $this->useEnglish();
        $this->assertFalse($this->__validator->isValid('Feb, 1. 2002'));
    }

    public function testIsValidForInvalidDateGerman()
    {
        $this->useGerman();
        $this->assertFalse($this->__validator->isValid('29.2.2001'));
    }

    public function testIsValidForInvalidDateEnglish()
    {
        $this->useEnglish();
        $this->assertFalse($this->__validator->isValid('2001/02/29'));
    }

    public function testConstructGerman()
    {
        $this->useGerman();
        $validator = new Application_Form_Validate_Date();
        $this->assertEquals(self::DE_FORMAT, $validator->getFormat());
    }

    public function testConstructEnglish()
    {
        $this->useEnglish();
        $validator = new Application_Form_Validate_Date();
        $this->assertEquals(self::EN_FORMAT, $validator->getFormat());
    }

    public function testGetInputPatternGerman()
    {
        $this->useGerman();
        $validator = new Application_Form_Validate_Date();
        $this->assertEquals(self::DE_PATTERN, $validator->getInputPattern());
    }

    public function testGetInputPatternEnglish()
    {
        $this->useEnglish();
        $validator = new Application_Form_Validate_Date();
        $this->assertEquals(self::EN_PATTERN, $validator->getInputPattern());
    }

    public function testSetGetInputPattern()
    {
        $this->useEnglish();
        $validator = new Application_Form_Validate_Date();
        $validator->setInputPattern('#^[0-9]{1.4}$#');
        $this->assertEquals('#^[0-9]{1.4}$#', $validator->getInputPattern());
        $validator->setInputPattern(null);
        $this->assertEquals(self::EN_PATTERN, $validator->getInputPattern());
    }

    public function testSetLocale()
    {
        $validator = new Application_Form_Validate_Date();
        $validator->setLocale('de');
        $this->assertEquals(self::DE_FORMAT, $validator->getFormat());
        $this->assertEquals(self::DE_PATTERN, $validator->getInputPattern());
        $validator->setLocale('en');
        $this->assertEquals(self::EN_FORMAT, $validator->getFormat());
        $this->assertEquals(self::EN_PATTERN, $validator->getInputPattern());
    }
}
