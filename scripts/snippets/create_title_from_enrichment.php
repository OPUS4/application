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

/**
 * This script searches for values of the provided enrichment field (--enrichment). These values are saved
 * as title (--type). The script is executed for all documents of the specified type (--doctype). If no document type
 * is provided, the script runs for all documents.
 *
 * Command line parameters:
 * - enrichment
 * - type
 * - doctype
 * - dryrun
 */
if (basename(__FILE__) !== basename($argv[0])) {
    echo "script must be executed directy (not via opus-console)" . PHP_EOL;
    exit;
}

require_once dirname(__FILE__) . '/../common/bootstrap.php';

use Opus\Common\Document;
use Opus\Common\Repository;

$options = getopt('', ['dryrun', 'type:', 'doctype:', 'enrichment:']);

$dryrun = isset($options['dryrun']);

$doctype = '';
if ($options['doctype'] === null) {
    echo "parameter --doctype not specified; function will be executed for all document types" . PHP_EOL;
} else {
    $doctype = $options['doctype'];
}

if (! isset($options['type']) || empty($options['type'])) {
    echo "Usage: {$argv[0]} --type <type of title> (--dryrun)" . PHP_EOL;
    echo "type of title must be provided (e. g. parent)" . PHP_EOL;
    exit;
}

$enrichmentField = '';
if ($options['enrichment'] === null) {
    echo "parameter --enrichment not specified; function will now exit" . PHP_EOL;
    exit;
} else {
    $enrichmentField = $options['enrichment'];
}

$getType = 'getTitle' . ucfirst(strtolower($options['type']));
$addType = 'addTitle' . ucfirst(strtolower($options['type']));

if ($dryrun) {
    writeMessage("TEST RUN: NO DATA WILL BE MODIFIED");
}

$docFinder = Repository::getInstance()->getDocumentFinder();
$docIds    = $docFinder->setEnrichmentExists($enrichmentField)->getIds();

writeMessage(count($docIds) . " documents found");

foreach ($docIds as $docId) {
    $doc = Document::get($docId);
    if ($doc->getType() === $doctype || $doctype === '') {
        $enrichments = $doc->getEnrichment();
        foreach ($enrichments as $enrichment) {
            $enrichmentArray = $enrichment->toArray();
            if ($enrichmentArray['KeyName'] === $enrichmentField) {
                $titles = $doc->{$getType}();
                if (count($titles) > 0) {
                    writeMessage(
                        'Title ' . ucfirst(strtolower($options['type'])) . ' already exists for Document #' . $docId
                        . '. Skipping.. '
                    );
                } else {
                    $title = $doc->{$addType}();
                    $title->setValue($enrichmentArray['Value']);
                    if (! $dryrun) {
                        $doc->store();
                    }
                    writeMessage('Document #' . $docId . ' updated');
                }
            }
        }
    }
}

/**
 * @param string $message
 */
function writeMessage($message)
{
    echo $message . PHP_EOL;
}
