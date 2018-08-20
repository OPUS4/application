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
 * @category    Application Unit Test
 * @package     Form_Validate
 * @author      Maximilian Salomon <salomon@zib.de>
 * @copyright   Copyright (c) 2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

//namespace library\Application\Form\Validate;


class Application_Form_Validate_FilenameTest extends ControllerTestCase
{

    private $config;

    public function setUp()
    {
        parent::setUp();
        $this->config = Application_Configuration::getInstance();
    }

    /**
     * Data provider for valid arguments.
     *
     * @return array Array of invalid arguments.
     */
    public function validDataProvider()
    {
        return [
            ['Test.txt'],
            ['Big_data.pdf'],
            ['Python-Code.pdf'],
            ['Opus4.txt'],
            ['4.7_Handbuch_Opus-4.pdf']
        ];
    }

    /**
     * Data provider for invalid arguments.
     *
     * @return array Array of invalid arguments and a message.
     */
    public function invalidDataProvider()
    {
        return [
            [null, 'Null value not rejected'],
            ['', 'Empty string not rejected'],
            ['_test.txt', 'Malformed string not rejected.'],
            [true, 'Boolean not rejected'],
            ['-Opus4.pdf', 'Malformed string not rejected.'],
            ['Töst.pdf', 'Malformed string not rejected.'],
            ['!Töst.pdf', 'Malformed string not rejected.'],
            ['Töst?.pdf', 'Malformed string not rejected.'],
            ['testtesttesttesttesttesttesttesttesttesttesttesttesttesttest.pdf', 'String too long']
        ];
    }

    public function nullDataProvider()
    {
        return [
            ['Test.txt'],
            ['Big_data.pdf'],
            ['Python-Code.pdf'],
            ['Opus4.txt'],
            ['4.7_Handbuch_Opus-4.pdf'],
            [null, 'Null value not rejected'],
            ['', 'Empty string not rejected'],
            ['_test.txt', 'Malformed string not rejected.'],
            [true, 'Boolean not rejected'],
            ['-Opus4.pdf', 'Malformed string not rejected.'],
            ['Töst.pdf', 'Malformed string not rejected.'],
            ['!Töst.pdf', 'Malformed string not rejected.'],
            ['Töst?.pdf', 'Malformed string not rejected.'],
        ];
    }

    /**
     * Test validation of incorrect arguments.
     *
     * @param mixed $arg Invalid value to check given by the data provider.
     * @param string $msg Error message.
     * @return void
     *
     * @dataProvider invalidDataProvider
     */
    public function testInvalidArguments($arg, $msg)
    {
        $filenameMaxLength = 50;
        $filenameFormat = "^[a-zA-Z0-9][a-zA-Z0-9_.-]+$";
        $filenameOptions = [
            'filenameMaxLength' => $filenameMaxLength,
            'filenameFormat' => $filenameFormat
        ];
        $validator = new Application_Form_Validate_Filename($filenameOptions);
        $this->assertFalse($validator->isValid($arg), $msg);
    }

    /**
     * Test validation of correct arguments.
     *
     * @param mixed $arg Value to check given by the data provider.
     * @return void
     *
     * @dataProvider validDataProvider
     */
    public function testValidArguments($arg)
    {
        $filenameMaxLength = 50;
        $filenameFormat = "^[a-zA-Z0-9][a-zA-Z0-9_.-]+$";
        $filenameOptions = [
            'filenameMaxLength' => $filenameMaxLength,
            'filenameFormat' => $filenameFormat
        ];
        $validator = new Application_Form_Validate_Filename($filenameOptions);

        $result = $validator->isValid($arg);

        $codes = $validator->getErrors();
        $msgs = $validator->getMessages();
        $err = '';
        foreach ($codes as $code) {
            $err .= '(' . $msgs[$code] . ') ';
        }

        $this->assertTrue($result, $arg . ' should pass validation but validator says: ' . $err);
    }

    /**
     * Test validation of zero arguments.
     *
     * @param mixed $arg Value to check given by the data provider.
     * @return void
     *
     * @dataProvider nullDataProvider
     */
    public function testZeroFormat($arg)
    {
        $filenameMaxLength = 50;
        $filenameFormat = null;
        $filenameOptions = [
            'filenameMaxLength' => $filenameMaxLength,
            'filenameFormat' => $filenameFormat
        ];
        $validator = new Application_Form_Validate_Filename($filenameOptions);

        $result = $validator->isValid($arg);

        $codes = $validator->getErrors();
        $msgs = $validator->getMessages();
        $err = '';
        foreach ($codes as $code) {
            $err .= '(' . $msgs[$code] . ') ';
        }

        $this->assertTrue($result, $arg . ' should pass validation but validator says: ' . $err);
    }

    /**
     * Test the logging of an wrong filenameFormat-key
     * @dataProvider nullDataProvider
     */
    public function testInvalidFormatKey($arg)
    {
        //TODO: Change for Log-Trade
        $logger = new MockLogger();
        $this->config->setLogger($logger);

        $filenameMaxLength = 50;
        $filenameFormat = 'se/\ ';
        $filenameOptions = [
            'filenameMaxLength' => $filenameMaxLength,
            'filenameFormat' => $filenameFormat
        ];
        $validator = new Application_Form_Validate_Filename($filenameOptions);

        $messages = $logger->getMessages();
        $this->assertEquals(1, count($messages));
        $this->assertContains('Your regular expression for your filename-validation is not valid.', $messages[0]);

        $result = $validator->isValid($arg);

        $codes = $validator->getErrors();
        $msgs = $validator->getMessages();
        $err = '';
        foreach ($codes as $code) {
            $err .= '(' . $msgs[$code] . ') ';
        }

        $this->assertTrue($result, $arg . ' should pass validation but validator says: ' . $err);
    }
}
