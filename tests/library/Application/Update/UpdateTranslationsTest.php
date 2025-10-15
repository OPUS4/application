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
 * @copyright   Copyright (c) 2025, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Translate\Dao;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;

class Application_Update_UpdateTranslationsTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'database';

    /** @var Application_Update_UpdateTranslations */
    private $updater;

    /** @var Dao */
    private $translations;

    public function setUp(): void
    {
        parent::setUp();

        $this->updater = new Application_Update_UpdateTranslations();
        $this->updater->setOutput(new NullOutput());

        $this->translations = new Dao();
        $this->translations->setTranslation('oldTestTranslation', [
            'en' => 'translationEN',
            'de' => 'translationDE',
        ]);
        $this->translations->remove('newTestTranslation');
    }

    public function tearDown(): void
    {
        $this->translations->remove('oldTestTranslation');
        $this->translations->remove('newTestTranslation');

        parent::tearDown();
    }

    public function testUpdate()
    {
        $oldKey = 'oldTestTranslation';
        $newKey = 'newTestTranslation';

        $this->assertNotNull($this->translations->getTranslation($oldKey));
        $this->assertNull($this->translations->getTranslation($newKey));

        $this->updater->update($oldKey, $newKey);

        $this->assertNull($this->translations->getTranslation($oldKey));
        $this->assertNotNull($this->translations->getTranslation($newKey));
    }

    public function testUpdateOldKeyDoesNotExist()
    {
        $oldKey = 'oldTestTranslation';
        $newKey = 'newTestTranslation';

        $this->translations->remove($oldKey);

        $this->assertNull($this->translations->getTranslation($oldKey));
        $this->assertNull($this->translations->getTranslation($newKey));

        $this->updater->update($oldKey, $newKey);

        $this->assertNull($this->translations->getTranslation($oldKey));
        $this->assertNull($this->translations->getTranslation($newKey));
    }

    public function testUpdateNewKeyAlreadyExists()
    {
        $oldKey = 'oldTestTranslation';
        $newKey = 'newTestTranslation';

        $this->translations->setTranslation($newKey, [
            'en' => 'newTranslationEN',
            'de' => 'newTranslationDE',
        ]);

        $this->assertNotNull($this->translations->getTranslation($oldKey));
        $this->assertNotNull($this->translations->getTranslation($newKey));

        $output = new BufferedOutput();
        $this->updater->setOutput($output);

        $this->updater->update($oldKey, $newKey);

        $this->assertNotNull($this->translations->getTranslation($oldKey));
        $this->assertNotNull($this->translations->getTranslation($newKey));

        // Check existing key was not changed
        $this->assertEqualsCanonicalizing([
            'en' => 'newTranslationEN',
            'de' => 'newTranslationDE',
        ], $this->translations->getTranslation($newKey));

        $this->assertStringContainsString(
            "New key '{$newKey}' already exists.",
            $output->fetch()
        );
    }
}
