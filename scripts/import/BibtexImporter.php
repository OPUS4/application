#!/usr/bin/env php5
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
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
/**
 *
 * TODO: dieses Skript wird aktuell nicht in den Tarball / Deb-Package aufgenommen
 * Es ist noch sehr stark an die Anforderungen einer Testinstanz angepasst und
 * müsste vor der offiziellen Aufnahme noch generalisiert werden. Die Steuerung
 * sollte über eine externe Konfigurationsdatei erfolgen, so dass der Quellcode
 * später nicht mehr angepasst werden muss.
 *
 */
require_once dirname(__FILE__) . '/../common/bootstrap.php';

/*
 * TODO add to collection "no-projects"
 * TODO mark imported documents with enrichments
 * TODO logging
 * TODO handling of special characters in Rule classes
 * TODO move code to classes for unit testing (generalize)
 */

use Opus\Bibtex\Import\Parser;

// Register enrichment key 'opus.rawdata' if necessary
$sourceEnrichmentKey = Opus_EnrichmentKey::fetchByName('opus.rawdata');
if (is_null($sourceEnrichmentKey)) {
    $sourceEnrichmentKey = new Opus_EnrichmentKey();
    $sourceEnrichmentKey->setName('opus.rawdata');
    $sourceEnrichmentKey->store();
}

$sourceHashEnrichmentKey = Opus_EnrichmentKey::fetchByName('opus.rawdata.hash');
if (is_null($sourceHashEnrichmentKey)) {
    $sourceHashEnrichmentKey = new Opus_EnrichmentKey();
    $sourceHashEnrichmentKey->setName('opus.rawdata.hash');
    $sourceHashEnrichmentKey->store();
}

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

$verbose = true;

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
        if ($enrichment->getKeyName() === 'opus.rawdata.hash') {
            $hash = $enrichment->getValue();
            break;
        }
    }

    // Check if BibTeX entry has already been imported
    $alreadyImported = false;

    $finder = new Opus_DocumentFinder();

    if (! is_null($hash)) {
        $finder->setEnrichmentKeyValue('opus.rawdata.hash', $hash);
        if ($finder->count() > 0) {
            $alreadyImported = true;
        }
    }

    if (! $alreadyImported) {
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
