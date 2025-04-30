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

use Opus\Common\Document;
use Opus\Common\Person;
use Symfony\Component\Console\Output\NullOutput;

class Application_Console_Orcid_NormalizeCommandTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database'];

    public function testFixOrcidValues()
    {
        $doc    = $this->createTestDocument();
        $person = Person::new();
        $person->setLastName('Tester');
        $person->setIdentifierOrcid('0000-0002-1694-233');
        $doc->addPersonAuthor($person);
        $docId = (int) $doc->store();

        $command = new Application_Console_Orcid_NormalizeCommand();
        $command->fixOrcidValues(new NullOutput());

        $doc    = Document::get($docId);
        $person = $doc->getPerson(0);
        $this->assertEquals('0000-0002-1694-233X', $person->getIdentifierOrcid());
    }

    public function testFixOrcid()
    {
        $command = new Application_Console_Orcid_NormalizeCommand();

        $this->assertNull($command->fixOrcid('0000-0001-5109-3700'));
        $this->assertNull($command->fixOrcid('1111-2222-3333-4444'));
        $this->assertEquals('0000-0002-1694-233X', $command->fixOrcid('0000-0002-1694-233'));
    }
}
