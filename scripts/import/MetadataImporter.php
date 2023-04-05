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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

require_once dirname(__FILE__) . '/../common/bootstrap.php';
require_once 'Log.php';

use Opus\Import\Xml\MetadataImport;
use Opus\Import\Xml\MetadataImportSkippedDocumentsException;

class MetadataImporter
{
    /** @var object */
    private $console;

    /** @var object */
    private $logfile;

    /**
     * @param array $options
     * @throws MetadataImportSkippedDocumentsException
     */
    public function run($options)
    {
        $consoleConf = ['lineFormat' => '[%1$s] %4$s'];
        $logfileConf = ['append' => false, 'lineFormat' => '%4$s'];

        $this->console = Log::factory('console', '', '', $consoleConf, PEAR_LOG_INFO);

        if (count($options) < 2) {
            $this->console->log('Missing parameter: no file to import.');
            return;
        }

        $logfilePath = 'reject.log';
        if (count($options) > 2) {
            // logfile path is given
            $logfilePath = $options[2];
        }
        $this->logfile = Log::factory('file', $logfilePath, '', $logfileConf, PEAR_LOG_INFO);

        $xmlFile = $options[1];

        $importer = new MetadataImport($xmlFile, true, $this->console, $this->logfile);
        $importer->run();
    }
}

try {
    $importer = new MetadataImporter();
    $importer->run($argv);
} catch (Exception $e) {
    echo "\nAn error occurred while importing: " . $e->getMessage() . "\n\n";
    exit();
}
