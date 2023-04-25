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
 * @copyright   Copyright (c) 2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Config;
use Opus\Common\Document;
use Opus\Common\Model\NotFoundException;
use Opus\Common\Repository;

/**
 * Script for exporting all documents.
 *
 * TODO more cleanup
 * TODO move code into classes
 * TODO add command line parameters, e.g. export target
 * TODO add options for selected export? (only published)
 * TODO add progress reporting
 */

require_once dirname(__FILE__) . '/common/bootstrap.php';

$config = Config::get();

// process options
$options = getopt('o:');

if (array_key_exists('o', $options)) {
    $exportFilePath = $options['o'];
    $exportPath     = dirname($exportFilePath);
    $exportFilePath = basename($exportFilePath);

    // if path is not absolute use export folder
    if ($exportPath === '.') {
        $exportPath = $config->workspacePath . DIRECTORY_SEPARATOR . "export";
    }
} else {
    $exportFilePath = 'export.xml';
    $exportPath     = $config->workspacePath . DIRECTORY_SEPARATOR . "export";
}

$exportFilePath = $exportPath . DIRECTORY_SEPARATOR . $exportFilePath;

// Exception if not writeable
try {
    if (! is_writable($exportPath)) {
        throw new Exception("export folder is not writeable ($exportPath)");
    }
    if (file_exists($exportFilePath)) {
        throw new Exception("export file already exists ($exportFilePath)");
    }
} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
    exit(1);
}

$opusDocuments               = new DOMDocument('1.0', 'utf-8');
$opusDocuments->formatOutput = true;
$export                      = $opusDocuments->createElement('export');

$docFinder = Repository::getInstance()->getDocumentFinder();

// get all documents
foreach ($docFinder->getIds() as $id) {
    $doc = null;

    try {
        $doc = Document::get($id);
    } catch (NotFoundException $e) {
        echo "Document with id $id does not exist." . PHP_EOL;
        continue;
    }

    $domDocument  = $doc->toXml();
    $opusDocument = $domDocument->getElementsByTagName('Opus_Document')->item(0);
    $node         = $opusDocuments->importNode($opusDocument, true);
    $export->appendChild($node);
}

$opusDocuments->appendChild($export);

// write XML to export file
echo "Writing export to $exportFilePath ..." . PHP_EOL;
$exportFile = fopen($exportFilePath, 'w');
fwrite($exportFile, $opusDocuments->saveXML());
fclose($exportFile);

exit();
