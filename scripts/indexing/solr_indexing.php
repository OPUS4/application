<?php
/**
 * LICENCE
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @category    Application
 * @author      Henning Gerhardt <henning.gerhardt@slub-dresden.de>
 * @copyright   Copyright (c) 2010
 *              Saechsische Landesbibliothek - Staats- und Universitaetsbibliothek Dresden (SLUB)
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

function printMessage($message) {
    echo strftime('%d.%m.%Y %H:%M:%S', time()) . $message . "\n";
}

define('APPLICATION_ENV', 'production');

// basic bootstrapping
require_once dirname(__FILE__) . '/../common/bootstrap.php';

$config = Zend_Registry::get('Zend_Config');

$host = $config->searchengine->solr->host;
$port = $config->searchengine->solr->port;
$baseUri = $config->searchengine->solr->path;
$EOL = "\n";

$commitRange = 100;

$solr = new Apache_Solr_Service($host, $port, $baseUri);

if (false === $solr->ping()) {
    echo 'Could not connect to solr service.' . $EOL;
    return;
}

$startTime = time();
$docIds = Opus_Document::getAllIds();

$documents = array();

$conf = Zend_Registry::get('Zend_Config');
$baseFilePath = null;
if ((true === isset($conf->file->destinationPath)) and
    (true === is_dir($conf->file->destinationPath))) {
    $baseFilePath = $conf->file->destinationPath;
}

foreach($docIds as $docId) {

    printMessage(' Indexing document : ' . $docId);

    $opusDoc = new Opus_Document($docId);

    $solrDocument = Qucosa_Search_Solr_Document_OpusDocument::loadOpusDocument($opusDoc);

    if ((null !== $baseFilePath) and
        ('published' === $opusDoc->getServerState())) {
        $files = $opusDoc->getFile();
        
        if (false === is_array($files)) {
            $files = array($files);
        }

        foreach ($files as $file) {
            $fileName = $file->getPathName();
            $filePath = $baseFilePath . DIRECTORY_SEPARATOR . $docId . DIRECTORY_SEPARATOR . $fileName;

            // skip files which are invisible on frontdoor
            if (false == $file->getFrontdoorVisible()) {
                printMessage(' Skipped file "' . $filePath . '". Reason: Not visible on frontdoor.');
                continue;
            }

            if (true === file_exists($filePath)) {
                $mimeType = mime_content_type($filePath);
                switch ($mimeType) {
                    case 'application/pdf':
                        try {
                            $fileContent = Qucosa_Search_Solr_Document_Pdf::loadPdf($filePath);
                            $solrDocument->addField('fulltext', implode(' ', $fileContent->body));
                        } catch (Exception $e) {
                            printMessage(' Skipped file "' . $filePath . '". Reason: ' . $e->getMessage());
                        }
                        break;

                    default:
                        printMessage(' Skipped file "' . $filePath . '". Reason: Mime type "' . $mimeType . '" has no processor.');
                        break;
                }
            } else {
                printMessage(' Skipped file "' . $filePath . '". Reason: File not found.');
            }
        }
    } else {
        printMessage(' Skipped indexing of document files. Reason: no base path to files or document is not published.');
    }

    $documents[] = $solrDocument;

    if (0 === (count($documents) % $commitRange)) {
        printMessage(' Committing data set of ' . $commitRange . ' values.');
        $solr->addDocuments($documents);
        $solr->commit();
        $documents = array();
        printMessage(' Committing done.');
    }
    
}

if (count($documents) > 0 ) {
    printMessage(' Committing data set of ' . count($documents) . ' values.');
    $solr->addDocuments($documents);
    $solr->commit();
    printMessage(' Committing done.');
}

printMessage(' Optimizing.');
$solr->optimize();
printMessage(' Optimizing done.');

$stopTime = time();
$time = $stopTime - $startTime;
echo 'Time to index all documents: ' . gmstrftime('%H:%M:%S', $time) . $EOL; 

