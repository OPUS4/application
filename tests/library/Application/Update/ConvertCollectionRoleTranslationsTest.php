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

use Opus\Common\CollectionRole;
use Opus\Common\Model\NotFoundException;
use Opus\Translate\Dao;

class Application_Update_ConvertCollectionRoleTranslationsTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database', 'translation'];

    /** @var int */
    private $roleId;

    public function tearDown(): void
    {
        if ($this->roleId !== null) {
            try {
                $role = CollectionRole::get($this->roleId);
                $role->delete();
            } catch (NotFoundException $ex) {
            }
        }

        $database = new Dao();
        $database->removeAll();

        parent::tearDown();
    }

    public function testRun()
    {
        $role = CollectionRole::new();

        $invalidName = 'Collection Role mit ungültigem Namen.';

        $role->getField('Name')->setValidator(new DummyValidator()); // disable validation to store invalid name
        $role->setName($invalidName);
        $role->setOaiName('testColRole');

        $this->roleId = $role->store();

        $update = new Application_Update_ConvertCollectionRoleTranslations();
        $update->setQuietMode(true);
        $update->run();

        $role = CollectionRole::get($this->roleId);

        $name    = $role->getName();
        $oaiName = $role->getOaiName();

        $this->assertEquals($oaiName, $name);

        $manager = new Application_Translate_TranslationManager();

        $translation = $manager->getTranslation("default_collection_role_$name");

        foreach ($translation['translations'] as $lang => $value) {
            $this->assertEquals($invalidName, $value);
        }
    }

    public function testRunWithExistingInvalidTranslationKey()
    {
        $role = CollectionRole::new();

        $invalidName = 'Tagungsbände';

        $role->getField('Name')->setValidator(new DummyValidator()); // disable validation to store invalid name
        $role->setName($invalidName);
        $role->setOaiName('testColRole');

        $this->roleId = $role->store();

        $manager = new Application_Translate_TranslationManager();

        $manager->setTranslation("default_collection_role_$invalidName", [
            'en' => 'Translation EN',
            'de' => 'Translation DE',
        ], 'default');

        $update = new Application_Update_ConvertCollectionRoleTranslations();
        $update->setQuietMode(true);
        $update->run();

        $role = CollectionRole::get($this->roleId);

        $name    = $role->getName();
        $oaiName = $role->getOaiName();

        $this->assertEquals($oaiName, $name);

        $translation = $manager->getTranslation("default_collection_role_$name");

        $this->assertEquals([
            'en' => 'Translation EN',
            'de' => 'Translation DE',
        ], $translation['translations']);

        $this->assertFalse($manager->keyExists("default_collection_role_$invalidName"));
    }

    public function testRunWithInvalidOaiName()
    {
        $role = CollectionRole::new();

        $invalidName = 'Tagungsbände';

        $role->getField('Name')->setValidator(new DummyValidator()); // disable validation to store invalid name
        $role->setName($invalidName);
        $role->setOaiName($invalidName);

        $this->roleId = $role->store();

        $update = new Application_Update_ConvertCollectionRoleTranslations();
        $update->setQuietMode(true);
        $update->run();

        $role = CollectionRole::get($this->roleId);

        $name    = $role->getName();
        $oaiName = $role->getOaiName();

        $this->assertEquals("ColRole{$this->roleId}", $name);

        $manager = new Application_Translate_TranslationManager();

        $translation = $manager->getTranslation("default_collection_role_$name");

        $this->assertEquals([
            'en' => $invalidName,
            'de' => $invalidName,
        ], $translation['translations']);
    }
}
