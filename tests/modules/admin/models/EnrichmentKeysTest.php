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

use Opus\Common\EnrichmentKey;
use Opus\Enrichment\RegexType;
use Opus\Translate\Dao;

class Admin_Model_EnrichmentKeysTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'database';

    public function tearDown(): void
    {
        $database = new Dao();
        $database->removeAll();

        parent::tearDown();
    }

    public function testGetProtectedEnrichmentKeys()
    {
        $model = new Admin_Model_EnrichmentKeys();

        $config = new Zend_Config([
            'enrichmentkey' => [
                'protected' => [
                    'modules'   => 'pkey1,pkey2',
                    'migration' => 'pkey3,pkey4',
                ],
            ],
        ]);

        $model->setConfig($config);

        $protectedKeys = $model->getProtectedEnrichmentKeys();

        $this->assertCount(4, $protectedKeys);
        $this->assertContains('pkey1', $protectedKeys);
        $this->assertContains('pkey2', $protectedKeys);
        $this->assertContains('pkey3', $protectedKeys);
        $this->assertContains('pkey4', $protectedKeys);

        $config = new Zend_Config([
            'enrichmentkey' => [
                'protected' => [
                    'migration' => 'pkey3,pkey4',
                ],
            ],
        ]);

        $model->setConfig($config);
        $model->setProtectedEnrichmentKeys(null);

        $protectedKeys = $model->getProtectedEnrichmentKeys();

        $this->assertCount(2, $protectedKeys);
        $this->assertContains('pkey3', $protectedKeys);
        $this->assertContains('pkey4', $protectedKeys);

        $config = new Zend_Config([
            'enrichmentkey' => [
                'protected' => [
                    'modules' => 'pkey1,pkey2',
                ],
            ],
        ]);

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
        $database = new Dao();
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

    public function testCreateTranslationsDoNotOverwriteExistingValues()
    {
        $database = new Dao();
        $database->removeAll();

        $hintKey = 'hint_EnrichmentMyTestEnrichment';

        $database->setTranslation($hintKey, [
            'en' => 'English',
            'de' => 'Deutsch',
        ], 'default');

        $model = new Admin_Model_EnrichmentKeys();

        $name = 'MyTestEnrichment';

        $model->createTranslations($name);

        $patterns = $model->getKeyPatterns();

        $translations = $database->getTranslations('default');

        $this->assertCount(6, $translations);

        foreach ($patterns as $pattern) {
            $key = sprintf($pattern, $name);
            $this->assertArrayHasKey($key, $translations);
            if ($key !== 'hint_EnrichmentMyTestEnrichment') {
                $this->assertEquals([
                    'en' => 'MyTestEnrichment',
                    'de' => 'MyTestEnrichment',
                ], $translations[$key]);
            } else {
                $this->assertEquals([
                    'en' => 'English',
                    'de' => 'Deutsch',
                ], $translations[$key]);
            }
        }
    }

    public function testChangeNamesOfTranslationKeys()
    {
        $database = new Dao();
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

        $database = new Dao();
        $database->removeAll();

        $name = 'TestEnrichment';

        $model->createTranslations($name);

        $translations = $database->getTranslations('default');
        $this->assertCount(6, $translations);

        $model->removeTranslations($name);

        $translations = $database->getTranslations('default');
        $this->assertCount(0, $translations);
    }

    public function testGetTranslation()
    {
        $model = new Admin_Model_EnrichmentKeys();

        $key = 'MyTestEnrichment';

        $model->createTranslations($key);

        $translations = $model->getTranslations($key);

        $model->removeTranslations($key);

        $this->assertCount(6, $translations);

        $translationKeys = $model->getSupportedKeys();

        foreach ($translationKeys as $translationKey) {
            $this->assertArrayHasKey($translationKey, $translations);
            $this->assertEquals('MyTestEnrichment', $translations[$translationKey]['de']);
            $this->assertEquals('MyTestEnrichment', $translations[$translationKey]['en']);
        }
    }

    public function testGetEnrichmentConfig()
    {
        $model = new Admin_Model_EnrichmentKeys();

        $key = 'MyTestEnrichment';

        $enrichment = EnrichmentKey::fetchByName($key);

        if ($enrichment === null) {
            $enrichment = EnrichmentKey::new();
            $enrichment->setName($key);
            $enrichment->setType('RegexType');
            $enrichmentType = new RegexType();
            $enrichmentType->setRegex('/[a-z]+/');
            $enrichment->setOptions($enrichmentType->getOptions());
            $enrichment->store();
        }

        $this->addModelToCleanup($enrichment);

        $model->createTranslations($key);

        $config = $model->getEnrichmentConfig($key);

        // cleanup
        $model->removeTranslations($key);

        $this->assertArrayHasKey('name', $config);
        $this->assertEquals($key, $config['name']);
        $this->assertArrayHasKey('translations', $config);
        $this->assertCount(6, $config['translations']);

        $this->assertArrayHasKey('options', $config);
        $this->assertEquals('none', $config['options']['validation']);
        $this->assertEquals('/[a-z]+/', $config['options']['regex']);
    }
}
