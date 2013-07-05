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
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2009, 2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

// Configure include path.
require_once dirname(__FILE__) . '/../common/bootstrap.php';
set_include_path('.' . PATH_SEPARATOR
        . PATH_SEPARATOR . dirname(dirname(dirname(__FILE__))) . '/scripts/migration/importer'
        . PATH_SEPARATOR . get_include_path());

require_once 'Opus3MigrationValidation.php';
require_once 'Opus3MigrationICL.php';
require_once 'Opus3MigrationDocuments.php';

class Opus3MigrationBatch {

	public function run() {
		$stepsize=50;
		
		try {
			$this->checkParameter();	
		} catch (Exception $e)  {
			print "Aborting migration: " . $e->getMessage() . "\n"; 
			exit(1);
		}
		

		exec(realpath(dirname(__FILE__)) . "/opus3-migration-clean.sh", $output, $exec_return);
		print implode("\n", $output) ."\n";
		if ($exec_return != 0) {
			exit(1);
		}
			
		$validation = new Opus3MigrationValidation();
		try {					
			$validation->run();
		} catch (Exception $e)  {
			print "Aborting migration: " . $e->getMessage() . "\n"; 
			exit(1);
		}

		$migration = new Opus3MigrationICL();
		$migration->run();
		
		$start=1;
		$end = $start + $stepsize - 1;
			
		$status = 1;
		while ($status == 1) {
			$migration = new Opus3MigrationDocuments($start, $end);
			$migration->run();
			$start = $end+1;
			$end = $start + $stepsize - 1;
			$status = $migration->getStatus();
		}
	}
	
	private function checkParameter() {
	        $config = Zend_Registry::get('Zend_Config');
		$filename = $config->migration->file;
		 if (!is_readable($filename)) {
			throw new Exception("Opus3-XML-Dumpfile '$filename' does not exist or is not readable.");
		}
		$path = $config->migration->path;
		if (!is_readable($path)) {		
			throw new Exception("Opus3-Fulltextpath '$path' does not exist or is not readable.");
		}
	}
}


