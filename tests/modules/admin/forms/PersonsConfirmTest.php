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

class Admin_Form_PersonsConfirmTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'translation';

    /** @var Admin_Form_PersonsConfirm */
    private $form;

    public function setUp(): void
    {
        parent::setUp();

        $this->form = new Admin_Form_PersonsConfirm();
    }

    public function testConstruct()
    {
        $form = $this->form;

        $subforms = $form->getSubforms();

        $this->assertCount(2, $subforms);
        $this->assertArrayHasKey('Changes', $subforms);
        $this->assertArrayHasKey('Documents', $subforms);

        $elements = $form->getElements();

        $this->assertCount(4, $elements);
        $this->assertArrayHasKey('Back', $elements);
        $this->assertArrayHasKey('Save', $elements);
        $this->assertArrayHasKey('Cancel', $elements);
        $this->assertArrayHasKey('FormId', $elements);

        $this->assertEquals('persons-confirm', $form->getAttrib('class'));
    }

    public function testProcessPostBack()
    {
        $form = $this->form;

        $result = $form->processPost(['Back' => 'Zurück'], null);

        $this->assertEquals(Admin_Form_PersonsConfirm::RESULT_BACK, $result);
    }

    public function testPopulateFromModel()
    {
        $this->disableTranslation();

        $form = $this->form;

        $person = [
            'last_name'  => 'Doe',
            'first_name' => 'Jane',
        ];

        $form->populateFromModel($person);

        $docsForm = $form->getSubform('Documents');

        $legend = $docsForm->getDecorator('fieldset')->getLegend();

        $this->assertEquals('admin_title_documents (8)', $legend);
    }
}
