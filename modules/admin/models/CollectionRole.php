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

use Opus\Common\CollectionRole;
use Opus\Common\CollectionRoleInterface;
use Opus\Common\Model\NotFoundException;

/**
 * TODO überarbeiten (entfernen?)
 */
class Admin_Model_CollectionRole
{
    /** @var CollectionRoleInterface */
    private $collectionRole;

    /**
     * @param int|null $id TODO BUG cannot use NULL with int
     * @throws Admin_Model_Exception
     */
    public function __construct($id = null)
    {
        if ($id === '') {
            throw new Admin_Model_Exception('missing parameter roleid');
        }
        if ($id === null) {
            $this->initNewCollectionRole();
            return;
        }
        try {
            $this->collectionRole = CollectionRole::get((int) $id);
        } catch (NotFoundException $e) {
            throw new Admin_Model_Exception('roleid parameter value unknown');
        }
    }

    /**
     * Initialisiert Defaultwerte für neue CollectionRole.
     */
    private function initNewCollectionRole()
    {
        $this->collectionRole = CollectionRole::new();
        foreach (['Visible', 'VisibleBrowsingStart', 'VisibleFrontdoor', 'VisibleOai'] as $field) {
            $this->collectionRole->getField($field)->setValue(1);
        }
    }

    /**
     * Liefert CollectionRole.
     *
     * @return null|CollectionRoleInterface
     */
    public function getObject()
    {
        return $this->collectionRole;
    }

    /**
     * Löscht CollectionRole.
     */
    public function delete()
    {
        $this->collectionRole->delete();
    }

    /**
     * Setzt Sichtbarkeit von CollectionRole.
     *
     * @param bool $visibility
     */
    public function setVisibility($visibility)
    {
        $this->collectionRole->setVisible($visibility);
        $this->collectionRole->store();
    }

    /**
     * Verschiebt CollectionRole zu neuer Position.
     *
     * @param int $position
     * @throws Admin_Model_Exception
     *
     * TODO make robuster
     */
    public function move($position)
    {
        if ($position === null) {
            return;
        }
        $position = (int) $position;
        if ($position < 1) {
            throw new Admin_Model_Exception('cannot move collection role');
        }
        $this->collectionRole->setPosition($position);
        $this->collectionRole->store();
    }
}
