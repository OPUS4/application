#!/usr/bin/env php7
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
 * @package     Import
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

require_once dirname(__FILE__) . '/../common/bootstrap.php';

/*
 * TODO logging
 * TODO handling of special characters in Rule classes
 * TODO move code to classes for unit testing (generalize)
 * TODO mode for only listing newly added documents in subsequent imports
 */

use Opus\Bibtex\Import\Parser;
use Opus\Bibtex\Import\Processor\Rule\RawData;

$verbose = true;

// Process command line parameters
if (count($argv) < 2) {
    echo 'Please provide filename for import.' . PHP_EOL;
    exit(-1);
}

$filename = $argv[1];
if (! is_readable($filename)) {
    echo 'File not found or not readable.' . PHP_EOL;
    exit(-1);
}

// TODO Hier bitte die IDs der Sammlungen eintragen, denen die Dokumente hinzugefügt werden sollen.
$collectionIds = [
    16754, // 'no-projects'
    17062  // 'Pokutta, Sebastian'
];

$collections = [];

foreach ($collectionIds as $colId) {
    try {
        $col = new Opus_Collection($colId);
        $collections[] = $col;
    } catch (Opus_Model_NotFoundException $omnfe) {
       echo "Collection $colId not found" . PHP_EOL;
    }
}

$importDate = gmdate('c');

$importId = uniqid('', true);

$format = 'bibtex';

class ImportHelper
{

    public function createEnrichmentKey($name) {
        $sourceEnrichmentKey = Opus_EnrichmentKey::fetchByName($name);
        if (is_null($sourceEnrichmentKey)) {
            $sourceEnrichmentKey = new Opus_EnrichmentKey();
            $sourceEnrichmentKey->setName($name);
            $sourceEnrichmentKey->store();
        }
    }
}

$helper = new ImportHelper();

$requiredEnrichmentKeys = [
    RawData::SOURCE_DATA_KEY,
    RawData::SOURCE_DATA_HASH_KEY,
    'opus.import.date',
    'opus.import.file',
    'opus.import.format',
    'opus.import.id'
];

foreach ($requiredEnrichmentKeys as $keyName) {
    $helper->createEnrichmentKey($keyName);
}

// Parse and convert BibTeX file
$parser = new Parser();

$parser->fileToArray($filename);
$parser->convert();

$opus = $parser->getOpusFormat();

// Process OPUS model array
echo 'Importing' . PHP_EOL;

$count = 0;
$imported = 0;
$digits = strlen(count($opus));

foreach ($opus as $docdata) {
    $doc = Opus_Document::fromArray($docdata);

    $enrichments = $doc->getEnrichment();

    $hash = null;

    foreach ($enrichments as $enrichment) {
        if ($enrichment->getKeyName() === RawData::SOURCE_DATA_HASH_KEY) {
            $hash = $enrichment->getValue();
            break;
        }
    }

    // Check if BibTeX entry has already been imported
    $alreadyImported = false;

    $finder = new Opus_DocumentFinder();

    if (! is_null($hash)) {
        $finder->setEnrichmentKeyValue(RawData::SOURCE_DATA_HASH_KEY, $hash);
        if ($finder->count() > 0) {
            $alreadyImported = true;
        }
    }

    if (! $alreadyImported) {
        // Add document to collections
        foreach ($collections as $col) {
            $doc->addCollection($col);
        }

        // Add import enrichments
        $enrichment = new Opus_Enrichment();
        $enrichment->setKeyName('opus.import.date');
        $enrichment->setValue($importDate);
        $doc->addEnrichment($enrichment);

        $enrichment = new Opus_Enrichment();
        $enrichment->setKeyName('opus.import.file');
        $enrichment->setValue($filename);
        $doc->addEnrichment($enrichment);

        $enrichment = new Opus_Enrichment();
        $enrichment->setKeyName('opus.import.format');
        $enrichment->setValue($format);
        $doc->addEnrichment($enrichment);

        $enrichment = new Opus_Enrichment();
        $enrichment->setKeyName('opus.import.id');
        $enrichment->setValue($importId);
        $doc->addEnrichment($enrichment);

        try {
            $doc = new Opus_Document($doc->store());
        } catch (Opus_Model_Exception $ome) {
            echo $ome->getMessage();
        }

        $imported++;
        echo '.';
    } else {
        echo 'D';
    }

    if ($verbose) {
        echo PHP_EOL;
        if ($alreadyImported) {
            echo 'Existing document ' . $finder->ids()[0] . " ($hash)" . PHP_EOL;
        } else {
            $title = $doc->getMainTitle();
            if (is_null($title)) {
                $title = '[NO TITLE]';
            } else {
                $title = "\"{$title->getValue()}\"";
            }
            echo 'New document ' . $doc->getId() . " ($title)" . PHP_EOL;
        }
    }

    $count++;
    if ($count % (79 - $digits) == 0) {
        printf(" %{$digits}d" . PHP_EOL, $count);
    }
}

echo PHP_EOL;
echo $count . ' entries processed' . PHP_EOL;
echo $imported . ' documents imported' . PHP_EOL;
