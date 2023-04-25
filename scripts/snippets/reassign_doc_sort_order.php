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

use Opus\Common\Document;
use Opus\Common\Series;

/**
 * Setzt die interne Sortierreihenfolge (doc_sort_order) für die einer
 * Schriftenreihe zugeordneten Dokumente auf Basis der vergebenenen Bandnummern
 * neu.
 *
 * Dazu werden für eine Schriftenreihe alle Bandnummern der zugeordneten
 * Dokumente ermittelt. Die Dokumente werden anschließend der Schriftenreihe
 * neu zugeordnet. Als Sortierkriterium wird dabei die existierende Bandnummer
 * betrachtet. Sind alle Bandnummern numerisch, so wird numerisch nach
 * Bandnummer sortiert; andernfalls lexikographisch nach Bandnummer.
 */

foreach (Series::getAll() as $series) {
    echo "\nreassign doc_sort_order for documents in series #" . $series->getId() . ': ';
    $docIds = $series->getDocumentIds();
    if (empty($docIds)) {
        echo "no documents found -- nothing to do\n";
        continue;
    }
    echo count($docIds) . " documents found\n";

    $seriesNumbers = [];
    foreach ($docIds as $docId) {
        $doc = Document::get($docId);
        foreach ($doc->getSeries() as $docSeries) {
            if ($docSeries->getModel()->getId() === $series->getId()) {
                $seriesNumbers[$docId] = $docSeries->getNumber();
            }
        }
    }

    $allNumerics = true;
    foreach ($seriesNumbers as $docId => $seriesNumber) {
        if (! is_numeric($seriesNumber)) {
            $allNumerics = false;
            break;
        }
    }

    if ($allNumerics) {
        echo "sorting documents in series #" . $series->getId() . " numerically\n";
        if (! asort($seriesNumbers, SORT_NUMERIC)) {
            echo "Error while sorting docs -- skip series #" . $series->getId() . "\n";
            break;
        }
    } else {
        echo "sorting documents in series #" . $series->getId() . " lexicographically\n";
        if (! asort($seriesNumbers, SORT_STRING)) {
            echo "Error while sorting docs -- skip series #" . $series->getId() . "\n";
            break;
        }
    }

    $seriesCounter = 0;
    foreach ($seriesNumbers as $docId => $seriesNumber) {
        $doc       = Document::get($docId);
        $allSeries = $doc->getSeries();
        $doc->setSeries([]);
        $doc->store();
        foreach ($allSeries as $docSeries) {
            $seriesInstance = $docSeries->getModel();
            if ($seriesInstance->getId() === $series->getId()) {
                echo "reassign doc_sort_order for doc #" . $doc->getId() . " (series number: "
                    . $docSeries->getNumber() . ") -- old / new doc_sort_order: " . $docSeries->getDocSortOrder()
                    . " / " . $seriesCounter . "\n";
                $doc->addSeries($seriesInstance)->setNumber($docSeries->getNumber())->setDocSortOrder($seriesCounter++);
            } else {
                $doc->addSeries($seriesInstance)->setNumber(
                    $docSeries->getNumber()
                )->setDocSortOrder(
                    $docSeries->getDocSortOrder()
                );
            }
        }
        $doc->store();
    }
}

exit();
