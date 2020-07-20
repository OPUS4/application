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
 * @category    Application Unit Test
 * @package     Module_Admin
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * @category    Application Unit Test
 * @package     Module_Admin
 */
class Admin_Model_EnrichmentKeysTest extends ControllerTestCase
{

    protected $additionalResources = 'database';

    public function tearDown()
    {
        $database = new Opus_Translate_Dao();
        $database->removeAll();

        parent::tearDown();
    }

    public function testGetProtectedEnrichmentKeys()
    {
        $model = new Admin_Model_EnrichmentKeys();

        $config = new Zend_Config(['enrichmentkey' => ['protected' => [
            'modules' => 'pkey1,pkey2',
            'migration' => 'pkey3,pkey4'
        ]]]);

        $model->setConfig($config);

        $protectedKeys = $model->getProtectedEnrichmentKeys();

        $this->assertCount(4, $protectedKeys);
        $this->assertContains('pkey1', $protectedKeys);
        $this->assertContains('pkey2', $protectedKeys);
        $this->assertContains('pkey3', $protectedKeys);
        $this->assertContains('pkey4', $protectedKeys);

        $config = new Zend_Config(['enrichmentkey' => ['protected' => [
            'migration' => 'pkey3,pkey4'
        ]]]);

        $model->setConfig($config);
        $model->setProtectedEnrichmentKeys(null);

        $protectedKeys = $model->getProtectedEnrichmentKeys();

        $this->assertCount(2, $protectedKeys);
        $this->assertContains('pkey3', $protectedKeys);
        $this->assertContains('pkey4', $protectedKeys);

        $config = new Zend_Config(['enrichmentkey' => ['protected' => [
            'modules' => 'pkey1,pkey2',
        ]]]);

        $model->setConfig($config);
        $model->setProtectedEnrichmentKeys(null);

        $protectedKeys = $model->getProtectedEnrichmentKeys();

        $this->assertCount(2, $protectedKeys);
        $this->assertContains('pkey1', $protectedKeys);
        $this->assertContains('pkey2', $protectedKeys);
    }

    public function testGetProtectedEnrichmentKeysNotConfigured()
    {
        $model = new Admin_Model_EnrichmentKeys();

        $config = new Zend_Config([]);

        $model->setConfig($config);

        $protectedKeys = $model->getProtectedEnrichmentKeys();

        $this->assertInternalType('array', $protectedKeys);
        $this->assertCount(0, $protectedKeys);
    }

    public function testCreateTranslations()
    {
        $database = new Opus_Translate_Dao();
        $database->removeAll();

        $model = new Admin_Model_EnrichmentKeys();

        $name = 'MyTestEnrichment';

        $model->createTranslations($name);

        $patterns = $model->getKeyPatterns();

        $translations = $database->getTranslations('default');

        $this->assertCount(6, $translations);

        foreach ($patterns as $pattern) {
            $key = sprintf($pattern, $name);
            $this->assertArrayHasKey($key, $translations);
        }
    }

    public function testChangeNamesOfTranslationKeys()
    {
        $database = new Opus_Translate_Dao();
        $database->removeAll();

        $model = new Admin_Model_EnrichmentKeys();

        $name = 'MyTestEnrichment';

        $model->createTranslations($name);

        $newName = 'NewTest';

        $model->createTranslations($newName, $name);

        $patterns = $model->getKeyPatterns();

        $translations = $database->getTranslations('default');

        $this->assertCount(6, $translations);

        foreach ($patterns as $pattern) {
            $key = sprintf($pattern, $newName);
            $this->assertArrayHasKey($key, $translations);
        }
    }

    public function testRemoveTranslations()
    {
        $model = new Admin_Model_EnrichmentKeys();

        $database = new Opus_Translate_Dao();
        $database->removeAll();

        $name = 'TestEnrichment';

        $model->createTranslations($name);

        $translations = $database->getTranslations('default');
        $this->assertCount(6, $translations);

        $model->removeTranslations($name);

        $translations = $database->getTranslations('default');
        $this->assertCount(0, $translations);
    }
}
