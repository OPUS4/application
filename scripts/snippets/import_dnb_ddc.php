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

use Opus\Common\Collection;
use Opus\Common\CollectionRole;
use Opus\Db\TableGateway;

/**
 * script that imports collections from a text file
 * file format: each collection (name and number) on a separate line
 * collection name and number are separated by | character
 */

// file to import
$inputFile = '../workspace/tmp/ddc_dnb.txt';
// visibility status of imported collections
$visible        = true;
$fieldSeparator = '	';

if (! file_exists($inputFile)) {
    echo "Error: input file $inputFile does not exist\n";
    exit();
}

if (! is_readable($inputFile)) {
    echo "Error: input file $inputFile is not readable\n";
    exit();
}

// find next valid position for collection role
$table    = TableGateway::getInstance(CollectionRole::getTableGatewayClass());
$select   = $table->select()->from($table, ['MAX(position) AS max_position']);
$row      = $table->fetchRow($select);
$position = (int) $row->max_position + 1;

// create root collection
$collectionRole = CollectionRole::new();
$collectionRole->setPosition($position);
$collectionRole->setName('ddc_dnb');
$collectionRole->setOaiName('ddc_dnb');
$collectionRole->setVisible(true);
$collectionRole->setVisibleBrowsingStart(true);
$collectionRole->setDisplayBrowsing('Number,Name');
$collectionRole->setDisplayFrontdoor('Number,Name');
$collectionRole->setVisibleFrontdoor(true);
$collectionRole->setVisibleOai(true);
$collectionRoleId = $collectionRole->store();

$rootCollection = Collection::new();
$rootCollection->setPositionKey('Root');
$rootCollection->setVisible(true);
$rootCollection->setRoleId($collectionRoleId);
$rootCollection->store();

if ($rootCollection !== null) {
    $lineCount     = 0;
    $linesImported = 0;
    foreach (file($inputFile) as $line) {
        $lineCount++;
        if (trim($line) === '') {
            continue;
        }
        $parts = explode($fieldSeparator, $line);
        if (count($parts) > 2) {
            echo "Warning: ignore line number $lineCount (more than one field separating character exists): $line\n";
            continue;
        }
        if (count($parts) < 2) {
            echo "Warning: ignore line number $lineCount (field separating character does not exist): $line\n";
            continue;
        }

        $collection = Collection::new();
        $collection->setNumber(trim($parts[0]));
        $collection->setOaiSubset(trim($parts[0]));
        $collection->setName(trim($parts[1]));
        $collection->setVisible($visible);
        $rootCollection->addLastChild($collection);
        $rootCollection->store();
        $linesImported++;
    }

    echo "$linesImported collections were successfully imported\n";
}

exit();
