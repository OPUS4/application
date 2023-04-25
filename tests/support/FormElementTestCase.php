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

abstract class FormElementTestCase extends ControllerTestCase
{
    /** @var string */
    protected $formElementClass;

    /** @var int */
    protected $expectedDecoratorCount = -1;

    /** @var string[] */
    protected $expectedDecorators = ['ViewHelper'];

    /** @var string */
    protected $staticViewHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->assertNotNull($this->formElementClass, 'No form element class configured.');
        $this->assertNotEquals(-1, $this->expectedDecorators, 'Expected decorator count not configured.');
    }

    /**
     * @param array|null $options
     * @return Zend_Form_Element
     */
    protected function getElement($options = null)
    {
        return new $this->formElementClass('name', $options);
    }

    public function testLoadDefaultDecorators()
    {
        $element = $this->getElement();

        $element->setDecorators([]);

        $this->assertEmpty($element->getDecorators());

        $element->loadDefaultDecorators();

        $this->assertEquals($this->expectedDecoratorCount, count($element->getDecorators()));

        $this->assertEquals(
            $this->expectedDecoratorCount,
            count($this->expectedDecorators),
            'Configured expected decorators do not match expected count.'
        );

        foreach ($this->expectedDecorators as $decorator) {
            $this->assertTrue(
                $element->getDecorator($decorator) !== false,
                "Decorator '$decorator' fehlt.'"
            );
        }
    }

    public function testLoadDefaultDecoratorsDisabled()
    {
        $element = $this->getElement(['disableLoadDefaultDecorators' => true]);

        $this->assertEmpty($element->getDecorators());
    }

    public function testLoadDefaultDecoratorsCustomDecorators()
    {
        $element = $this->getElement(['decorators' => ['ViewHelper']]);

        $this->assertEquals(1, count($element->getDecorators()));
        $this->assertNotNull($element->getDecorator('ViewHelper'));
    }

    public function testPrepareRenderingAsView()
    {
        $element = $this->getElement();

        if ($element instanceof Application_Form_FormElementInterface) {
            $element->prepareRenderingAsView();

            $this->assertNotNull($element->getDecorator('ViewHelper'));
            $this->assertInstanceOf('Application_Form_Decorator_ViewHelper', $element->getDecorator('ViewHelper'));
            $this->assertTrue($element->getDecorator('ViewHelper')->isViewOnlyEnabled());
        }
    }

    public function testConstruct()
    {
        $element = $this->getElement();

        $paths = $element->getPluginLoader(Zend_Form::DECORATOR)->getPaths();
        $this->assertArrayHasKey('Application_Form_Decorator_', $paths);
        $this->assertContains('Application/Form/Decorator/', $paths['Application_Form_Decorator_']);
    }

    public function testGetStaticViewHelper()
    {
        $element = $this->getElement();

        if (isset($this->staticViewHelper)) {
            $this->assertEquals($this->staticViewHelper, $element->getStaticViewHelper());
        } else {
            // if method exists _staticViewHelper should be configured for testing
            $this->assertFalse(
                method_exists($element, 'getStaticViewHelper'),
                'Need to configure \'_staticViewHelper\' for test in class ' . __CLASS__ . '.'
            );
        }
    }
}
