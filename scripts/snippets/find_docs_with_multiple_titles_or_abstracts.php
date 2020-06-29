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
 * @copyright   Copyright (c) 2008-2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */


/**
 *
 * Dieses Skript gibt alle IDs der Dokumente zurück, die mehr als einen Titel
 * und/oder Abstract in der Sprache des Dokuments besitzen.
 *
 * Diese Dokumente müssen aktuell manuell behandelt werden, da das Dokument
 * sonst nicht fehlerfrei indexiert werden kann (siehe OPUSVIER-2240).
 *
 */

$updateRequired = 0;

$docfinder = new Opus_DocumentFinder();
foreach ($docfinder->ids() as $docId) {
    $doc = new Opus_Document($docId);

    $numOfTitles = 0;
    foreach ($doc->getTitleMain() as $title) {
        if ($title->getLanguage() === $doc->getLanguage()) {
            $numOfTitles++;
        }
    }

    $numOfAbstracts = 0;
    foreach ($doc->getTitleAbstract() as $abstract) {
        if ($abstract->getLanguage() === $doc->getLanguage()) {
            $numOfAbstracts++;
        }
    }

    if ($numOfTitles > 1 || $numOfAbstracts > 1) {
        $msg = "document #$docId (";
        $opusThreeId = $doc->getIdentifierOpus3();
        if (count($opusThreeId) > 0) {
            $msg .= 'opus3id #' . $opusThreeId[0]->getValue() . ' ';
        }
        $msg .= 'server_state: ' . $doc->getServerState() . ') needs to be updated manually: has';
        if ($numOfTitles > 1) {
            $msg .= " $numOfTitles titles";
        }
        if ($numOfAbstracts > 1) {
            $msg .= " $numOfAbstracts abstracts";
        }
        echo $msg . "\n";
        $updateRequired++;
    }
}

if ($updateRequired == 0) {
    echo "all docs were checked -- nothing to do!\n";
} else {
    echo "$updateRequired docs need to be updated manually!\n";
}

exit();
