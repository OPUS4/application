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
 * @copyright   Copyright (c) 2026, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * List of document identifiers for export.
 *
 * The purpose of this class is to isolate the export from the search or database
 * query, that determines the documents for an export.
 *
 * TODO support iterating over the result?
 * TODO active object hiding multiple queries if totalResults > results
 */
class Application_Export_SearchResult
{
    /** @var int */
    private $totalResults = 0;

    /** @var array */
    private $documentIds;

    public function __construct(array $documentIds, int $totalResults = 0)
    {
        $this->documentIds = $documentIds;
        $documentCount     = count($documentIds);
        if ($totalResults === 0 && $documentCount > 0) {
            $this->totalResults = $documentCount;
        } else {
            $this->totalResults = $totalResults;
        }
    }

    public function getDocumentIds(): array
    {
        return $this->documentIds;
    }

    public function getDocumentCount(): int
    {
        return count($this->documentIds);
    }

    public function getTotalResults(): int
    {
        return $this->totalResults;
    }
}
