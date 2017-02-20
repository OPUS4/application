#!/usr/bin/env php
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
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2010-2015, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

// Bootstrapping
require_once dirname(__FILE__) . '/common/bootstrap.php';

/**
 * Indexes all or a range of documents.
 *
 * If all documents are indexed the index is cleared first.
 *
 * TODO move to class and unit test
 */
class SolrIndexBuilder {

    /**
     * Start of document ID range for indexing (first command line parameter).
     * @var int
     */
    private $_start = null;

    /**
     * End of document ID range for indexing (second command line parameter).
     * @var int
     */
    private $_end = null;

    /**
     * Flag for deleting all documents from index before indexing.
     * @var bool
     */
    private $_deleteAllDocs = false;

    /**
     * Temporary variable for storing sync mode.
     * @var bool
     */
    private $_syncMode = true;

    /**
     * Flag for showing help information (command line parameter '--help' or '-h')
     * @var bool
     */
    private $_showHelp = false;

    /**
     * Flag for deleting document xml cache before indexing.
     * @var bool
     */
    private $_clearCache = false;

    /**
     * Flag for debug output.
     * @var bool
     */
    private $_debugEnabled = false;

    /**
     * Prints a help message to the console.
     */
    private function printHelpMessage($argv) {
        $text = <<<EOT
OPUS 4 SolrIndexBuilder

This program can be used to build up an initial Solr index (e.g., useful when
migrating instances).

Usage:

  php $argv[0] [-c] [Start ID] [End ID]

  [Start ID] ID of document where indexing should start
  [End ID]   ID of document where indexing should stop

If only the starting ID is specified all remaining documents with higher IDs
will be indexed.

If no ID is specified the entire index will be cleared before reindexing all
documents.

Options:
  -c : Clear document XML cache entries before indexing
  -h : Shows this help message (--help)
  -d : Enables debug output (--debug) - NOT IMPLEMENTED YET

EOT;
        $this->write($text . PHP_EOL);
    }

    /**
     * Evaluates command line arguments.
     */
    private function evaluateArguments($argc, $argv) {
        $options = getopt("cdh", array('help', 'debug'));

        if (array_key_exists('debug', $options) || array_key_exists('d', $options)) {
            $this->_debugEnabled = true;
        }

        if (array_key_exists('help', $options) || array_key_exists('h', $options)) {
            $this->_showHelp = true;
            return;
        }

        if (true === array_key_exists('c', $options)) {
            $this->_clearCache = true;
        }

        if ($argc == 2)
        {
            $start = $argv[$argc - 1];
        }
        else if ($argc > 2)
        {
            $start = $argv[$argc - 2];
            $end = $argv[$argc - 1];
        }

        if (is_numeric($start) && ctype_digit($start))
        {
            $this->_start = $start;
        }

        if (is_numeric($end) && ctype_digit($end))
        {
            $this->_end = $end;
        }

        // check if only end is set (happens when options are used)
        if (is_null($this->_start) && !is_null($this->_end)) {
            $this->_start = $this->_end;
            $this->_end = null;
        }

        if (is_null($this->_start) && is_null($this->_end)) {
            // TODO gesondertes Argument für Indexdeletion einführen
            $this->_deleteAllDocs = true;
        }
    }

    /**
     * Starts an Opus console.
     */
    public function run($argc, $argv) {
        $this->evaluateArguments($argc, $argv);

        if ($this->_showHelp) {
            $this->printHelpMessage($argv);
            return;
        }

        if (!is_null($this->_end))
        {
            echo PHP_EOL . "Indexing documents {$this->_start} to {$this->_end} ..." . PHP_EOL;
        }
        else if (!is_null($this->_start))
        {
            echo PHP_EOL . "Indexing documents starting at ID = {$this->_start} ..." . PHP_EOL;
        }
        else
        {
            echo PHP_EOL . 'Indexing all documents ...' . PHP_EOL;
        }

        try {
            $runtime = $this->index($this->_start, $this->_end);
            echo PHP_EOL . "Operation completed successfully in $runtime seconds." . PHP_EOL;
        }
        catch (Opus_Search_Exception $e) {
            echo PHP_EOL . "An error occurred while indexing.";
            echo PHP_EOL . "Error Message: " . $e->getMessage();
            if (!is_null($e->getPrevious())) {
                echo PHP_EOL . "Caused By: " . $e->getPrevious()->getMessage();
            }
            echo PHP_EOL . "Stack Trace:" . PHP_EOL . $e->getTraceAsString();
            echo PHP_EOL . PHP_EOL;
        }
    }

    private function index($startId, $endId) {
        $this->forceSyncMode();

        $docIds = $this->getDocumentIds($startId, $endId);

        $indexer = Opus_Search_Service::selectIndexingService( 'indexBuilder' );

        if ($this->_deleteAllDocs) {
            echo 'Removing all documents from the index ...' . PHP_EOL;
            $indexer->removeAllDocumentsFromIndex();
        }

        echo date('Y-m-d H:i:s') . " Start indexing of " . count($docIds) . " documents.\n";
        $numOfDocs = 0;
        $runtime = microtime(true);

        $docs = array();

        // measure time for each document

        $cache = new Opus_Model_Xml_Cache();

        foreach ($docIds as $docId) {
            $timeStart = microtime(true);

            if ($this->_clearCache) {
                $cache->removeAllEntriesWhereDocumentId($docId);
            }

            $doc = new Opus_Document($docId);

            // dirty hack: disable implicit reindexing of documents in case of cache misses
            $doc->unregisterPlugin('Opus_Document_Plugin_Index');

            $docs[] = $doc;

            $timeDelta = microtime(true) - $timeStart;
            if ($timeDelta > 30) {
                echo date('Y-m-d H:i:s') . " WARNING: Indexing document $docId took $timeDelta seconds.\n";
            }

            $numOfDocs++;

            if ($numOfDocs % 10 == 0) {
                $this->addDocumentsToIndex( $indexer, $docs );
                $docs = array();
                $this->outputProgress($runtime, $numOfDocs);
            }
        }

        // Index leftover documents
        if (count($docs) > 0) {
            $this->addDocumentsToIndex( $indexer, $docs );
            $this->outputProgress($runtime, $numOfDocs);
        }

        $runtime = microtime(true) - $runtime;
        echo PHP_EOL . date('Y-m-d H:i:s') . ' Finished indexing.' . PHP_EOL;
        // new search API doesn't track number of indexed files, but issues are kept written to log file
        //echo "\n\nErrors appeared in " . $indexer->getErrorFileCount() . " of " . $indexer->getTotalFileCount()
        //    . " files. Details were written to opus-console.log";
        echo PHP_EOL . PHP_EOL . 'Details were written to opus-console.log';

        $this->resetMode();

        return $runtime;
    }

    /**
     * Returns IDs for published documents in range.
     *
     * @param $start int Start of ID range
     * @param $end int End of ID range
     * @return array Array of document IDs
     */
    private function getDocumentIds($start, $end) {
        $finder = new Opus_DocumentFinder();

        $finder->setServerState('published');

        if (isset($start)) {
            $finder->setIdRangeStart($start);
        }

        if (isset($end)) {
            $finder->setIdRangeEnd($end);
        }

        return $finder->ids();
    }

    /**
     * Output current processing status and performance.
     *
     * @param $runtime long Time of start of processing
     * @param $numOfDocs Number of processed documents
     */
    private function outputProgress($runtime, $numOfDocs) {
        $memNow = round(memory_get_usage() / 1024 / 1024);
        $memPeak = round(memory_get_peak_usage() / 1024 / 1024);

        $deltaTime = microtime(true) - $runtime;
        $docPerSecond = round($deltaTime) == 0 ? 'inf' : round($numOfDocs / $deltaTime, 2);
        $secondsPerDoc = round($deltaTime / $numOfDocs, 2);

        echo date('Y-m-d H:i:s') . " Stats after $numOfDocs documents -- memory $memNow MB,"
            . " peak memory $memPeak (MB), $docPerSecond docs/second, $secondsPerDoc seconds/doc" . PHP_EOL;
    }

    private function addDocumentsToIndex($indexer, $docs) {
        try {
            $indexer->addDocumentsToIndex( $docs );
        }
        catch ( Opus_Search_Exception $e ) {
            // echo date('Y-m-d H:i:s') . " ERROR: Failed indexing document $docId.\n";
            echo date('Y-m-d H:i:s') . "        {$e->getMessage()}\n";

        }
        catch ( Opus_Storage_Exception $e ) {
            // echo date('Y-m-d H:i:s') . " ERROR: Failed indexing unavailable file on document $docId.\n";
            echo date('Y-m-d H:i:s') . "        {$e->getMessage()}\n";

        }
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

    private function write($str) {
        echo $str;
    }

}

/**
 * Main code of index builder script.
 */
global $argc, $argv;

$builder = new SolrIndexBuilder();
$builder->run($argc, $argv);
