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
 * @category    Test
 * @package     Setup_Form
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2021, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Setup_Form_HomePageTest extends ControllerTestCase
{

    protected $additionalResources = 'Translation';

    private $database;

    public function setUp()
    {
        parent::setUp();

        $this->database = new \Opus\Translate\Dao();
        $this->database->removeAll();
    }

    public function tearDown()
    {
        $this->database->removeAll();
        $translate = Application_Translate::getInstance();
        $translate->clearCache();
        \Zend_Translate::clearCache();

        parent::tearDown();
    }

    public function testInit()
    {
        $form = new Setup_Form_HomePage();

        $element = $form->getElement('home_index_index_pagetitle');
        $this->assertNotNull($element);
        $this->assertEquals([
            'en' => 'Home',
            'de' => 'Einstieg'
        ], $element->getValue());
    }

    public function testUpdatingTranslations()
    {
        $key = 'home_index_index_pagetitle';

        $this->database->setTranslation($key, [
            'en' => 'TestHome',
            'de' => 'TestEinstieg'
        ], null);

        $translations = $this->database->getTranslationsWithModules();

        $this->assertCount(1, $translations);
        $this->assertArrayHasKey($key, $translations);
        $this->assertEmpty($translations[$key]['module']);

        $form = new Setup_Form_HomePage();

        $element = $form->getElement($key);

        $change = [
            'en' => 'Homepage',
            'de' => 'Startseite'
        ];

        $element->setValue($change);

        $form->updateTranslations();

        $database = $this->database;

        $translations = $database->getTranslationsWithModules();

        $this->assertCount(1, $translations);
        $this->assertArrayHasKey($key, $translations);
        $this->assertEquals('home', $translations[$key]['module']);
    }
}
