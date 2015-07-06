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
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

// Bootstrapping
require_once dirname(__FILE__) . '/common/bootstrap.php';

/**
 * Bootstraps and runs an import from Opus3
 *
 * @category Search
 */
class SolrIndexBuilder {

    private $_start = null;

    private $_end = null;

    private $_deleteAllDocs = false;

    private $_syncMode = true;

    /**
     * Prints a help message to the console.
     */
    private function printHelpMessage($argv) {
        echo "\nThis program can be used to build up an initial Solr index (e.g., useful when migrating instances)\n\n";
        echo "Usage: " . $argv[0] . " [starting with ID] [ending with ID]\n";
        echo "\n";
        echo "[starting with ID] If system aborted indexing at some ID, you can restart this command by supplying"
            . " this parameter.\n";
        echo "It should be the ID where the program stopped before.\n";
        echo "Default start value is 0.\n";
        echo "\n";
        echo "[ending with ID] You can also supply a second ID where the indexer should stop indexing.\n";
        echo "If you omit this parameter or set it to -1, the indexer will index all remaining documents.\n";
        echo "\n";
        echo "In case both parameters are not specified the currently used index is deleted before insertion of new"
            . " documents begins.\n";
        echo "\n";
    }

    /**
     * Evaluates command line arguments.
     */
    private function evaluateArguments($argc, $argv) {
        if ($argc >= 2) {
            $this->_start = $argv[1];
        }
        if ($argc >= 3) {
            $this->_end = $argv[2];
        }
        if (is_null($this->_start) && is_null($this->_end)) {
            // TODO gesondertes Argument für Indexdeletion einführen
            $this->_deleteAllDocs = true;
        }
    }

    /**
     * Starts an Opus console.
     */
    public function run() {
        global $argv, $argc;
        if (true === in_array('--help', $argv) || true === in_array('-h', $argv)) {
            $this->printHelpMessage($argv);
            exit;
        }
        $this->evaluateArguments($argc, $argv);
        $this->forceSyncMode();
        $docIds = Opus_Document::getAllPublishedIds($this->_start, $this->_end);

	    $indexer = Opus_Search_Service::selectIndexingService( 'indexBuilder' );
	    if ( $this->_deleteAllDocs ) {
		    $indexer->removeAllDocumentsFromIndex();
	    }

	    $extractor = Opus_Search_Service::selectExtractingService( 'indexBuilder' );

        echo date('Y-m-d H:i:s') . " Start indexing of " . count($docIds) . " documents.\n";
        $numOfDocs = 0;
        $runtime = microtime(true);
        foreach ($docIds as $docId) {
            $timeStart = microtime(true);

            $doc = new Opus_Document($docId);

            // dirty hack: disable implicit reindexing of documents in case of cache misses
            $doc->unregisterPlugin('Opus_Document_Plugin_Index');

	        try {
		        $indexer->addDocumentsToIndex( $doc );

		        foreach ( $doc->getFile() as $file ) {
			        $extractor->extractDocumentFile( $file, $doc );
		        }
	        } catch ( Opus_Search_Exception $e ) {
		        echo date('Y-m-d H:i:s') . " ERROR: Failed indexing document $docId.\n";
		        echo date('Y-m-d H:i:s') . "        {$e->getMessage()}\n";

	        } catch ( Opus_Storage_Exception $e ) {
		        echo date('Y-m-d H:i:s') . " ERROR: Failed indexing unavailable file on document $docId.\n";
		        echo date('Y-m-d H:i:s') . "        {$e->getMessage()}\n";

	        }


            $timeDelta = microtime(true) - $timeStart;
            if ($timeDelta > 30) {
               echo date('Y-m-d H:i:s') . " WARNING: Indexing document $docId took $timeDelta seconds.\n";
            }

            $numOfDocs++;
            if ($numOfDocs % 10 == 0) {
                $memNow = round(memory_get_usage() / 1024 / 1024);
                $memPeak = round(memory_get_peak_usage() / 1024 / 1024);
                $deltaTime = microtime(true) - $runtime;
                $docPerSecond = round($deltaTime) == 0 ? 'inf' : round($numOfDocs/$deltaTime, 2);
                $secondsPerDoc = round($deltaTime/$numOfDocs, 2);
                echo date('Y-m-d H:i:s') . " Stats after $numOfDocs documents -- memory $memNow MB,"
                    . " peak memory $memPeak (MB), $docPerSecond docs/second, $secondsPerDoc seconds/doc\n";
            }
        }
        $runtime = microtime(true) - $runtime;
        echo "\n" . date('Y-m-d H:i:s') . " Finished indexing.\n";
	    // new search API doesn't track number of indexed files, but issues are kept written to log file
        //echo "\n\nErrors appeared in " . $indexer->getErrorFileCount() . " of " . $indexer->getTotalFileCount()
        //    . " files. Details were written to opus-console.log";
        echo "\n\nDetails were written to opus-console.log";
        $this->resetMode();
        return $runtime;
    }

    private function forceSyncMode() {
        $config = Zend_Registry::get('Zend_Config');
        if (isset($config->runjobs->asynchronous) && $config->runjobs->asynchronous) {
            $this->_syncMode = false;
            $config->runjobs->asynchronous = 0;
            Zend_Registry::set('Zend_Config', $config);
        }
    }

    private function resetMode() {
        if (!$this->_syncMode) {
            $config = Zend_Registry::get('Zend_Config');
            $config->runjobs->asynchronous = 1;
            Zend_Registry::set('Zend_Config', $config);
        }
    }
}

$index = new SolrIndexBuilder;
try {
    $runtime = (int) $index->run();
    echo "\nOperation completed successfully in $runtime seconds.\n";
}
catch (Opus_SolrSearch_Index_Exception $e) {
    echo "\nAn error occurred while indexing.";
    echo "\nError Message: " . $e->getMessage();
    if (!is_null($e->getPrevious())) {
        echo "\nCaused By: " . $e->getPrevious()->getMessage();
    }
    echo "\nStack Trace:\n" . $e->getTraceAsString();
    echo "\n\n";
}
