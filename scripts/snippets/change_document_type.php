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

/**
 * TODO should this be part of administration, part of opus4 tool
 */
if (basename(__FILE__) !== basename($argv[0])) {
    echo "script must be executed directy (not via opus-console)\n";
    exit;
}

require_once dirname(__FILE__) . '/../common/bootstrap.php';

use Opus\Common\Document;
use Opus\Common\Repository;

$options = getopt('', ['dryrun', 'from:', 'to:']);

$dryrun = isset($options['dryrun']);

if (! isset($options['from']) || empty($options['from']) || ! isset($options['to']) || empty($options['to'])) {
    echo "Usage: {$argv[0]} --from <current doc type> --to <target doc type> (--dryrun)\n";
    echo "--from and --to must be provided.\n";
    exit;
}

$from = $options['from'];
$to   = $options['to'];

if ($dryrun) {
    writeMessage("TEST RUN: NO DATA WILL BE MODIFIED");
}

$docFinder = Repository::getInstance()->getDocumentFinder();
$docIds    = $docFinder->setDocumentType($from)->getIds();

writeMessage(count($docIds) . " documents found");

foreach ($docIds as $docId) {
    $doc = Document::get($docId);
    $doc->setType($to);
    if (! $dryrun) {
        $doc->store();
    }
    writeMessage("Document #$docId changed from '$from' to '$to'");
}

/**
 * @param string $message
 */
function writeMessage($message)
{
    echo "$message\n";
}
