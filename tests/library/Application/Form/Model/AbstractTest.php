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

use Opus\Common\Licence;
use Opus\Common\LicenceInterface;

/**
 * Unit Tests fuer abstrakte Basisklasse fuer Model-Formulare.
 */
class Application_Form_Model_AbstractTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'database';

    /** @var Application_Form_Model_Abstract */
    private $form;

    public function setUp(): void
    {
        parent::setUp();
        $this->form = $this->getForm();
    }

    /**
     * @return Application_Form_Model_Abstract
     */
    private function getForm()
    {
        return $this->getMockForAbstractClass(Application_Form_Model_Abstract::class);
    }

    public function testInit()
    {
        $this->assertEquals(3, count($this->form->getElements()));
        $this->assertNotNull($this->form->getElement('Id'));
        $this->assertNotNull($this->form->getElement('Save'));
        $this->assertNotNull($this->form->getElement('Cancel'));

        $this->assertEquals(2, count($this->form->getDecorators()));
        $this->assertNotNull($this->form->getDecorator('FormElements'));
        $this->assertNotNull($this->form->getDecorator('Form'));

        $this->assertNotNull($this->form->getDisplayGroup('actions'));
    }

    public function testProcessPost()
    {
        $this->assertNull($this->form->processPost([], []));
    }

    public function testProcessPostSave()
    {
        $this->assertEquals(
            Application_Form_Model_Abstract::RESULT_SAVE,
            $this->form->processPost(['Save' => 'Speichern'], [])
        );
    }

    public function testProcessPostCancel()
    {
        $this->assertEquals(
            Application_Form_Model_Abstract::RESULT_CANCEL,
            $this->form->processPost(['Cancel' => 'Abbrechen'], [])
        );
    }

    public function testGetModel()
    {
        $this->form->setModelClass(Licence::class);

        $this->form->getElement('Id')->setValue(1);

        $model = $this->form->getModel();

        $this->assertNotNull($model);
        $this->assertInstanceOf(LicenceInterface::class, $model);
        $this->assertEquals(1, $model->getId());
    }

    public function testGetModelNewInstance()
    {
        $this->form->setModelClass(Licence::class);

        $model = $this->form->getModel();

        $this->assertNotNull($model);
        $this->assertInstanceOf(LicenceInterface::class, $model);
        $this->assertNull($model->getId());
    }

    public function testGetModelNoModelClass()
    {
        $this->expectException(Application_Exception::class);
        $this->expectExceptionMessage('Model class has not been set.');
        $this->form->getModel();
    }

    public function testGetModelBadModelId()
    {
        $this->form->setModelClass(Licence::class);
        $this->form->getElement('Id')->setValue('notAnId');

        $this->expectException(Application_Exception::class);
        $this->expectExceptionMessage('Model-ID must be numeric.');
        $this->form->getModel();
    }

    public function testGetModelUnknownModelId()
    {
        $this->form->setModelClass(Licence::class);
        $this->form->getElement('Id')->setValue(1000);

        $this->expectException(Application_Exception::class);
        $this->expectExceptionMessage('Model with ID \'1000\' not found.');
        $this->form->getModel();
    }

    /**
     * @covers Application_Form_Model_Abstract::setModelClass
     * @covers Application_Form_Model_Abstract::getModelClass
     */
    public function testSetGetModelClass()
    {
        $this->form->setModelClass(Licence::class);

        $this->assertEquals(Licence::class, $this->form->getModelClass());

        $this->form->setModelClass(null);

        $this->assertNull($this->form->getModelClass());
    }

    public function testPrepareRenderingAsView()
    {
        $this->form->prepareRenderingAsView();

        $this->assertFalse($this->form->getDecorator('Form'));
        $this->assertNull($this->form->getDisplayGroup('actions'));
    }

    public function testSetGetVerifyModelIdIsNumeric()
    {
        $value = $this->form->getVerifyModelIdIsNumeric();

        $this->assertTrue($value);

        $this->form->setVerifyModelIdIsNumeric(false);

        $value = $this->form->getVerifyModelIdIsNumeric();

        $this->assertFalse($value);
    }

    public function testValidateModelIdValidMustBeNumeric()
    {
        $method = new ReflectionMethod('Application_Form_Model_Abstract', 'validateModelId');
        $method->setAccessible(true);

        $this->assertNull($method->invoke($this->form, '123'));
    }

    public function testValidateModelIdValidNonNumeric()
    {
        $method = new ReflectionMethod('Application_Form_Model_Abstract', 'validateModelId');
        $method->setAccessible(true);

        $this->form->setVerifyModelIdIsNumeric(false);
        $this->assertNull($method->invoke($this->form, 'enrichment'));
    }

    public function testValidateModelIdNotValidNonNumeric()
    {
        $method = new ReflectionMethod('Application_Form_Model_Abstract', 'validateModelId');
        $method->setAccessible(true);

        $this->expectException(Application_Exception::class);
        $this->expectExceptionMessage('Model-ID must be numeric.');
        $method->invoke($this->form, 'enrichment');
    }

    public function testValidateModelIdForNull()
    {
        $method = new ReflectionMethod('Application_Form_Model_Abstract', 'validateModelId');
        $method->setAccessible(true);

        $this->assertNull($method->invoke($this->form, null));

        $this->form->setVerifyModelIdIsNumeric(false);
        $this->assertNull($method->invoke($this->form, null));
    }
}
