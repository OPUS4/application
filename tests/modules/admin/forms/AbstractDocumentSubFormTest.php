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
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Licence;

/**
 * Unit Tests fuer abstrakte Parent-Klasse fuer Metadaten Unterformulare.
 */
class Admin_Form_AbstractDocumentSubFormTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database'];

    /** @var Admin_Form_AbstractDocumentSubForm */
    private $form;

    public function setUp(): void
    {
        parent::setUp();
        $this->form = $this->getForm();
    }

    /**
     * @return Admin_Form_AbstractDocumentSubForm
     */
    private function getForm()
    {
        return $this->getMockForAbstractClass(Admin_Form_AbstractDocumentSubForm::class);
    }

    public function testInit()
    {
        $form = $this->form;

        $form->init();

        $this->assertTrue($form->loadDefaultDecoratorsIsDisabled());
        $this->assertEquals(4, count($form->getDecorators()));
        $this->assertNotNull($form->getDecorator('FormElements'));
        $this->assertNotNull($form->getDecorator('fieldsWrapper'));
        $this->assertNotNull($form->getDecorator('Fieldset'));
        $this->assertNotNull($form->getDecorator('divWrapper'));
    }

    /**
     * Tut nichts.
     */
    public function testPopulateFromModel()
    {
        $this->form->populateFromModel(Licence::new());
    }

    /**
     * Tut nichts.
     */
    public function testConstructFromPost()
    {
        $this->form->constructFromPost([]);
    }

    /**
     * Tut nichts.
     */
    public function testContinueEdit()
    {
        $this->form->continueEdit($this->getRequest());
    }

    public function testProcessPostNoSubforms()
    {
        $this->assertNull($this->form->processPost([], []));
    }

    public function testProcessPost()
    {
        $this->markTestIncomplete('Mocking funktioniert noch nicht.');
        $post = [
            'subform1' => [
                'Button' => 'Value',
            ],
        ];

        $subform1 = $this->getMockForAbstractClass('Application_Form_Model_Abstract');
        $subform1->expects($this->exactly(1))->method('processPost')->will($this->returnValue(null));

        $subform2 = $this->getMockForAbstractClass('Application_Form_Model_Abstract');
        $subform2->expects($this->exactly(0))->method('processPost')->will($this->returnValue(null));

        $subform2->processPost($post, $post);

        $this->assertNull($this->form->processPost($post, $post));
    }

    public function testUpdateModel()
    {
        $this->markTestIncomplete('Mocking funktioniert noch nicht.');
    }

    public function testIsDependenciesValid()
    {
        $this->markTestIncomplete('Mocking funktioniert noch nicht.');
    }

    public function testGetDatesHelper()
    {
        $form = $this->getForm();

        $this->assertNotNull($form->getDatesHelper());
        $this->assertInstanceOf('Application_Controller_Action_Helper_Dates', $form->getDatesHelper());
    }
}
