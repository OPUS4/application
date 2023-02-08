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

class Application_Form_Model_TableTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'database';

    public function testConstructForm()
    {
        $form = new Application_Form_Model_Table();

        $this->assertEquals(2, count($form->getDecorators()));
    }

    public function testGetColumnLabel()
    {
        $form = new Application_Form_Model_Table();

        $form->setColumns([['label' => 'Opus_Licence']]);

        $this->assertEquals('Opus_Licence', $form->getColumnLabel(0));
    }

    public function testgetColumnLabelUnknownIndex()
    {
        $form = new Application_Form_Model_Table();

        $form->setColumns([['label' => 'Opus_Licence']]);

        $this->assertNull($form->getColumnLabel(1));
    }

    public function testSetGetModels()
    {
        $form = new Application_Form_Model_Table();

        $models = Licence::getAll();

        $form->setModels($models);

        $this->assertEquals($models, $form->getModels());
    }

    public function testSetModelNotArray()
    {
        $form = new Application_Form_Model_Table();

        $models = Licence::getAll();

        $this->expectException(Application_Exception::class);
        $this->expectExceptionMessage('Parameter must be array.');
        $form->setModels('notanarray');
    }

    public function testSetGetModelsNull()
    {
        $form = new Application_Form_Model_Table();

        $form->setModels(Licence::getAll());

        $this->assertNotNull($form->getModels());

        $form->setModels(null);

        $this->assertNull($form->getModels());
    }

    public function testSetGetColumns()
    {
        $form = new Application_Form_Model_Table();

        $columns = [['label' => 'col1']];

        $form->setColumns($columns);

        $this->assertEquals($columns, $form->getColumns());
    }

    public function testGetViewScript()
    {
        $form = new Application_Form_Model_Table();

        $this->assertEquals('modeltable.phtml', $form->getViewScript());
    }

    public function testSetViewScript()
    {
        $form = new Application_Form_Model_Table();

        $form->setViewScript('series/modeltable.phtml');

        $this->assertEquals('series/modeltable.phtml', $form->getViewScript());

        $form->setViewScript(null);

        $this->assertEquals('modeltable.phtml', $form->getViewScript());
    }

    public function testIsRenderShowActionLinkDefault()
    {
        $form = new Application_Form_Model_Table();

        $this->assertTrue($form->isRenderShowActionLink());
    }

    public function testIsModifiableDefault()
    {
        $form = new Application_Form_Model_Table();

        $this->assertTrue($form->isModifiable(null));
    }

    public function testIsUsedDefault()
    {
        $form = new Application_Form_Model_Table();

        $this->assertFalse($form->isUsed(null));
    }

    public function testIsProtectedDefault()
    {
        $form = new Application_Form_Model_Table();

        $this->assertFalse($form->isUsed(null));
    }

    public function testGetRowCssClassDefault()
    {
        $form = new Application_Form_Model_Table();

        $this->assertNull($form->getRowCssClass(null));
    }

    public function testGetRowTooltipDefault()
    {
        $form = new Application_Form_Model_Table();

        $this->assertNull($form->getRowTooltip(null));
    }

    public function testIsRenderShowActionLinkLog()
    {
        $logger = new MockLogger();
        $form   = new Application_Form_Model_Table();

        $form->setLogger($logger);

        $mock = $this->getControllerMock();
        $form->setController($mock);
        $this->assertTrue($form->isRenderShowActionLink());

        $this->assertEquals('The used controller does not have the method getShowActionEnabled.', $logger->getMessages()[0]);
    }

    public function testIsModifiableLog()
    {
        $logger = new MockLogger();
        $form   = new Application_Form_Model_Table();

        $form->setLogger($logger);

        $mock = $this->getControllerMock();
        $form->setController($mock);
        $this->assertTrue($form->isModifiable(null));

        $this->assertEquals('The used controller does not have the method isModifiable.', $logger->getMessages()[0]);
    }

    public function testIsDeletableLog()
    {
        $logger = new MockLogger();
        $form   = new Application_Form_Model_Table();

        $form->setLogger($logger);

        $mock = $this->getControllerMock();
        $form->setController($mock);
        $this->assertTrue($form->isDeletable(null));

        $this->assertEquals('The used controller does not have the method isDeletable.', $logger->getMessages()[0]);
    }

    public function testIsUsedLog()
    {
        $logger = new MockLogger();
        $form   = new Application_Form_Model_Table();

        $form->setLogger($logger);

        $mock = $this->getControllerMock();
        $form->setController($mock);
        $this->assertFalse($form->isUsed(null));

        $this->assertEquals('The used controller does not have the method isUsed.', $logger->getMessages()[0]);
    }

    public function testIsProtectedLog()
    {
        $logger = new MockLogger();
        $form   = new Application_Form_Model_Table();

        $form->setLogger($logger);

        $mock = $this->getControllerMock();
        $form->setController($mock);
        $this->assertFalse($form->isProtected(null));

        $this->assertEquals('The used controller does not have the method isProtected.', $logger->getMessages()[0]);
    }

    public function testGetRowCssClassLog()
    {
        $logger = new MockLogger();
        $form   = new Application_Form_Model_Table();

        $form->setLogger($logger);

        $mock = $this->getControllerMock();
        $form->setController($mock);
        $this->assertNull($form->getRowCssClass(null));

        $this->assertEquals('The used controller does not have the method getRowCssClass.', $logger->getMessages()[0]);
    }

    public function testGetRowTooltipLog()
    {
        $logger = new MockLogger();
        $form   = new Application_Form_Model_Table();

        $form->setLogger($logger);

        $mock = $this->getControllerMock();
        $form->setController($mock);
        $this->assertNull($form->getRowTooltip(null));

        $this->assertEquals('The used controller does not have the method getRowTooltip.', $logger->getMessages()[0]);
    }

    /**
     * @return Zend_Controller_Action_Interface
     */
    protected function getControllerMock()
    {
        // TODO PHPUNIT $this->getMockBuilder(Zend_Controller_Action_Interface::class)->getMock();
        //      Aufgrund von Problemen mit PHPUnit 5 ist folgendes notwendig - mit PHPUnit 8 sollte das nicht mehr
        //      notwendig sein.
        $request  = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();
        return new class ($request, $response, []) implements Zend_Controller_Action_Interface
        {
            public function __construct(
                Zend_Controller_Request_Abstract $request,
                Zend_Controller_Response_Abstract $response,
                array $invokeArgs = []
            ) {
            }

            /**
             * @param string $action
             */
            public function dispatch($action)
            {
            }
        };
    }
}
