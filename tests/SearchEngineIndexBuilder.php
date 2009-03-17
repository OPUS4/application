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
		$indexer = new Opus_Search_Index_Indexer();

        $docresult = Opus_Document::getAllIds();

        echo date('Y-m-d H:i:s') . " Start\n";
        foreach ($docresult as $row) {
            $docadapter = new Opus_Document( (int) $row);
        	$indexer->addDocumentToEntryIndex($docadapter);
       		echo date('Y-m-d H:i:s') . ": Indexed Metadata for " . $row . "\n";
        }
        echo date('Y-m-d H:i:s') . ' Stop';
        
        $indexer->finalize();
    }
}

// Start migration
$index = new SearchEngineIndexBuilder;
$index->run(dirname(dirname(__FILE__)), Opus_Bootstrap_Base::CONFIG_TEST,
    dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'config');