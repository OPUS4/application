<?php

/*
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
 * @category    TODO
 * @author      Jens Schwidder <schwidder@zib.de>
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Helper for getting a list of document IDs used by admin and review module.
 */
class Application_Controller_Action_Helper_Documents extends Zend_Controller_Action_Helper_Abstract {

    /**
     * Gets called when the helper is used like a function of the helper broker.
     *
     * @param string $sortOrder
     * @param boolean $sortReverse
     * @param string $state ('published', 'unpublished', ...)
     * @return array of document identifiers
     */
    public function direct($sortOrder = null, $sortReverse = 0, $state = 'published') {
        return $this->getSortedDocumentIds($sortOrder, $sortReverse, $state);
    }

    /**
     * Returns Opus_Document for provided ID or throws exception.
     * @param string $docId Document identifier
     * @return Opus_Document
     */
    public function getDocumentForId($docId) {
        // Check if parameter is formally correct
        if (empty($docId) || !is_numeric($docId)) {
            return null;
        }

        try {
            $doc = new Opus_Document($docId);
        }
        catch (Opus_Model_NotFoundException $omnfe) {
            return null;
        }

        return $doc;
    }

    /**
     * Returns documents from database for browsing.
     *
     * @param string $sortOrder
     * @param boolean $sortReverse
     * @param string @state
     * @return array of document identifiers
     *
     * TODO following could be handled inside a application model
     */
    public function getSortedDocumentIds($sortOrder = null, $sortReverse = true, $state = null) {
        $finder = new Opus_DocumentFinder();

        if (!is_null($state) && $state !== 'all')
        {
            $finder->setServerState($state);
        }

        switch ($sortOrder)
        {
            case 'author':
                $finder->orderByAuthorLastname($sortReverse);
                break;
            case 'publicationDate':
                $finder->orderByServerDatePublished($sortReverse);
                break;
            case 'docType':
                $finder->orderByType($sortReverse);
                break;
            case 'title':
                $finder->orderByTitleMain($sortReverse);
                break;
            default:
                $finder->orderById($sortReverse);
                break;
        }

        return $finder->ids();
    }

}
