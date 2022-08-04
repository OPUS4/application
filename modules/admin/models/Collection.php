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

use Opus\Document;
use Opus\Collection;
use Opus\Common\Model\ModelException;
use Opus\Common\Model\NotFoundException;

class Admin_Model_Collection
{

    private $_collection = null;

    public function __construct($id = null)
    {
        if ($id === '') {
            throw new Admin_Model_Exception('missing parameter id');
        }
        if (is_null($id)) {
            $this->initNewCollection();
            return;
        }
        try {
            $this->_collection = new Collection($id);
        } catch (NotFoundException $e) {
            throw new Admin_Model_Exception('id parameter value unknown');
        }
    }

    private function initNewCollection()
    {
        $this->_collection = new Collection();
        $this->_collection->setVisible('1');
        $this->_collection->setVisiblePublish('1');
    }

    /**
     *
     * @return Collection
     */
    public function getObject()
    {
        return $this->_collection;
    }

    public function delete()
    {
        if (is_null($this->_collection)) {
            return;
        }
        $parents = $this->_collection->getParents();
        $this->_collection->delete();
        return $parents[1]->getId();
    }

    public function setVisiblity($visibility)
    {
        if (is_null($this->_collection)) {
            return;
        }
        $this->_collection->setVisible($visibility);
        $this->_collection->store();
        $parents = $this->_collection->getParents();
        return $parents[1]->getId();
    }

    public function addDocument($documentId)
    {
        if (is_null($documentId)) {
            throw new Admin_Model_Exception('missing document id');
        }
        $document = null;
        try {
            $document = Document::get($documentId);
        } catch (ModelException $e) {
            throw new Admin_Model_Exception('invalid document id');
        }
        $document->addCollection($this->_collection);
        $document->store();
    }

    public function getName()
    {
        if (count($this->_collection->getParents()) === 1) {
            // die Wurzel einer Collection-Hierarchie hat selbst keinen Namen/Number: in diesem Fall wird der Name
            // der Collection Role verwendet
            return $this->_collection->getRole()->getDisplayName();
        }
        return $this->_collection->getNumberAndName();
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
        if (is_null($newPosition)) {
            throw new Admin_Model_Exception('missing parameter pos');
        }

        $newPosition = (int) $newPosition;
        if ($newPosition < 1) {
            throw new Admin_Model_Exception('cannot move collection to position ' . $newPosition);
        }

        $parents = $this->_collection->getParents();
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
            if ($sibling->getId() === $this->_collection->getId()) {
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
            $this->_collection->moveAfterNextSibling();
        } elseif ($newPosition < $oldPosition) {
            $this->_collection->moveBeforePrevSibling();
        }
        return $parents[1]->getId();
    }
}
