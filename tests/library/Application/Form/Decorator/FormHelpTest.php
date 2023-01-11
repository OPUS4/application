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
 * @copyright   Copyright (c) 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Application_Form_Decorator_FormHelpTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'translation';

    /** @var Application_Form_Decorator_FormHelp */
    private $decorator;

    public function setUp(): void
    {
        parent::setUp();

        $form            = new Zend_Form();
        $this->decorator = new Application_Form_Decorator_FormHelp();
        $this->decorator->setElement($form);
    }

    public function testRender()
    {
        $this->decorator->setOption('message', 'Test message');

        $output = $this->decorator->render('CONTENT');

        $this->assertEquals('<div class="form-help">Test message</div>CONTENT', $output);
    }

    public function testRenderWithoutMessage()
    {
        $output = $this->decorator->render('CONTENT');

        $this->assertEquals('CONTENT', $output);
    }

    public function testRenderWithoutTranslator()
    {
        $this->useGerman();

        $this->decorator->setOption('message', 'LicenceLanguage');
        $this->decorator->getElement()->setDisableTranslator(true);

        $output = $this->decorator->render('CONTENT');

        $this->assertEquals('<div class="form-help">LicenceLanguage</div>CONTENT', $output);
    }

    public function testRenderWithTranslation()
    {
        $this->useGerman();

        $this->decorator->setOption('message', 'LicenceLanguage');

        $output = $this->decorator->render('CONTENT');

        $this->assertEquals('<div class="form-help">Sprache</div>CONTENT', $output);
    }

    public function testRenderAppend()
    {
        $this->decorator->setOption('message', 'Test');
        $this->decorator->setOption('placement', Application_Form_Decorator_FormHelp::APPEND);

        $output = $this->decorator->render('CONTENT');

        $this->assertEquals('CONTENT<div class="form-help">Test</div>', $output);
    }

    public function testRenderCustomCssClass()
    {
        $this->decorator->setOption('message', 'Test');
        $this->decorator->setOption('class', 'custom-helptext');

        $output = $this->decorator->render('CONTENT');

        $this->assertEquals('<div class="custom-helptext">Test</div>CONTENT', $output);
    }
}
