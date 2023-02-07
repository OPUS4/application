<?php

/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the North Rhine-Westphalian Library Service Center,
 * the Cooperative Library Network Berlin-Brandenburg, the Saarland University
 * and State Library, the Saxon State Library - Dresden State and University
 * Library, the Bielefeld University Library and the University Library of
 * Hamburg University of Technology with funding from the German Research
 * Foundation and the European Regional Development Fund.
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

// Bootstrapping
require_once dirname(__FILE__) . '/../common/bootstrap.php';

use Opus\Common\Config;
use Opus\Common\Document;
use Opus\Common\Model\NotFoundException;

// Get files directory...
$startTime = microtime(true);
$config    = Config::get();
$filesPath = realpath($config->workspacePath . DIRECTORY_SEPARATOR . "files");

if ($filesPath === false || empty($filesPath)) {
    die("ERROR: Failed scanning workspace files path.\n");
}

echo "INFO: Scanning directory '$filesPath'...\n";

// Iterate over all files
$count  = 0;
$errors = 0;

foreach (glob($filesPath . DIRECTORY_SEPARATOR . "*") as $file) {
    if ($count > 0 && $count % 100 === 0) {
        echo "INFO: checked $count entries with " . round($count / (microtime(true) - $startTime)) . " entries/seconds.\n";
    }
    $count++;

    $matches = [];
    if (preg_match('/\/([0-9]+)$/', $file, $matches) !== 1) {
        continue;
    }

    if (! is_dir($file)) {
        echo "ERROR: expected directory: $file\n";
        $errors++;
        continue;
    }

    $id = $matches[1];
    try {
        $d = Document::get($id);
    } catch (NotFoundException $e) {
        echo "ERROR: No document $id found for workspace path '$file'!\n";
        $errors++;
    }
}

echo "INFO: Checked a total of $count entries with " . round($count / (microtime(true) - $startTime)) . " entries/seconds.\n";

if ($errors === 0) {
    exit(0);
}

echo "ERROR: Found $errors ERRORs in workspace files directory '$filesPath'!\n";
exit(1);
