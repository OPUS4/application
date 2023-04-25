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

use Opus\Common\Collection;
use Opus\Common\CollectionInterface;
use Opus\Common\Document;
use Opus\Common\Model\ModelException;
use Opus\Common\Model\NotFoundException;

class Admin_Model_Collection
{
    /** @var CollectionInterface|null */
    private $collection;

    /**
     * @param int|null $id TODO BUG int cannot be null
     * @throws Admin_Model_Exception
     */
    public function __construct($id = null)
    {
        if ($id === '') {
            throw new Admin_Model_Exception('missing parameter id');
        }
        if ($id === null) {
            $this->initNewCollection();
            return;
        }
        try {
            $this->collection = Collection::get($id);
        } catch (NotFoundException $e) {
            throw new Admin_Model_Exception('id parameter value unknown');
        }
    }

    private function initNewCollection()
    {
        $this->collection = Collection::new();
        $this->collection->setVisible('1');
        $this->collection->setVisiblePublish('1');
    }

    /**
     * @return CollectionInterface
     */
    public function getObject()
    {
        return $this->collection;
    }

    /**
     * @return int|false
     */
    public function delete()
    {
        if ($this->collection === null) {
            return false;
        }
        $parents = $this->collection->getParents();
        $this->collection->delete();
        return $parents[1]->getId();
    }

    /**
     * @param bool|int|string $visibility
     * @return int|false
     * @throws ModelException
     */
    public function setVisiblity($visibility)
    {
        if ($this->collection === null) {
            return false; // TODO BUG throw exception, let PHP 8 handle it
        }
        $this->collection->setVisible($visibility);
        $this->collection->store();
        $parents = $this->collection->getParents();
        return $parents[1]->getId();
    }

    /**
     * @param int $documentId
     * @throws Admin_Model_Exception
     */
    public function addDocument($documentId)
    {
        if ($documentId === null) {
            throw new Admin_Model_Exception('missing document id');
        }
        $document = null;
        try {
            $document = Document::get($documentId);
        } catch (ModelException $e) {
            throw new Admin_Model_Exception('invalid document id');
        }
        $document->addCollection($this->collection);
        $document->store();
    }

    /**
     * @return string
     */
    public function getName()
    {
        if (count($this->collection->getParents()) === 1) {
            // die Wurzel einer Collection-Hierarchie hat selbst keinen Namen/Number: in diesem Fall wird der Name
            // der Collection Role verwendet
            return $this->collection->getRole()->getDisplayName();
        }
        return $this->collection->getNumberAndName();
    }

    /**
     * Moves the collection within the same hierarchy level. Return the parent's
     * collection id.
     *
     * @param int $newPosition
     * @return int
     */
    public function move($newPosition)
    {
        if ($newPosition === null) {
            throw new Admin_Model_Exception('missing parameter pos');
        }

        $newPosition = (int) $newPosition;
        if ($newPosition < 1) {
            throw new Admin_Model_Exception('cannot move collection to position ' . $newPosition);
        }

        $parents = $this->collection->getParents();
        if (count($parents) < 2) {
            throw new Admin_Model_Exception('cannot move root collection');
        }

        $siblings = $parents[1]->getChildren();
        if ($newPosition > count($siblings)) {
            throw new Admin_Model_Exception('cannot move collection to position ' . $newPosition);
        }

        // find current position of collection
        $oldPosition = 0;
        foreach ($siblings as $position => $sibling) {
            if ($sibling->getId() === $this->collection->getId()) {
                $oldPosition = $position;
            }
        }

        // counting for newPosition is not zero-based
        $newPosition--;

        // TODO: moving distance needs to be increased
        if (abs($oldPosition - $newPosition) > 1) {
            // restore value for displaying error message
            $newPosition++;
            throw new Admin_Model_Exception('cannot move collection to position ' . $newPosition);
        }

        if ($newPosition > $oldPosition) {
            $this->collection->moveAfterNextSibling();
        } elseif ($newPosition < $oldPosition) {
            $this->collection->moveBeforePrevSibling();
        }
        return $parents[1]->getId();
    }
}
