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
 * @category    Application
 * @author      Edouard Simon (edouard.simon@zib.de)
 * @author      Michael Lang  (lang@zib.de)
 * @copyright   Copyright (c) 2008-2014, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id: update-thesispublisher.php 11775 2013-06-25 14:28:41Z tklein $
 */

/**
 * This script searches for values of the provided enrichment field (--enrichment). These values are saved
 * as title (--type). The script is executed for all documents of the specified type (--doctype). If no document type
 * is provided, the script runs for all documents.
 *
 * @param enrichment
 * @param type
 * @param doctype
 * @param dryrun
 */
if (basename(__FILE__) !== basename($argv[0])) {
    echo "script must be executed directy (not via opus-console)" . PHP_EOL;
    exit;
}

require_once dirname(__FILE__) . '/../common/bootstrap.php';

$options = getopt('', ['dryrun', 'type:', 'doctype:', 'enrichment:']);

$dryrun = isset($options['dryrun']);

$doctype = '';
if (is_null($options['doctype'])) {
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
if (is_null($options['enrichment'])) {
    echo "parameter --enrichment not specified; function will now exit" . PHP_EOL;
    exit;
} else {
    $enrichmentField = $options['enrichment'];
}

$getType = 'getTitle' . ucfirst(strtolower($options['type']));
$addType = 'addTitle' . ucfirst(strtolower($options['type']));

if ($dryrun) {
    _log("TEST RUN: NO DATA WILL BE MODIFIED");
}

$docFinder = new Opus_DocumentFinder();
$docIds = $docFinder->setEnrichmentKeyExists($enrichmentField)->ids();

_log(count($docIds) . " documents found");

foreach ($docIds as $docId) {
    $doc = new Opus_Document($docId);
    if ($doc->getType() == $doctype || $doctype == '') {
        $enrichments = $doc->getEnrichment();
        foreach ($enrichments as $enrichment) {
            $enrichmentArray = $enrichment->toArray();
            if ($enrichmentArray['KeyName'] == $enrichmentField) {
                $titles = $doc->{$getType}();
                if (count($titles) > 0) {
                    _log(
                        'Title ' . ucfirst(strtolower($options['type'])) . ' already exists for Document #' . $docId
                        . '. Skipping.. '
                    );
                } else {
                    $title = $doc->{$addType}();
                    $title->setValue($enrichmentArray['Value']);
                    if (! $dryrun) {
                        $doc->store();
                    }
                    _log('Document #' . $docId . ' updated');
                }
            }
        }
    }
}

function _log($message)
{
    echo $message . PHP_EOL;
}
