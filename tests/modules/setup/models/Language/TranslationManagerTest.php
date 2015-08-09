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
 * @category    Application
 * @package     Module_Setup
 * @author      Edouard Simon <edouard.simon@zib.de>
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$

/**
 * Test class for Setup_Model_Language_TranslationManager.
 */
class Setup_Model_Language_TranslationManagerTest extends ControllerTestCase {

    /**
     * @var Setup_Model_Language_TranslationManager
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    public function setUp() {
        parent::setUp();
        $this->object = new Setup_Model_Language_TranslationManager;
    }

    /**
     * @todo Implement testGetFiles().
     */
    public function testGetFiles() {
        $files = $this->object->getFiles();
        $this->assertEquals(array(), $files, 'Expected empty result with no modules set');
        $this->object->setModules(array('default'));
        $files = $this->object->getFiles();
        $this->assertNotEquals(array(), $files, 'Expected non empty result with module set');
    }

    /**
     */
    public function testGetTranslations() {

        $sortKeys = array(
            Setup_Model_Language_TranslationManager::SORT_DIRECTORY,
            Setup_Model_Language_TranslationManager::SORT_FILENAME,
            Setup_Model_Language_TranslationManager::SORT_LANGUAGE,
            Setup_Model_Language_TranslationManager::SORT_MODULE,
            Setup_Model_Language_TranslationManager::SORT_UNIT,
            Setup_Model_Language_TranslationManager::SORT_VARIANT
        );

        $this->object->setModules(array('default'));

        foreach (array(SORT_ASC, SORT_DESC) as $sortOrder) {
            foreach ($sortKeys as $sortKey) {
                $actualValues = array();
                $translations = $this->object->getTranslations($sortKey, $sortOrder);
                foreach ($translations as $translation) {
                    $actualValues[] = $translation[$sortKey];
                }
                $sortedValues = $actualValues;
                if ($sortOrder == SORT_ASC)
                    sort($sortedValues, SORT_STRING);
                elseif ($sortOrder == SORT_DESC)
                    rsort($sortedValues, SORT_STRING);
                $this->assertEquals($sortedValues, $actualValues);
            }
        }
    }

    /**
     *
     */
    public function testSetModules() {

        $this->object->setModules(array('default'));
        $files = $this->object->getFiles();
        $this->assertEquals(array('default'), array_keys($files));

        $this->object->setModules(array('default', 'home'));
        $files = $this->object->getFiles();
        $this->assertEquals(array('default', 'home'), array_keys($files));
    }

    /**
     *
     */
    public function testSetFilter() {
        $filter = 'error';

        $this->object->setModules(array('default'));
        $allTranlsations = $this->object->getTranslations();

        $this->object->setFilter($filter);
        $filteredTranlsations = $this->object->getTranslations();

        $this->assertLessThan(count($allTranlsations), count($filteredTranlsations), 'Expected count of filtered subset of translations to be less than all translations');

        foreach ($filteredTranlsations as $translation) {
            $this->assertTrue(strpos($translation['unit'], $filter) !== false, 'Expected filtered translation unit to contain filter string');
        }
    }

}

?>
