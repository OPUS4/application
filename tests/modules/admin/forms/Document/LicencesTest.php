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

use Opus\Common\Document;
use Opus\Common\Licence;

/**
 * Description of Document_LicencesTest
 */
class Admin_Form_Document_LicencesTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database'];

    public function testCreateForm()
    {
        $form = new Admin_Form_Document_Licences();

        $licences = Licence::getAll();

        foreach ($licences as $licence) {
            $element = $form->getElement('licence' . $licence->getId());
            $this->assertNotNull($element, 'Checkbox for Licence ' . $licence->getId() . ' is missing.');

            $cssClass = $element->getDecorator('Label')->getOption('class');
            if ($licence->getActive()) {
                $this->assertEquals(Admin_Form_Document_Licences::ACTIVE_CSS_CLASS, $cssClass);
            } else {
                $this->assertEquals(Admin_Form_Document_Licences::INACTIVE_CSS_CLASS, $cssClass);
            }
        }
    }

    public function testPopulateFromModel()
    {
        $form = new Admin_Form_Document_Licences();

        $document = Document::get(146);

        $form->populateFromModel($document);

        $licences = Licence::getAll();

        foreach ($licences as $licence) {
            $element = $form->getElement('licence' . $licence->getId());

            // Nur Lizenz mit ID = 4 ist gesetzt fuer Dokument 146
            if ($licence->getId() === 4) {
                $this->assertEquals(4, $element->getValue(), 'Lizenz ' . $licence->getId() . ' nicht gesetzt.');
            } else {
                $this->assertEquals(0, $element->getValue(), 'Lizenz ' . $licence->getId() . ' gesetzt.');
            }
        }
    }

    public function testUpdateModel()
    {
        $form = new Admin_Form_Document_Licences();

        $form->getElement('licence4')->setChecked(true);
        $form->getElement('licence2')->setChecked(true);

        $document = $this->createTestDocument();

        $form->updateModel($document);

        $licences = $document->getLicence();

        $this->assertEquals(2, count($licences));

        $licenceIds = [];

        $licenceIds[] = $licences[0]->getModel()->getId();
        $licenceIds[] = $licences[1]->getModel()->getId();

        $this->assertContains('2', $licenceIds);
        $this->assertContains('4', $licenceIds);
    }

    public function testIsEmptyFalse()
    {
        $form = new Admin_Form_Document_Licences();

        $form->getElement('licence4')->setChecked(true);
        $form->getElement('licence2')->setChecked(true);

        $this->assertFalse($form->isEmpty());
    }

    public function testIsEmptyTrue()
    {
        $form = new Admin_Form_Document_Licences();

        $this->assertTrue($form->isEmpty());
    }

    public function testHasLicenceFalse()
    {
        $form = new Admin_Form_Document_Licences();

        $document = Document::get(146);
        $licence  = Licence::get(2);

        $this->assertFalse($form->hasLicence($document, $licence));
    }

    public function testHasLicenceTrue()
    {
        $form = new Admin_Form_Document_Licences();

        $document = Document::get(146);

        $this->assertTrue($form->hasLicence($document, 4));
    }

    public function testPrepareRenderingAsView()
    {
        $form = new Admin_Form_Document_Licences();

        $form->getElement('licence4')->setChecked(true);
        $form->getElement('licence2')->setChecked(true);

        $form->prepareRenderingAsView();

        $this->assertEquals(2, count($form->getElements()));
        $this->assertNotNull($form->getElement('licence4'));
        $this->assertNotNull($form->getElement('licence2'));

        $element = $form->getElement('licence4');

        $this->assertFalse($element->getDecorator('ViewHelper'));
        $this->assertFalse($element->getDecorator('ElementHtmlTag'));
        $this->assertTrue($element->getDecorator('Label')->getOption('disableFor'));
    }
}
