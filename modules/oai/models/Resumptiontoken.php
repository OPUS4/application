<?php

/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the North Rhine-Westphalian Library Service Center,
 * the Cooperative Library Network Berlin-Brandenburg, the Saarland University
 * and State Library, the Saxon State Library - Dresden State and University
 * Library, the Bielefeld University Library and the University Library of
 * Hamburg University of Technology with funding from the German Research
 * Foundation and the European Regional Development Fund.
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
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Contains content and structure of a resumption token
 */
class Oai_Model_Resumptiontoken
{
    /** @var array Holds dcoument ids */
    private $documentIds = [];

    /** @var string Holds metadata prefix information */
    private $metadataPrefix;

    /** @var string Holds resumption id (only if token is stored) */
    private $resumptionId;

    /** @var int Holds start postion */
    private $startPosition = 0;

    /** @var int Holds total amount of document ids */
    private $totalIds = 0;

    /** @var string Holds the set specification (if given) */
    private $set;

    /**
     *  Returns current holded document ids.
     *
     * @return array
     */
    public function getDocumentIds()
    {
        return $this->documentIds;
    }

    /**
     * Returns metadata prefix information.
     *
     * @return string
     */
    public function getMetadataPrefix()
    {
        return $this->metadataPrefix;
    }

    /**
     * Return setted resumption id after successful storing of resumption token.
     *
     * @return string Returns resumption id
     */
    public function getResumptionId()
    {
        return $this->resumptionId;
    }

    /**
     * Returns start position.
     *
     * @return int
     */
    public function getStartPosition()
    {
        return $this->startPosition;
    }

    /**
     * Returns total number of document ids for this request
     *
     * @return int
     */
    public function getTotalIds()
    {
        return $this->totalIds;
    }

    /**
     * Returns the set specification.
     *
     * @return string|null
     */
    public function getSet()
    {
        return $this->set;
    }

    /**
     * Set document ids for this token.
     *
     * @param array $idsToStore Set of document ids to store.
     */
    public function setDocumentIds($idsToStore)
    {
        if (false === is_array($idsToStore)) {
            $idsToStore = [$idsToStore];
        }

        $this->documentIds = $idsToStore;
    }

    /**
     * Set metadata prefix information.
     *
     * @param string $prefix
     */
    public function setMetadataPrefix($prefix)
    {
        $this->metadataPrefix = $prefix;
    }

    /**
     * Set resumption id
     *
     * @param string $resumptionId
     */
    public function setResumptionId($resumptionId)
    {
        $this->resumptionId = $resumptionId;
    }

    /**
     * Set postion where to start on next request.
     *
     * @param int $startPosition Positon where to start on next request
     */
    public function setStartPosition($startPosition)
    {
        $this->startPosition = $startPosition;
    }

    /**
     * Set count of document ids for this request.
     *
     * @param int $totalIds
     */
    public function setTotalIds($totalIds)
    {
        $this->totalIds = $totalIds;
    }

    /**
     * Sets the set specification.
     *
     * @param string $set
     */
    public function setSet($set)
    {
        $this->set = $set;
    }
}
