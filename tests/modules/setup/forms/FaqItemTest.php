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
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Translate\Dao;

class Setup_Form_FaqItemTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'Translation';

    public function setUp(): void
    {
        parent::setUp();

        $translations = new Dao();
        $translations->removeAll();
    }

    public function tearDown(): void
    {
        $translations = new Dao();
        $translations->removeAll();

        parent::tearDown();
    }

    public function testInit()
    {
        $this->markTestIncomplete();
    }

    public function testUpdatingContact()
    {
        $form = new Setup_Form_FaqItem();

        $form->setName('contact');

        $content = [
            'en' => 'new contact text',
            'de' => 'neuer Kontakt text',
        ];

        $element = $form->getElement('Answer');
        $element->setValue($content);

        $form->updateEntry();

        $translations = new Dao();

        $this->assertEquals(
            $content,
            $translations->getTranslation('help_content_contact')
        );
    }

    public function testUpdatingImprint()
    {
        $form = new Setup_Form_FaqItem();

        $form->setName('imprint');

        $content = [
            'en' => 'new imprint text',
            'de' => 'neuer Impressum text',
        ];

        $element = $form->getElement('Answer');
        $element->setValue($content);

        $form->updateEntry();

        $translations = new Dao();

        $this->assertEquals(
            $content,
            $translations->getTranslation('help_content_imprint')
        );
    }
}
