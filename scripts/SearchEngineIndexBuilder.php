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
 * @package     Module_Search
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
// Configure include path.
set_include_path('.' . PATH_SEPARATOR
            . PATH_SEPARATOR . dirname(__FILE__)
            . PATH_SEPARATOR . dirname(dirname(__FILE__)) . '/library'
            . PATH_SEPARATOR . get_include_path());

// Zend_Loader is'nt available yet. We have to do a require_once
// in order to find the bootstrap class.
require_once 'Application/Bootstrap.php';

/**
 * Bootstraps and runs an import from Opus3
 *
 * @category    Search
 */
class SearchEngineIndexBuilder extends Application_Bootstrap {

    /**
     * Starts an Opus console.
     *
     * @return void
     */
    public function _run() {

        global $argv, $argc;

    	if (true === in_array('--help', $argv) || true === in_array('-h', $argv)) {
    		echo "Usage: " . $argv[0] . " [starting with ID] [ending with ID] [number of maximum buffered docs]\n";
    		echo "\n";
    		echo "[starting with ID] If system aborted indexing at some ID, you can restart this command by supplying this parameter.\n";
    		echo "It should be the ID where the program stopped before.\n";
    		echo "Default start value is 0.\n";
    		echo "\n";
    		echo "[ending with ID] You can also supply a second ID where the indexer should stop indexing.\n";
    		echo "If you omit this parameter or set it to 0, the indexer will index all remaining documents.\n";
    		echo "\n";
    		echo "[number of maximum buffered docs] sets the number of documents that should get held in memory before writing them to index\n";
    		echo "Low numbers will decrease performance and time amount for indexing, but also decreases the amount of desired memory.\n";
    		echo "A high number can increase performance, but maybe the system will run out of memory and abort indexing.\n";
    		echo "The best value to supply here is to calculate [php memory limit]/[size of biggest pdf document to index].\n";
   		    echo "You should set the memory limit to a value larger than your biggest document!\n";
   		    echo "Default value for maximum buffered docs is 3.\n";
    		exit;
    	}
   		
   		$config = Zend_Registry::get('Zend_Config');

		$searchEngine = $config->searchengine->engine;
		if (empty($searchEngine) === true) {
			$searchEngine = 'Lucene';
		}

    	if ($searchEngine === 'Solr' || true === in_array('--solr', $argv)) {
    	    echo "Building Solr Index...\n";
    	    $indexbuilder = 'Opus_Search_Index_SolrIndexer';
    	}
    	else {
    		echo "Building Lucene Index...\n";
    	    $indexbuilder = 'Opus_Search_Index_Indexer';
    	}

        $docresult = Opus_Document::getAllIds();

        // Evaluate parameters or set them to default values
        $start = 0;
        $end = null;
        $maxBufferedDocs = 3;
        if ($argc >= 2) $start = $argv[1];
        $docsToIndex = count($docresult)-$start;
        if ($argc >= 3) {
        	if ($argv[2] === 0) {
        		$end = null;
        	}
        	else {
        		$end = $argv[2];
        	    $docsToIndex = $end-$start;
        	}
        }
        if ($start === $end) $docsToIndex = 1;
        if ($argc >= 4) $maxBufferedDocs = $argv[3];

		// removes index directory to start from scratch, if no limit is given
        if ($start === 0 && $end === null) {
            $registry = Zend_Registry::getInstance();
            $indexpath = $registry->get('Zend_LuceneIndexPath');
            $fh = opendir($indexpath);
            while (false !== $file = readdir($fh)) {
                @unlink($indexpath . '/' . $file);
            }
            closedir($fh);
            $indexer = new $indexbuilder(true, $maxBufferedDocs);
        }
        else {
        	$indexer = new $indexbuilder(false, $maxBufferedDocs);
        }

        echo date('Y-m-d H:i:s') . " Starting indexing of " . $docsToIndex . " documents\n";
        foreach ($docresult as $row) {
        	if ($start <= $row) {
        		if ($end >= $row || $end === null) {
                    echo "Memory amount: " . round(memory_get_usage()/1024/1024, 2) . " (MB)\n";
                    $docadapter = new Opus_Document( (int) $row);
                    $returnvalue = $indexer->addDocumentToEntryIndex($docadapter);
        	        unset($docadapter);
       		        foreach ($returnvalue as $value) {
       		            echo date('Y-m-d H:i:s') . ": " . $value . "\n";
       		        }
       		        unset($returnvalue);
        		}
            }
        }
        echo date('Y-m-d H:i:s') . " Finished indexing!\n";

        $indexer->finalize();
    }
}

// Start migration
$index = new SearchEngineIndexBuilder;
$index->run(dirname(dirname(__FILE__)), Opus_Bootstrap_Base::CONFIG_TEST,
    dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'config');