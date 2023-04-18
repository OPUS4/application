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
use Opus\Common\Model\NotFoundException;
use Opus\Common\Repository;

/**
 * Finds all non-extractable full texts.
 *
 * TODO make part of diagnostic tools for index problems
 */

$host = 'opus4web.zib.de';
$port = '8984';
$app  = 'solr/opus';

$solrServer = new Apache_Solr_Service($host, $port, $app);

$docFinder = Repository::getInstance()->getDocumentFinder();

$overallNumOfFulltexts        = 0;
$numOfNonExtractableFulltexts = 0;

foreach ($docFinder->getIds() as $id) {
    $d = null;
    try {
        $d = Document::get($id);
    } catch (NotFoundException $e) {
        // document with id $id does not exist
        continue;
    }

    $files = $d->getFile();
    if (count($files) === 0) {
        continue;
    }

    foreach ($files as $file) {
        $overallNumOfFulltexts++;
        $response = null;
        try {
            $response = $solrServer->extract(
                $file->getPath(),
                ['extractOnly' => 'true', 'extractFormat' => 'text']
            );
        } catch (Exception $e) {
            echo "error while extracting full text for document # " . $d->getId() . " (file name : "
                . $file->getPath() . " )\n";
            $numOfNonExtractableFulltexts++;
            continue;
        }
        $rawResponse = $response->getRawResponse();
        if ($rawResponse === null || strlen(trim($rawResponse)) === 0) {
            echo "non-extractable full text for document # " . $d->getId() . " (file name: "
                . $file->getPath() . " )\n";
            $numOfNonExtractableFulltexts++;
        }
    }
}

echo "overall num of full texts: $overallNumOfFulltexts\n";

$errorRate = (100.0 * $numOfNonExtractableFulltexts) / $overallNumOfFulltexts;
echo "num of non extractable full texts: $numOfNonExtractableFulltexts ($errorRate %)\n";

exit();
