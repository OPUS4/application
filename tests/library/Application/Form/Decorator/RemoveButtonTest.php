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
 * Unit Tests fuer Klasse, die Remove-Button ausgibt.
 */
class Application_Form_Decorator_RemoveButtonTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'view';

    public function testRender()
    {
        $form = new Zend_Form();
        $form->setName('Test');
        $form->addElement('submit', 'Remove');

        $decorator = new Application_Form_Decorator_RemoveButton();
        $decorator->setElement($form);

        $output = $decorator->render('content'); // Output wird an content dran gehängt

        $this->assertEquals('content<input type="submit" name="Remove" id="Remove" value="Remove" />', $output);
    }

    public function testRenderWithHidden()
    {
        $form = new Zend_Form();
        $form->setName('Test');
        $form->addElement('submit', 'Remove');
        $element = $form->createElement('hidden', 'Id');
        $element->setValue(10);
        $form->addElement($element);

        $decorator = new Application_Form_Decorator_RemoveButton();
        $decorator->setElement($form);
        $decorator->setSecondElement($element);

        $output = $decorator->render('content'); // Output wird an content dran gehängt

        $this->assertEquals(
            'content'
            . '<input type="hidden" name="Id" id="Id" value="10" />'
            . '<input type="submit" name="Remove" id="Remove" value="Remove" />',
            $output
        );
    }

    public function testSetSecondElementOption()
    {
        $element   = new Application_Form_Element_Hidden('name');
        $decorator = new Application_Form_Decorator_RemoveButton(['element' => $element]);

        $this->assertEquals($element, $decorator->getSecondElement());
        $this->assertEquals($element, $decorator->getSecondElement()); // works 2nd time as well
    }
}
