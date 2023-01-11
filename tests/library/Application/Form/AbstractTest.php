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
 * Unit Tests für abstrakte Basisklasse für alle OPUS Formulare.
 */
class Application_Form_AbstractTest extends ControllerTestCase
{
    /** @var Application_Form_Abstract */
    private $form;

    public function setUp(): void
    {
        parent::setUp();

        $this->form = $this->getForm();
    }

    /**
     * @return Application_Form_Abstract
     */
    private function getForm()
    {
        return $this->getMockForAbstractClass(Application_Form_Abstract::class);
    }

    public function testInit()
    {
        $this->form->init();

        $paths = $this->form->getPluginLoader(Zend_Form::DECORATOR)->getPaths();
        $this->assertArrayHasKey('Application_Form_Decorator_', $paths);
        $this->assertContains('Application/Form/Decorator/', $paths['Application_Form_Decorator_']);

        $paths = $this->form->getPluginLoader(Zend_Form::ELEMENT)->getPaths();
        $this->assertArrayHasKey('Application_Form_Element_', $paths);
        $this->assertContains('Application/Form/Element/', $paths['Application_Form_Element_']);
    }

    public function testSetLogger()
    {
        $logger = new MockLogger();

        $this->form->setLogger($logger);

        $this->assertEquals($logger, $this->form->getLogger());
    }

    public function testGetLogger()
    {
        $this->assertNotNull($this->form->getLogger());
        $this->assertInstanceOf(Zend_Log::class, $this->form->getLogger());
    }

    public function testGetElementValue()
    {
        $form = $this->form;

        $elementText = new Zend_Form_Element_Text('text');
        $form->addElement($elementText);

        $elementText->setValue('Test Test');
        $this->assertEquals('Test Test', $form->getElementValue('text'));

        $elementText->setValue('  ');
        $this->assertNull($form->getElementValue('text'));

        $elementText->setValue('0');
        $this->assertEquals('0', $form->getElementValue('text'));

        $elementCheckbox = new Zend_Form_Element_Checkbox('checkbox');
        $form->addElement($elementCheckbox);

        $elementCheckbox->setChecked(true);
        $this->assertEquals('1', $form->getElementValue('checkbox'));

        $elementCheckbox->setChecked(false);
        $this->assertEquals('0', $form->getElementValue('checkbox'));
    }

    public function testGetElementValueUnknownElement()
    {
        $logger = new MockLogger();
        $this->form->setLogger($logger);
        $this->form->setName('Abstract');

        $this->assertNull($this->form->getElementValue('unknownelement'));

        $messages = $logger->getMessages();

        $this->assertEquals(1, count($messages));
        $this->assertEquals('Element \'unknownelement\' in form \'Abstract\' not found.', $messages[0]);
    }

    public function testAddElement()
    {
        $this->form->addElement('text', 'test', ['label' => 'TestLabel']);

        $this->assertNotNull($this->form->getElement('test'));
        $this->assertEquals('TestLabel', $this->form->getElement('test')->getLabel());
    }

    public function testAddElementAutomaticLabelOn()
    {
        $this->form->setUseNameAsLabel(true);
        $this->form->addElement('text', 'textelement');
        $this->assertNotNull($this->form->getElement('textelement'));
        $this->assertEquals('textelement', $this->form->getElement('textelement')->getLabel());
    }

    public function testAddElementAutomaticLabelOnWithCustomLabel()
    {
        $this->form->setUseNameAsLabel(true);
        $this->form->addElement('text', 'textelement', ['label' => 'customlabel']);
        $this->assertNotNull($this->form->getElement('textelement'));
        $this->assertEquals('customlabel', $this->form->getElement('textelement')->getLabel());
    }

    public function testAddElementAutomaticLabelOnWithPrefix()
    {
        $this->form->setUseNameAsLabel(true);
        $this->form->setLabelPrefix('Opus_Model_');
        $this->form->addElement('text', 'textelement');
        $this->assertNotNull($this->form->getElement('textelement'));
        $this->assertEquals('Opus_Model_textelement', $this->form->getElement('textelement')->getLabel());
    }

    public function testAddElementAutomaticLabelOff()
    {
        $this->form->setUseNameAsLabel(false);
        $this->form->addElement('text', 'textelement');
        $this->assertNotNull($this->form->getElement('textelement'));
        $this->assertNull($this->form->getElement('textelement')->getLabel());
    }

    public function testAddElementRequiredMessage()
    {
        $this->form->addElement('text', 'test', ['required' => true]);

        $element = $this->form->getElement('test');

        $this->assertTrue($element->isRequired());
        $this->assertNotNull($element->getValidator('notEmpty'));

        $messages = $element->getValidator('notEmpty')->getMessageTemplates();

        $this->assertArrayHasKey('isEmpty', $messages);
        $this->assertEquals('admin_validate_error_notempty', $messages['isEmpty']);
        $this->assertArrayHasKey('notEmptyInvalid', $messages);
        $this->assertEquals('admin_validate_error_notempty', $messages['notEmptyInvalid']);
    }

    public function testAddElementNotRequired()
    {
        $this->form->addElement('text', 'test');

        $element = $this->form->getElement('test');

        $this->assertFalse($element->isRequired());
        $this->assertFalse($element->getValidator('notEmpty'));
    }

    /**
     * Wenn das Formularelement bereits mit einem notEmpty Validator erzeugt wird, soll dessen Konfiguration nicht
     * mehr überschrieben werden.
     */
    public function testAddElementRequiredExistingValidatorMessages()
    {
        $this->form->addElement('text', 'test', [
            'required'   => true,
            'validators' => ['notEmpty'],
        ]);

        $element = $this->form->getElement('test');

        $this->assertNotNull($element);
        $this->assertTrue($element->isRequired());
        $this->assertNotFalse($element->getValidator('notEmpty'));

        $messages = $element->getValidator('notEmpty')->getMessageTemplates();

        $this->assertArrayHasKey('isEmpty', $messages);
        $this->assertNotEquals('admin_validate_error_notempty', $messages['isEmpty']);
        $this->assertArrayHasKey('notEmptyInvalid', $messages);
        $this->assertNotEquals('admin_validate_error_notempty', $messages['notEmptyInvalid']);
    }

    public function testAddElementRequiredAutoAddingDisabled()
    {
        $this->form->addElement('text', 'test', ['required' => true, 'autoInsertNotEmptyValidator' => false]);

        $element = $this->form->getElement('test');

        $this->assertNotNull($element);
        $this->assertTrue($element->isRequired());
        $this->assertFalse($element->getValidator('notEmpty'));
    }

    /**
     * @covers Application_Form_Abstract::isUseNameAsLabel
     * @covers Application_Form_Abstract::setUseNameAsLabel
     */
    public function testUseNameAsLabelSetting()
    {
        $this->form->setUseNameAsLabel(true);

        $this->assertTrue($this->form->isUseNameAsLabel());

        $this->form->setUseNameAsLabel(false);

        $this->assertFalse($this->form->isUseNameAsLabel());
    }

    public function testLabelPrefixSetting()
    {
        $this->assertNull($this->form->getLabelPrefix());
        $this->form->setLabelPrefix('Opus_File_');
        $this->assertEquals('Opus_File_', $this->form->getLabelPrefix());
    }

    public function testGetApplicationConfig()
    {
        $config = $this->form->getApplicationConfig();

        $this->assertNotNull($config);
        $this->assertInstanceOf(Zend_Config::class, $config);
        $this->assertSame($config, $this->getConfig());
    }

    public function testSetApplicationConfig()
    {
        $config = new Zend_Config(['test' => true]);

        $this->form->setApplicationConfig($config);

        $returnedConfig = $this->form->getApplicationConfig();

        $this->assertSame($config, $returnedConfig);

        $this->form->setApplicationConfig(null);

        $returnedConfig = $this->form->getApplicationConfig();

        $this->assertSame($this->getConfig(), $returnedConfig);
    }
}
