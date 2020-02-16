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
 * @package     Admin_Form
 * @author      Jens Schwidder <schwidder@zib.de>
 * @author      Sascha Szott <opus-development@saschaszott.de>
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Unit Tests for Admin_Form_Enrichmentkey.
 *
 * @category Application Unit Test
 * @package Admin_Form
 */
class Admin_Form_EnrichmentKeyTest extends ControllerTestCase
{

    protected $additionalResources = ['database', 'translation'];

    public function testConstructForm()
    {
        $form = new Admin_Form_EnrichmentKey();

        $this->assertEquals(7, count($form->getElements()));

        $this->assertNotNull($form->getElement(Admin_Form_EnrichmentKey::ELEMENT_NAME));
        $this->assertNotNull($form->getElement(Admin_Form_EnrichmentKey::ELEMENT_TYPE));
        $this->assertNotNull($form->getElement(Admin_Form_EnrichmentKey::ELEMENT_OPTIONS));
        $this->assertNotNull($form->getElement(Admin_Form_EnrichmentKey::ELEMENT_VALIDATION));
        $this->assertNotNull($form->getElement(Admin_Form_EnrichmentKey::ELEMENT_SAVE));
        $this->assertNotNull($form->getElement(Admin_Form_EnrichmentKey::ELEMENT_CANCEL));
        $this->assertNotNull($form->getElement(Admin_Form_EnrichmentKey::ELEMENT_MODEL_ID));
    }

    public function testPopulateFromModel()
    {
        $enrichmentKey = new Opus_EnrichmentKey();
        $enrichmentKey->setName('Test');

        $form = new Admin_Form_EnrichmentKey();
        $form->populateFromModel($enrichmentKey);

        $this->assertEquals('Test', $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_NAME)->getValue());
        $this->assertNull($form->getElement(Admin_Form_EnrichmentKey::ELEMENT_TYPE)->getValue());
        $this->assertNull($form->getElement(Admin_Form_EnrichmentKey::ELEMENT_OPTIONS)->getValue());
        $this->assertEquals('0', $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_VALIDATION)->getValue());
    }

    public function testPopulateFromExistingModel()
    {
        $enrichment = new Opus_EnrichmentKey('City');
        $this->assertNotNull($enrichment);

        $form = new Admin_Form_EnrichmentKey();
        $form->populateFromModel($enrichment);

        $this->assertEquals('City', $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_NAME)->getValue());
        $this->assertNull($form->getElement(Admin_Form_EnrichmentKey::ELEMENT_TYPE)->getValue());
        $this->assertNull($form->getElement(Admin_Form_EnrichmentKey::ELEMENT_OPTIONS)->getValue());
        $this->assertEquals('0', $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_VALIDATION)->getValue());
    }

    public function testPopulateFromModelWithEnrichmentType()
    {
        $enrichmentKey = new Opus_EnrichmentKey();
        $enrichmentKey->setName('TestKey');
        $enrichmentKey->setType('TextType');

        $form = new Admin_Form_EnrichmentKey();
        $form->populateFromModel($enrichmentKey);

        $this->assertEquals('TestKey', $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_NAME)->getValue());
        $this->assertEquals('TextType', $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_TYPE)->getValue());
        $this->assertNull($form->getElement(Admin_Form_EnrichmentKey::ELEMENT_OPTIONS)->getValue());
        $this->assertEquals('0', $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_VALIDATION)->getValue());
    }

    public function testPopulateFromModelWithUnknownEnrichmentType()
    {
        $enrichmentKey = new Opus_EnrichmentKey();
        $enrichmentKey->setName('TestKey');
        $enrichmentKey->setType('FooType');

        $form = new Admin_Form_EnrichmentKey();
        $form->populateFromModel($enrichmentKey);

        $this->assertEquals('TestKey', $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_NAME)->getValue());
        $this->assertEquals('FooType', $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_TYPE)->getValue());
        $this->assertNull($form->getElement(Admin_Form_EnrichmentKey::ELEMENT_OPTIONS)->getValue());
        $this->assertEquals('0', $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_VALIDATION)->getValue());
    }

    public function testPopulateFromModelWithEnrichmentTypeAndOptionsAndStrictValidation()
    {
        $enrichmentKey = new Opus_EnrichmentKey();
        $enrichmentKey->setName('TestKey');
        $enrichmentKey->setType('RegexType');
        $enrichmentKey->setOptions(json_encode(['regex' => '^a$', 'validation' => 'strict']));

        $form = new Admin_Form_EnrichmentKey();
        $form->populateFromModel($enrichmentKey);

        $this->assertEquals('TestKey', $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_NAME)->getValue());
        $this->assertEquals('RegexType', $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_TYPE)->getValue());
        $this->assertEquals('^a$', $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_OPTIONS)->getValue());
        $this->assertEquals('1', $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_VALIDATION)->getValue());
    }

    public function testPopulateFromModelWithEnrichmentTypeAndOptionsAndNoValidation()
    {
        $enrichmentKey = new Opus_EnrichmentKey();
        $enrichmentKey->setName('TestKey');
        $enrichmentKey->setType('RegexType');
        $enrichmentKey->setOptions(json_encode(['regex' => '^a$', 'validation' => 'none']));

        $form = new Admin_Form_EnrichmentKey();
        $form->populateFromModel($enrichmentKey);

        $this->assertEquals('TestKey', $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_NAME)->getValue());
        $this->assertEquals('RegexType', $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_TYPE)->getValue());
        $this->assertEquals('^a$', $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_OPTIONS)->getValue());
        $this->assertEquals('0', $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_VALIDATION)->getValue());
    }

    public function testPopulateFromModelWithUnknownEnrichmentTypeAndOptions()
    {
        $enrichmentKey = new Opus_EnrichmentKey();
        $enrichmentKey->setName('TestKey');
        $enrichmentKey->setType('FooType');
        $enrichmentKey->setOptions(json_encode(['regex' => '^a$', 'validation' => 'strict']));

        $form = new Admin_Form_EnrichmentKey();
        $form->populateFromModel($enrichmentKey);

        $this->assertEquals('TestKey', $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_NAME)->getValue());
        $this->assertEquals('FooType', $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_TYPE)->getValue());
        $this->assertNull($form->getElement(Admin_Form_EnrichmentKey::ELEMENT_OPTIONS)->getValue());
        $this->assertEquals('0', $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_VALIDATION)->getValue());
    }

    public function testUpdateModel()
    {
        $form = new Admin_Form_EnrichmentKey();
        $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_NAME)->setValue('TestEnrichmentKey');

        $enrichmentKey = new Opus_EnrichmentKey();
        $form->updateModel($enrichmentKey);

        $this->assertEquals('TestEnrichmentKey', $enrichmentKey->getName());
        $this->assertNull($enrichmentKey->getType());
        $this->assertNull($enrichmentKey->getOptions());
    }

    public function testUpdateModelWithType()
    {
        $form = new Admin_Form_EnrichmentKey();
        $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_NAME)->setValue('TestEnrichmentKey');
        $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_TYPE)->setValue('TextType');

        $enrichmentKey = new Opus_EnrichmentKey();
        $form->updateModel($enrichmentKey);

        $this->assertEquals('TestEnrichmentKey', $enrichmentKey->getName());
        $this->assertEquals('TextType', $enrichmentKey->getType());
        $this->assertNull($enrichmentKey->getOptions());
    }

    public function testUpdateModelWithUnknownType()
    {
        $form = new Admin_Form_EnrichmentKey();
        $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_NAME)->setValue('TestEnrichmentKey');
        $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_TYPE)->setValue('UnknownType');

        $enrichmentKey = new Opus_EnrichmentKey();
        $form->updateModel($enrichmentKey);

        $this->assertEquals('TestEnrichmentKey', $enrichmentKey->getName());
        $this->assertNull($enrichmentKey->getType());
        $this->assertNull($enrichmentKey->getOptions());
    }

    public function testUpdateModelWithTypeAndOptionsAndStrictValidation()
    {
        $form = new Admin_Form_EnrichmentKey();
        $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_NAME)->setValue('TestEnrichmentKey');
        $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_TYPE)->setValue('RegexType');
        $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_OPTIONS)->setValue('^a$');
        $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_VALIDATION)->setValue('1');

        $enrichmentKey = new Opus_EnrichmentKey();
        $form->updateModel($enrichmentKey);

        $this->assertEquals('TestEnrichmentKey', $enrichmentKey->getName());
        $this->assertEquals('RegexType', $enrichmentKey->getType());
        $this->assertEquals(json_encode(['regex' => '^a$', 'validation' => 'strict']), $enrichmentKey->getOptions());
    }

    public function testUpdateModelWithTypeAndOptionsAndNoValidation()
    {
        $form = new Admin_Form_EnrichmentKey();
        $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_NAME)->setValue('TestEnrichmentKey');
        $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_TYPE)->setValue('RegexType');
        $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_OPTIONS)->setValue('^a$');
        $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_VALIDATION)->setValue('0');

        $enrichmentKey = new Opus_EnrichmentKey();
        $form->updateModel($enrichmentKey);

        $this->assertEquals('TestEnrichmentKey', $enrichmentKey->getName());
        $this->assertEquals('RegexType', $enrichmentKey->getType());
        $this->assertEquals(json_encode(['regex' => '^a$', 'validation' => 'none']), $enrichmentKey->getOptions());
    }

    public function testUpdateModelWithUnknownTypeAndOptions()
    {
        $form = new Admin_Form_EnrichmentKey();
        $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_NAME)->setValue('TestEnrichmentKey');
        $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_TYPE)->setValue('UnknownType');
        $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_OPTIONS)->setValue('^a$');
        $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_VALIDATION)->setValue('1');

        $enrichmentKey = new Opus_EnrichmentKey();
        $form->updateModel($enrichmentKey);

        $this->assertEquals('TestEnrichmentKey', $enrichmentKey->getName());
        $this->assertNull($enrichmentKey->getType());
        $this->assertNull($enrichmentKey->getOptions());
    }

    public function testValidationSuccess()
    {
        $form = new Admin_Form_EnrichmentKey();

        $this->assertTrue($form->isValid($this->createArray('City2', 'TextType')));
        $this->assertTrue($form->isValid($this->createArray('Test', 'TextType')));
        $this->assertTrue($form->isValid($this->createArray('Test', 'RegexType', '^a$')));
        $this->assertTrue($form->isValid(
            $this->createArray(
                str_pad('Long', Opus_EnrichmentKey::getFieldMaxLength('Name'), 'g'),
                "TextType"
            )
        ));
        $this->assertTrue($form->isValid($this->createArray('small_value59.dot', 'TextType')));
        $this->assertTrue($form->isValid($this->createArray('Test', 'RegexType')));
    }

    public function testValidationFailure()
    {
        $form = new Admin_Form_EnrichmentKey();

        $this->assertFalse($form->isValid([]));
        $this->assertFalse($form->isValid($this->createArray('City', 'TextType')));
        $this->assertFalse($form->isValid($this->createArray(' ', 'TextType')));
        $this->assertFalse($form->isValid(
            $this->createArray(
                str_pad('toolong', Opus_EnrichmentKey::getFieldMaxLength('Name') + 1, 'g'),
                "TextType"
            )
        ));
        $this->assertFalse($form->isValid($this->createArray('5zig', 'TextType')));
        $this->assertFalse($form->isValid($this->createArray('_Value', 'TextType')));

        // missing enrichment type (valid when adding new keys)
        $this->assertTrue($form->isValid($this->createArray('FooBarKey')));

        // empty enrichment type (valid when adding new keys)
        $this->assertTrue($form->isValid($this->createArray('FooBarKey', '')));

        // unknown enrichment type
        $this->assertFalse($form->isValid($this->createArray('FooBarKey', 'FooBarType')));
    }

    public function testSetNameElementValue()
    {
        $form = new Admin_Form_EnrichmentKey();
        $form->populateFromModel(new Opus_EnrichmentKey());
        $form->setNameElementValue('foo');

        $this->assertEquals('foo', $form->getElement(Admin_Form_EnrichmentKey::ELEMENT_NAME)->getValue());

        $form->populateFromModel(new Opus_EnrichmentKey());
        $this->assertNull($form->getElement(Admin_Form_EnrichmentKey::ELEMENT_NAME)->getValue());
    }

    /**
     * Hat ein existierender Enrichment Key bereits einen zugeordneten Enrichment Type,
     * so kann dieser nicht mehr gelöscht, sondern nur auf einen anderen Typ geändert werden.
     *
     * @throws Zend_Form_Exception
     * @throws \Opus\Model\Exception
     */
    public function testTypeIsRequiredForExistingTypedKey()
    {
        $enrichmentKey = new Opus_EnrichmentKey();
        $enrichmentKey->setName('TestKey');
        $enrichmentKey->setType('BooleanType');

        $form = new Admin_Form_EnrichmentKey();
        $form->populateFromModel($enrichmentKey);

        // missing enrichment type (is required)
        $this->assertFalse($form->isValid($this->createArray('TestKey')));

        // empty enrichment type (is required)
        $this->assertFalse($form->isValid($this->createArray('TestKey', '')));
    }

    /**
     * Hat ein existierender Enrichment Key keinen zugeordneten Enrichment Type,
     * so muss dieser beim erneuten Speichern des Enrichment Keys auch nicht gesetzt werden.
     *
     * @throws Zend_Form_Exception
     * @throws \Opus\Model\Exception
     */
    public function testTypeIsRequiredForExistingUntypedKey()
    {
        $enrichmentKey = new Opus_EnrichmentKey();
        $enrichmentKey->setName('TestKey');

        $form = new Admin_Form_EnrichmentKey();
        $form->populateFromModel($enrichmentKey);

        // missing enrichment type (is NOT required)
        $this->assertTrue($form->isValid($this->createArray('TestKey')));

        // empty enrichment type (is NOT required)
        $this->assertTrue($form->isValid($this->createArray('TestKey', '')));
    }

    private function createArray($name, $type = null, $options = null)
    {
        $result = [Admin_Form_EnrichmentKey::ELEMENT_NAME => $name];
        if (! is_null($type)) {
            $result[Admin_Form_EnrichmentKey::ELEMENT_TYPE] = $type;
        }
        if (! is_null($options)) {
            $result[Admin_Form_EnrichmentKey::ELEMENT_OPTIONS] = $options;
        }
        return $result;
    }
}
