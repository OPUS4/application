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

use Opus\Common\Document;
use Opus\Common\DocumentInterface;
use Opus\Common\Model\NotFoundException;
use Opus\Common\Repository;

/**
 * Helper for getting a list of document IDs used by admin and review module.
 */
class Application_Controller_Action_Helper_Documents extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Gets called when the helper is used like a function of the helper broker.
     *
     * @param null|string $sortOrder
     * @param bool        $sortReverse
     * @param string      $state ('published', 'unpublished', ...)
     * @return array of document identifiers
     */
    public function direct($sortOrder = null, $sortReverse = 0, $state = 'published')
    {
        return $this->getSortedDocumentIds($sortOrder, $sortReverse, $state);
    }

    /**
     * Returns Document for provided ID or throws exception.
     *
     * @param string $docId Document identifier
     * @return DocumentInterface|null
     */
    public function getDocumentForId($docId)
    {
        // Check if parameter is formally correct
        if (empty($docId) || ! is_numeric($docId)) {
            return null;
        }

        try {
            $doc = Document::get($docId);
        } catch (NotFoundException $omnfe) {
            return null;
        }

        return $doc;
    }

    /**
     * Returns documents from database for browsing.
     *
     * @param string|null $sortOrder
     * @param bool        $sortReverse
     * @param string|null $state
     * @return int[] Document identifiers
     *
     * TODO following could be handled inside a application model
     */
    public function getSortedDocumentIds($sortOrder = null, $sortReverse = true, $state = null)
    {
        $finder = Repository::getInstance()->getDocumentFinder();

        if ($state !== null && $state !== 'all') {
            $finder->setServerState($state);
        }

        switch ($sortOrder) {
            case 'author':
                $finder->setOrder('Author', $sortReverse);
                break;
            case 'publicationDate':
                $finder->setOrder('ServerDatePublished', $sortReverse);
                break;
            case 'docType':
                $finder->setOrder('Type', $sortReverse);
                break;
            case 'title':
                $finder->setOrder('Title', $sortReverse);
                break;
            default:
                $finder->setOrder('Id', $sortReverse);
                break;
        }

        return $finder->getIds();
    }
}
