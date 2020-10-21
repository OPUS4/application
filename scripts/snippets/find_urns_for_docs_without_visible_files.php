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
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @copyright   Copyright (c) 2008-2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Document;
use Opus\DocumentFinder;

/**
 * Dieses Script sucht Dokumente ohne sichtbare Dateien, fuer die bereits
 * eine URN vergeben wurde.
 *
 * TODO integrity check - make part of tools (console, administration)
 */

$updateRequired = 0;

$docfinder = new DocumentFinder();
$docfinder->setIdentifierTypeExists('urn');

echo "checking documents...\n";
foreach ($docfinder->ids() as $docId) {
    $doc = Document::get($docId);

    $numVisibleFiles = 0;
    foreach ($doc->getFile() as $file) {
        if ($file->getVisibleInOai() == 1) {
            $numVisibleFiles++;
        }
    }

    if ($numVisibleFiles > 0) {
        continue;
    }

    echo "-- document $docId has an URN " . $doc->getIdentifierUrn(0)->getValue() . ", but no visible files\n";
}

if ($updateRequired == 0) {
    echo "all docs were checked -- nothing to do!\n";
} else {
    echo "$updateRequired docs need to be updated manually!\n";
}

exit();
