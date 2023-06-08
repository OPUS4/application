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
 * @copyright   Copyright (c) 2023, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\CollectionInterface;
use Opus\Common\CollectionRole;
use Opus\Common\CollectionRoleInterface;
use Opus\Common\Document;

class Application_Console_Collection_CollectionCommandFixture
{
    /** @var CollectionRoleInterface */
    protected $role;

    /** @var CollectionInterface[] */
    protected $collections = [];

    /** @var DocumentInterface[] */
    protected $documents = [];

    public function setUp(): void
    {
        $role = CollectionRole::new();
        $role->setName('TestRole1');
        $role->setOaiName('TestRole1Oai');
        $root       = $role->addRootCollection();
        $this->role = $role;

        $col1 = $root->addLastChild();
        $col1->setName('Col1');
        $col1->setNumber('col1');
        $this->collections[] = $col1;

        $col2 = $root->addLastChild();
        $col2->setName('Col2');
        $col2->setNumber('col2');
        $this->collections[] = $col2;

        $role->store();
        $this->role = $role;

        $doc = Document::new();
        $doc->addCollection($col1);
        $doc->store();
        $this->documents[] = $doc;

        $doc = Document::new();
        $doc->addCollection($col1);
        $doc->store();
        $this->documents[] = $doc;
    }

    public function tearDown(): void
    {
        foreach ($this->documents as $document) {
            $document->delete();
        }

        foreach ($this->collections as $collection) {
            $collection->delete();
        }

        $this->role->delete();
    }

    /**
     * @return CollectionRoleInterface
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @return CollectionInterface[]
     */
    public function getCollections()
    {
        return $this->collections;
    }
}
