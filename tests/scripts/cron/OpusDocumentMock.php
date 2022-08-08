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

use Opus\Common\Date;
use Opus\Common\Model\ModelException;
use Opus\Document;
use Opus\Model\DbConstrainViolationException;
use Opus\Model\DbException;

/**
 * Mock used by DbCleanTemporary
 */
class OpusDocumentMock extends Document
{

    /**
     * This function is needed to set ServerDateModified bypassing the regular store-function of Document, since it
     * would update ServerDateModified to the current time.
     *
     * TODO hopefully the "temporary" state for documents will disappear, when the Publish module is rewritten. This
     *      will make the cleanup script and these tests unnecessary.
     * TODO Also setting ServerDatePublished automatically is "business logic". It should be handle it a plugin or
     *      a piece of code that can be disabled without the need to subclass Document.
     *
     * @param Date $date
     * @throws Zend_Db_Exception
     * @throws ModelException
     * @throws DbConstrainViolationException
     * @throws DbException
     */
    public function changeServerDateModified($date)
    {
        $this->setServerDateModified($date);
        // Start transaction
        $dbadapter = $this->getTableRow()->getTable()->getAdapter();
        $dbadapter->beginTransaction();

        // store internal and external fields
        try {
            $id = $this->_storeInternalFields();
            $this->_postStoreInternalFields();
            $this->_storeExternalFields();
            $this->_postStoreExternalFields();
        } catch (Exception $e) {
            $dbadapter->rollBack();
            throw $e;
        }

        // commit transaction
        $dbadapter->commit();

        $this->_postStore();
    }
}
