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
 * @category    Tests
 * @author      Maximilian Salomon <salomon@zib.de>
 * @copyright   Copyright (c) 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Unittests for  Class Application_Form_Validate_IdentifierTest
 * @coversDefaultClass Application_Form_Validate_Identifier
 */
class Application_Form_Validate_IdentifierTest extends ControllerTestCase
{
    /**
     * Represents an validator-object for identifier-elements.
     * @var Zend_Validate_Abstract
     */
    private $_validator;

    /**
     * Form element providing type of identifier.
     * @var Zend_Form_Element
     */
    private $_element;

    /**
     * set up variables.
     */
    public function setUp()
    {
        parent::setUp();

        Zend_Registry::get('Zend_Config')->merge(new Zend_Config([
            'identifier' => ['validation' => [
                'isbn' => [
                    'class' => 'Opus_Validate_Isbn'
                ],
                'issn' => [
                    'class' => 'Opus_Validate_Issn'
                ]
            ]]
        ]));

        $this->_element = new Application_Form_Element_Identifier('Element');
        $this->_element->setValue('ISBN');
        $this->_validator = new Application_Form_Validate_Identifier($this->_element);
        $this->useEnglish();
    }

    /**
     * Test for an empty argument in an ISBN-identifier.
     * @covers ::isValid
     */
    public function testIsValidEmpty()
    {
        $this->assertFalse($this->_validator->isValid(''));
    }

    /**
     * Test for an true ISBN.
     * @covers ::isValid
     */
    public function testIsValidTrue()
    {
        $this->assertTrue($this->_validator->isValid('978-3-86680-192-9'));
        $this->assertTrue($this->_validator->isValid('978 3 86680 192 9'));
        $this->assertTrue($this->_validator->isValid('978-0-13235-088-4'));
        $this->assertTrue($this->_validator->isValid('0 13235 088 2'));
        $this->assertTrue($this->_validator->isValid('0-13235-088-2'));
    }

    /**
     * Test for an wrong ISBN-checksum in an ISBN-identifier.
     * @covers ::isValid
     */

    public function testIsValidWrongIsbnChecksum()
    {
        $this->assertFalse($this->_validator->isValid('978-3-86680-192-3'));
        $this->assertFalse($this->_validator->isValid('978-0-13235-088-8'));
    }

    /**
     * Test for an wrong ISBN-form in an ISBN-identifier.
     * @covers ::isValid
     */
    public function testIsValidWrongIsbnForm()
    {
        $this->assertFalse($this->_validator->isValid('978-3-86680-192'));
        $this->assertFalse($this->_validator->isValid('978-3-8668X-192'));
        $this->assertFalse($this->_validator->isValid('978-3-866800-1942-34'));
        $this->assertFalse($this->_validator->isValid('9748-3-866800-1942-34'));
        $this->assertFalse($this->_validator->isValid('978-3-86680-19X-9'));
        $this->assertFalse($this->_validator->isValid('978-378-866800-1942'));
        $this->assertFalse($this->_validator->isValid('978386680192'));
        $this->assertFalse($this->_validator->isValid('978-0 13235 088 4'));
    }

    /**
     * Test for an NULL argument in an ISBN-identifier.
     * @covers ::isValid
     */
    public function testIsValidIsbnNull()
    {
        $this->assertFalse($this->_validator->isValid(null));
    }

    /**
     * Test for an empty argument in an DOI-identifier -> identifier without validation.
     * @covers ::isValid
     */
    public function testIsValidDoiEmpty()
    {
        $this->_element->setValue('DOI');
        $this->_validator = new Application_Form_Validate_Identifier($this->_element);
        $this->assertFalse($this->_validator->isValid(''));
    }

    /**
     * Test for an argument in an DOI-identifier -> identifier without validation.
     * @covers ::isValid
     */
    public function testIsValidDoi()
    {
        $this->_element->setValue('DOI');
        $this->_validator = new Application_Form_Validate_Identifier($this->_element);
        $this->assertTrue($this->_validator->isValid('23356'));
        $this->assertTrue($this->_validator->isValid('233dfsfsf'));
        $this->assertTrue($this->_validator->isValid('23fdt45356'));
        $this->assertTrue($this->_validator->isValid('233_:()$&56'));
        $this->assertTrue($this->_validator->isValid('23!"356'));
    }

    /**
     * Test for null as element.
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Argument must not be NULL
     * @covers ::__construct
     */
    public function testIsValidElementNull()
    {
        $this->_validator = new Application_Form_Validate_Identifier(null);
    }

    /**
     * Test for an NULL argument in an DOI-identifier-> identifier without validation.
     * @covers ::isValid
     */
    public function testIsValidDoiNull()
    {
        $this->_element->setValue('DOI');
        $this->_validator = new Application_Form_Validate_Identifier($this->_element);
        $this->assertFalse($this->_validator->isValid(null));
    }

    /**
     * Test for an unknown type as identifier -> same result as empty in type without validation.
     * @covers ::isValid
     */
    public function testIsValidUnknownType()
    {
        $this->_element->setValue('unknown');
        $this->_validator = new Application_Form_Validate_Identifier($this->_element);
        $this->assertFalse($this->_validator->isValid(''));
    }

    /**
     * Test for an unknown type as identifier -> same result as empty in type without validation.
     * @covers ::isValid
     */
    public function testErrorMessageChecksum()
    {
        $this->assertFalse($this->_validator->isValid('978-3-86680-192-3'));
        $this->assertContains('checkdigit', $this->_validator->getErrors());

        $this->assertFalse($this->_validator->isValid('978-3-86680-192-7'));
        $this->assertContains("The check digit of '978-3-86680-192-7' is not valid.", $this->_validator->getMessages());
    }

    /**
     * Test the error-messages for an invalid ISBN-form.
     * @covers ::isValid
     */
    public function testErrorMessageForm()
    {
        $this->assertFalse($this->_validator->isValid('978-3-866800-1942-34'));
        $this->assertContains('form', $this->_validator->getErrors());
        $this->assertContains("'978-3-866800-1942-34' is malformed.", $this->_validator->getMessages());

        $this->assertFalse($this->_validator->isValid('978386680192'));
        $this->assertContains('form', $this->_validator->getErrors());
        $this->assertContains("'978386680192' is malformed.", $this->_validator->getMessages());

        $this->assertFalse($this->_validator->isValid('978-3-86680-1X2-9'));
        $this->assertContains('form', $this->_validator->getErrors());
        $this->assertContains("'978-3-86680-1X2-9' is malformed.", $this->_validator->getMessages());
    }

    /**
     * Test the error-messages for an text as delivery for the Application_Form_Validate_Identifier-Object.
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Object must be Zend_Form_Element
     * @covers ::__construct
     */
    public function testInvalidConstructorArgument()
    {
        new Application_Form_Validate_Identifier("zhui");
    }

    /**
     * Invalid object type as constructor argument should throw exception.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Object must be Zend_Form_Element
     * @covers ::__construct
     */
    public function testInvalidConsructorArgumentWrongObjectType()
    {
        new Application_Form_Validate_Identifier(new Application_Form_Validate_EmailAddress('Element'));
    }

    /**
     * Tests, if the validators, which are set in the config-file, exists.
     */
    public function testClassesExists()
    {
        $config = Application_Configuration::getInstance()->getConfig();
        $types = $config->identifier->validation->toArray();
        foreach ($types as $key => $val) {
            $this->assertArrayHasKey('class', $val);
            $this->assertTrue(class_exists($val['class']));
        }
    }

    /**
     * Tests, if keys for message-templates, which are set in the config-files, exists in the validator-files.
     */
    public function testMessagesKeyValid()
    {
        $config = Application_Configuration::getInstance()->getConfig();
        $validators = $config->identifier->validation->toArray();

        foreach ($validators as $key => $val) {
            $validatorClass = $val['class'];
            $validator = new $validatorClass;
            $messageValidator = $validator->getMessageTemplates();
            if(array_key_exists('messageTemplates',$val)){
                $messageConfig = $val['messageTemplates'];
                foreach ($messageConfig as $key => $val) {
                    $this->assertArrayHasKey($key, $messageValidator);
                }
            }
        }
    }

    /**
     * Tests if all set message-templates(error-codes) are translated.
     */
    public function testTranslationExists()
    {
        $translate = Zend_Registry::get('Zend_Translate');
        $config = Application_Configuration::getInstance()->getConfig();
        $validators = $config->identifier->validation->toArray();
        foreach ($validators as $key => $val) {
            if(array_key_exists('messageTemplates',$val)) {
                $messageConfig = $val['messageTemplates'];
                foreach ($messageConfig as $key => $val) {
                    $this->assertTrue($translate->isTranslated($val));
                }
            }
        }
    }

}
