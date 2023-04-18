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

use Opus\Common\Collection;
use Opus\Common\CollectionRole;
use Opus\Common\CollectionRoleInterface;
use Opus\Common\Document;
use Opus\Common\Repository;
use Opus\Common\Series;

class FindMissingSeriesNumbers
{
    /** @var Zend_Log */
    private $logger;

    /** @var CollectionRoleInterface  */
    private $seriesRole;

    /**
     * @param string $logfile
     */
    public function __construct($logfile)
    {
        $this->seriesRole = CollectionRole::fetchByName('series');
        $this->initLogger($logfile);
    }

    /**
     * Initialise the logger with the given file.
     *
     * TODO Not using LogService, because file is written to working directory (OPUSVIER-4289)
     *
     * @param string $logfileName
     */
    private function initLogger($logfileName)
    {
        $logfile   = @fopen($logfileName, 'a', false);
        $writer    = new Zend_Log_Writer_Stream($logfile);
        $formatter = new Zend_Log_Formatter_Simple('%priorityName%: %message%' . PHP_EOL);
        $writer->setFormatter($formatter);
        $this->logger = new Zend_Log($writer);
    }

    /**
     * Für jede Collection der Collection Role series wird eine neue Schriftenreihe
     * Series angelegt, wobei der Name, die Sichtbarkeit und die Sorierreihenfolge
     * übernommen wird.
     *
     * Die Wurzel-Collection der Collection Role series wird nicht betrachtet.
     *
     * @return int number of collections that were migrated
     */
    private function migrateCollectionToSeries()
    {
        $numOfCollectionsMigrated = 0;
        foreach (Collection::fetchCollectionsByRoleId($this->seriesRole->getId()) as $collection) {
            // ignore root collection (does not have valid data and associated documents)
            if ($collection->isRoot()) {
                continue;
            }
            $series = Series::get(Series::createRowWithCustomId($collection->getId()));
            $series->setTitle($collection->getName());
            $series->setVisible($collection->getVisible());
            $series->setSortOrder($collection->getSortOrder());
            $series->store();
            $this->logger->info('created series with id #' . $collection->getId());
            $numOfCollectionsMigrated++;
        }
        return $numOfCollectionsMigrated;
    }

    /**
     * Im Rahmen der Zuweisung von Dokumenten, die Collections der Collection Role
     * series zugeordnet sind, müssen verschiedene Konflikte behandelt werden.
     *
     * Im Folgenden werden nur Dokumente betrachtet, die mindestens einer Collection
     * der Collection Role series (kurz: series-Collection) zugeordnet sind.
     *
     * Fall 1 (Dokumente ohne IdentifierSerial):
     * Da die Bandnummer einer Schriftenreihe Series obligatorisch ist, können
     * Dokumente ohne IdentifierSerial nicht migriert werden. Sie verbleiben
     * unangetastet. Die Zuweisung(en) zu series-Collection(s) wird (werden) nicht
     * verändert.
     *
     * Fall 2 (Dokumente mit mehr als einem IdentifierSerial):
     * Da ein Dokument pro Schriftenreihe nur eine Bandnummer besitzen kann, können
     * Dokumente mit mehr als einem Wert für das Feld IdentifierSerial nicht
     * migriert werden. Sie verbleiben unangetastet. Die Zuweisung(en) zu
     * series-Collection(s) wird (werden) nicht verändert.
     *
     * Fall 3 (Dokumente mit einem IdentifierSerial):
     * Da in einer Schriftenreihe nicht zwei Dokumente mit der gleichen Bandnummer
     * existieren können, muss beim Zuweisen von Dokumenten darauf geachtet werden,
     * dass eine Bandnummer nicht mehrfach vergeben wird.
     * Wird versucht ein Dokument zu einer Schriftenreihe mit einer bereits
     * in Benutzung befindlichen Bandnummer zuzuweisen, so wird die Zuweisung
     * nicht durchgeführt. Die Zuweisung des Dokuments zur series-Collection wird
     * in diesem Fall unverändert beibehalten.
     *
     * Im Falle der erfolgreichen Zuweisung des Dokuments zu einer Schriftenreihe
     * wird die Verknüpfung mit der korrespondierenden series-Collection
     * entfernt. Außerdem wird das Feld IdentifierSerial entfernt.
     *
     * @return array an array that contains both the number of conflicts found and
     * the number of documents that were successfully migrated
     */
    private function migrateDocuments()
    {
        $numOfConflicts    = 0;
        $numOfDocsMigrated = 0;

        $finder = Repository::getInstance()->getDocumentFinder();
        $finder->setCollectionRoleId($this->seriesRole->getId());

        $serialIdsInUse = [];
        foreach ($finder->getIds() as $docId) {
            $doc            = Document::get($docId);
            $serialIds      = $doc->getIdentifierSerial();
            $numOfSerialIds = count($serialIds);

            if ($numOfSerialIds === 0) {
                $this->logger->warn("doc #$docId : does not have a field IdentifierSerial -- leave it untouched");
                $numOfConflicts++;
                continue;
            }

            if ($numOfSerialIds > 1) {
                $this->logger->warn(
                    "doc #$docId : has $numOfSerialIds values for field IdentifierSerial -- leave it untouched"
                );
                $numOfConflicts++;
                continue;
            }

            $serialId             = $serialIds[0]->getValue();
            $remainingCollections = [];

            foreach ($doc->getCollection() as $collection) {
                // only consider collection in collection role series
                if ($collection->getRoleId() !== $this->seriesRole->getId()) {
                    array_push($remainingCollections, $collection);
                } else {
                    $collectionId = $collection->getId();
                    if (! $collection->isRoot()) {
                        // check for conflict
                        if (
                            array_key_exists($collectionId, $serialIdsInUse)
                                && in_array($serialId, $serialIdsInUse[$collectionId])
                        ) {
                            // conflict was found: serialId for series $collectionId already in use
                            $this->logger->warn(
                                "doc #$docId : could not assign to series #$collectionId: "
                                . "value $serialId already in use"
                            );
                            $this->logger->warn(
                                "doc #$docId : leave assignment to collection #$collectionId untouched"
                            );
                            array_push($remainingCollections, $collection);
                            $numOfConflicts++;
                        } else {
                            // no conflict
                            $series = Series::get($collectionId);
                            $doc->addSeries($series)->setNumber($serialId);
                            $doc->setIdentifierSerial([]);

                            // mark usage of serialId for collection $collectionId
                            if (array_key_exists($collectionId, $serialIdsInUse)) {
                                array_push($serialIdsInUse[$collectionId], $serialId);
                            } else {
                                $serialIdsInUse[$collectionId] = [$serialId];
                            }
                            $this->logger->info(
                                "doc #$docId : assign document to series #$collectionId with value $serialId"
                            );
                            $this->logger->info("doc #$docId : removed assignment from collection #$collectionId");
                            $this->logger->info(
                                "doc #$docId : removed field IdentifierSerial with value " . $serialId
                            );
                            $numOfDocsMigrated++;
                        }
                    } else {
                        // series root collection assignment will not be migrated
                        $this->logger->warn(
                            "doc #$docId : is assigned to root collection #$collectionId of collection role series:"
                            . " leave assignment untouched"
                        );
                        array_push($remainingCollections, $collection);
                        $numOfConflicts++;
                    }
                }
            }

            $doc->setCollection($remainingCollections);
            $doc->unregisterPlugin('Opus_Document_Plugin_Index');
            $doc->store();
        }

        return ['numOfConflicts' => $numOfConflicts, 'numOfDocsMigrated' => $numOfDocsMigrated];
    }

    private function hideCollectionRoleSeries()
    {
        $this->seriesRole->setVisible(0);
        $this->seriesRole->setVisibleBrowsingStart(0);
        $this->seriesRole->setVisibleFrontdoor(0);
        $this->seriesRole->setVisibleOai(0);
        $this->seriesRole->store();
        $this->logger->info("set visibility status of collection role series to unvisible");
    }

    /**
     * @return array
     */
    public function run()
    {
        $numOfCollectionsMigrated = $this->migrateCollectionToSeries();
        $result                   = $this->migrateDocuments();
        $this->hideCollectionRoleSeries();
        return [$result['numOfConflicts'], $numOfCollectionsMigrated, $result['numOfDocsMigrated']];
    }
}

if ($argc < 2) {
    echo "missing argument: logfile\n";
    exit;
}

echo "\nmigrating series -- can take a while\n";

$numbers = new FindMissingSeriesNumbers($argv[1]);
$result  = $numbers->run();

if ($result[0] > 0 || $result[1] > 0 || $result[2] > 0) {
    echo $result[0] . ' conflicts were found while series migration';
    echo "\n" . $result[1] . ' collections were migrated into series';
    echo "\n" . $result[2] . ' documents were migrated into series';
    echo "\nConsult the log file $argv[1] for full details\n";
}
