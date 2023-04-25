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

/**
 * Unit Tests fuer Formular fuer eine Lizenz.
 */
class Admin_Form_LicenceTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database', 'view', 'translation'];

    public function testConstructForm()
    {
        $form = new Admin_Form_Licence();

        $this->assertEquals(15, count($form->getElements()));

        $this->assertNotNull($form->getElement('Active'));
        $this->assertNotNull($form->getElement('CommentInternal'));
        $this->assertNotNull($form->getElement('DescMarkup'));
        $this->assertNotNull($form->getElement('DescText'));
        $this->assertNotNull($form->getElement('Language'));
        $this->assertNotNull($form->getElement('LinkLicence'));
        $this->assertNotNull($form->getElement('LinkLogo'));
        $this->assertNotNull($form->getElement('MimeType'));
        $this->assertNotNull($form->getElement('Name'));
        $this->assertNotNull($form->getElement('NameLong'));
        $this->assertNotNull($form->getElement('SortOrder'));
        $this->assertNotNull($form->getElement('PodAllowed'));

        $this->assertNotNull($form->getElement('Save'));
        $this->assertNotNull($form->getElement('Cancel'));
        $this->assertNotNull($form->getElement('Id'));
    }

    public function testPopulateFromModel()
    {
        $form = new Admin_Form_Licence();

        $licence = Licence::new();
        $licence->setActive(true);
        $licence->setCommentInternal('Test Internal Comment');
        $licence->setDescMarkup('<h1>Test Markup</h1>');
        $licence->setDescText('Test Description');
        $licence->setLanguage('rus');
        $licence->setLinkLicence('http://www.example.org/licence');
        $licence->setLinkLogo('http://www.example.org/logo');
        $licence->setMimeType('text/plain');
        $licence->setName('TL');
        $licence->setNameLong('Test Licence');
        $licence->setSortOrder(3);
        $licence->setPodAllowed(false);

        $form->populateFromModel($licence);

        $this->assertEquals(1, $form->getElement('Active')->getValue());
        $this->assertEquals('Test Internal Comment', $form->getElement('CommentInternal')->getValue());
        $this->assertEquals('<h1>Test Markup</h1>', $form->getElement('DescMarkup')->getValue());
        $this->assertEquals('Test Description', $form->getElement('DescText')->getValue());
        $this->assertEquals('rus', $form->getElement('Language')->getValue());
        $this->assertEquals('http://www.example.org/licence', $form->getElement('LinkLicence')->getValue());
        $this->assertEquals('http://www.example.org/logo', $form->getElement('LinkLogo')->getValue());
        $this->assertEquals('text/plain', $form->getElement('MimeType')->getValue());
        $this->assertEquals('TL', $form->getElement('Name')->getValue());
        $this->assertEquals('Test Licence', $form->getElement('NameLong')->getValue());
        $this->assertEquals(3, $form->getElement('SortOrder')->getValue());
        $this->assertEquals(0, $form->getElement('PodAllowed')->getValue());
    }

    public function testPopulateFromModelWithId()
    {
        $form = new Admin_Form_Licence();

        $licence = Licence::get(2);

        $form->populateFromModel($licence);

        $this->assertEquals(2, $form->getElement('Id')->getValue());
    }

    public function testUpdateModel()
    {
        $form = new Admin_Form_Licence();

        $form->getElement('Id')->setValue(99);
        $form->getElement('Active')->setChecked(true);
        $form->getElement('CommentInternal')->setValue('Test Internal Comment');
        $form->getElement('DescMarkup')->setValue('<h1>Test Markup</h1>');
        $form->getElement('DescText')->setValue('Test Description');
        $form->getElement('Language')->setValue('rus');
        $form->getElement('LinkLicence')->setValue('http://www.example.org/licence');
        $form->getElement('LinkLogo')->setValue('http://www.example.org/logo');
        $form->getElement('MimeType')->setValue('text/plain');
        $form->getElement('Name')->setValue('TL');
        $form->getElement('NameLong')->setValue('Test Licence');
        $form->getElement('SortOrder')->setValue(5);
        $form->getElement('PodAllowed')->setChecked(true);

        $licence = Licence::new();

        $form->updateModel($licence);

        $this->assertNull($licence->getId()); // ID wird beim Update nicht gesetzt
        $this->assertEquals(1, $licence->getActive());
        $this->assertEquals('Test Internal Comment', $licence->getCommentInternal());
        $this->assertEquals('<h1>Test Markup</h1>', $licence->getDescMarkup());
        $this->assertEquals('Test Description', $licence->getDescText());
        $this->assertEquals('rus', $licence->getLanguage());
        $this->assertEquals('http://www.example.org/licence', $licence->getLinkLicence());
        $this->assertEquals('http://www.example.org/logo', $licence->getLinkLogo());
        $this->assertEquals('text/plain', $licence->getMimeType());
        $this->assertEquals('TL', $licence->getName());
        $this->assertEquals('Test Licence', $licence->getNameLong());
        $this->assertEquals(5, $licence->getSortOrder());
        $this->assertEquals(1, $licence->getPodAllowed());
    }

    public function testValidationEmptyPost()
    {
        $form = new Admin_Form_Licence();

        $this->assertFalse($form->isValid([]));

        $this->assertContains('isEmpty', $form->getErrors('NameLong'));
        $this->assertContains('isEmpty', $form->getErrors('Language'));
        $this->assertContains('notInArray', $form->getErrors('Language'));
        $this->assertContains('isEmpty', $form->getErrors('LinkLicence'));
    }

    public function testValidationEmptyFields()
    {
        $form = new Admin_Form_Licence();

        $this->assertFalse($form->isValid([
            'NameLong'    => '  ',
            'Language'    => 'abc',
            'LinkLicence' => '  ',
        ]));

        $this->assertContains('isEmpty', $form->getErrors('NameLong'));
        $this->assertContains('notInArray', $form->getErrors('Language'));
        $this->assertContains('isEmpty', $form->getErrors('LinkLicence'));
    }

    public function testValidationUnknownLanguage()
    {
        $form = new Admin_Form_Licence();

        $this->assertFalse($form->isValid([
            'NameLong'    => 'Name',
            'Language'    => 'abc',
            'LinkLicence' => 'Link',
        ]));

        $this->assertContains('notInArray', $form->getErrors('Language'));
    }

    public function testValidationTrue()
    {
        $form = new Admin_Form_Licence();

        $this->assertTrue($form->isValid([
            'NameLong'    => 'New Test Licence',
            'Language'    => 'deu',
            'LinkLicence' => 'link',
        ]));
    }
}
