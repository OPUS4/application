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
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

// ID of parent collection
$parent_collection_id = 16005;
// file to import
$input_file = '../workspace/tmp/test.txt';
// visibility status of imported collections
$visible = true;

if (!file_exists($input_file)) {
    echo "Error: input file $input_file does not exist\n";
    exit();
}

if (!is_readable($input_file)) {
    echo "Error: input file $input_file is not readable\n";
    exit();
}

$root_collection = null;
try {
    $root_collection = new Opus_Collection($parent_collection_id);
}
catch (Opus_Model_NotFoundException $e) {
    echo "Error: collection with id $parent_collection_id does not exist\n";
    exit();
}

if (!is_null($root_collection)) {

    $line_count = 0;
    $lines_imported = 0;
    foreach (file($input_file) as $line) {
        $line_count++;
        if (trim($line) === '') {
            continue;
        }
        $collection = new Opus_Collection();
        $parts = explode('|', $line);
        if (count($parts) > 2) {
            echo "Warning: ignore line number $line_count (more than one | character exists): $line\n";
            continue;
        }
        if (count($parts) < 2) {
            echo "Warning: ignore line number $line_count (| character does not exist): $line\n";
            continue;
        }

        $collection->setName(trim($parts[0]));
        $collection->setNumber(trim($parts[1]));
        $collection->setVisible($visible);
        $root_collection->addLastChild($collection);
        $root_collection->store();
        $lines_imported++;
    }

    echo "$lines_imported collections were successfully imported\n";
}


exit();