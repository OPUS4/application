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
 * Dieses Skript findet alle Dokumente mit ServerState=published, deren ServerDateModified im Solr-Index kleiner ist
 * als das Datum in der Datenbank. Ist ein Dokument nicht im Index vorhanden, wird eine entsprechende
 * Fehlermeldung pro Dokument ausgegeben.
 *
 * Siehe dazu auch das Ticket OPUSVIER-2853.
 *
 */
$numOfModified = 0;
$numOfErrors = 0;
$finder = new Opus_DocumentFinder();
$finder->setServerState('published');
foreach ($finder->ids() as $docId) {
    // check if document with id $docId is already persisted in search index
    $search = Opus\Search\Service::selectSearchingService();
    $query  = Opus\Search\QueryFactory::selectDocumentById($search, $docId);

    if ($search->customSearch($query)->getAllMatchesCount() != 1) {
        echo "ERROR: document # $docId is not stored in search index\n";
        $numOfErrors++;
    } else {
        $result = $search->getResults();
        $solrModificationDate = $result[0]->getServerDateModified();
        $document = new Opus_Document($docId);
        $docModificationDate = $document->getServerDateModified()->getUnixTimestamp();
        if ($solrModificationDate != $docModificationDate) {
            $numOfModified++;
            echo "document # $docId is modified\n";
        }
    }
}
if ($numOfErrors > 0) {
    echo "$numOfErrors missing documents were found\n";
    echo "$numOfModified modified documents were found\n";
} else {
    echo "no missing or modified documents were found\n";
}

exit();
