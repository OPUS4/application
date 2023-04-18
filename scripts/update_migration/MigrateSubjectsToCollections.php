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

// TODO move code into classes

// Bootstrapping.
require_once dirname(__FILE__) . '/../common/bootstrap.php';

use Opus\Common\Collection;
use Opus\Common\CollectionRole;
use Opus\Common\Document;
use Opus\Common\DocumentInterface;
use Opus\Common\EnrichmentKey;
use Opus\Common\EnrichmentKeyInterface;
use Opus\Common\Model\NotFoundException;
use Opus\Common\Repository;

// Parse arguments.
$argc = $GLOBALS['argc'];
$argv = $GLOBALS['argv'];

if (count($argv) !== 2) {
    echo "usage: " . __FILE__ . " logfile.log\n";
    exit(-1);
}

echo "\nmigrating classification subjects -- can take a while";

// Initialize logger.
$logfileName = $argv[1];

/**
 * TODO Not using LogService, because file is written to working directory (OPUSVIER-4289)
 */
$logfile   = @fopen($logfileName, 'a', false);
$writer    = new Zend_Log_Writer_Stream($logfile);
$formatter = new Zend_Log_Formatter_Simple('%timestamp% %priorityName%: %message%' . PHP_EOL);
$writer->setFormatter($formatter);
$logger = new Zend_Log($writer);

// load collections (and check existence)
$mscRole = CollectionRole::fetchByName('msc');
if (! is_object($mscRole)) {
    $logger->warn("MSC collection does not exist.  Cannot migrate SubjectMSC.");
}

$ddcRole = CollectionRole::fetchByName('ddc');
if (! is_object($ddcRole)) {
    $logger->warn("DDC collection does not exist.  Cannot migrate SubjectDDC.");
}

// create enrichment keys (if neccessary)
createEnrichmentKey('MigrateSubjectMSC');
createEnrichmentKey('MigrateSubjectDDC');

// Iterate over all documents.
$docFinder          = Repository::getInstance()->getDocumentFinder();
$changedDocumentIds = [];
foreach ($docFinder->getIds() as $docId) {
    $doc = null;
    try {
        $doc = Document::get($docId);
    } catch (NotFoundException $e) {
        continue;
    }

    $removeMscSubjects = [];
    $removeDdcSubjects = [];
    try {
        if (is_object($mscRole)) {
            $removeMscSubjects = migrateSubjectToCollection($doc, 'msc', $mscRole->getId(), 'MigrateSubjectMSC', $logger);
        }

        if (is_object($ddcRole)) {
            $removeDdcSubjects = migrateSubjectToCollection($doc, 'ddc', $ddcRole->getId(), 'MigrateSubjectDDC', $logger);
        }
    } catch (Exception $e) {
        $logger->err("fatal error while parsing document $docId: " . $e);
        continue;
    }

    if (count($removeMscSubjects) > 0 || count($removeDdcSubjects) > 0) {
        $changedDocumentIds[] = $docId;

        try {
            $doc->unregisterPlugin('Opus\Document\Plugin\Index');
            $doc->store();
            $logger->info("changed document $docId");
        } catch (Exception $e) {
            $logger->err("fatal error while STORING document $docId: " . $e);
        }
    }
}
$logger->info("changed " . count($changedDocumentIds) . " documents: " . implode(",", $changedDocumentIds));

/**
 * @param DocumentInterface $doc
 * @param int               $collectionId
 * @return bool
 */
function checkDocumentHasCollectionId($doc, $collectionId)
{
    foreach ($doc->getCollection() as $c) {
        if ($c->getId() === $collectionId) {
            return true;
        }
    }
    return false;
}

/**
 * @param DocumentInterface $doc
 * @param string            $subjectType
 * @param int               $roleId
 * @param string            $eKeyName
 * @param Zend_Log          $logger
 * @return array
 */
function migrateSubjectToCollection($doc, $subjectType, $roleId, $eKeyName, $logger)
{
    $logPrefix = sprintf("[docId % 5d] ", $doc->getId());

    $keepSubjects   = [];
    $removeSubjects = [];
    foreach ($doc->getSubject() as $subject) {
        $keepSubjects[$subject->getId()] = $subject;

        $type  = $subject->getType();
        $value = $subject->getValue();

        if ($type !== $subjectType) {
            // $logger->debug("$logPrefix  Skipping subject (type '$type', value '$value')");
            continue;
        }

        // From now on, every subject will be migrated
        $keepSubjects[$subject->getId()] = false;
        $removeSubjects[]                = $subject;

        // check if (unique) collection for subject value exists
        $collections = Collection::fetchCollectionsByRoleNumber($roleId, $value);
        if (! is_array($collections) || count($collections) < 1) {
            $logger->warn("$logPrefix  No collection found for value '$value' -- migrating to enrichment $eKeyName.");
            // migrate subject to enrichments
            $doc->addEnrichment()
                    ->setKeyName($eKeyName)
                    ->setValue($value);
            continue;
        }

        if (count($collections) > 1) {
            $logger->warn("$logPrefix  Ambiguous collections for value '$value' -- migrating to enrichment $eKeyName.");
            // migrate subject to enrichments
            $doc->addEnrichment()
                    ->setKeyName($eKeyName)
                    ->setValue($value);
            continue;
        }

        $collection = $collections[0];
        if ($collection->isRoot()) {
            $logger->warn(
                "$logPrefix  No non-root collection found for value '$value' -- migrating to enrichment $eKeyName."
            );
            // migrate subject to enrichments
            $doc->addEnrichment()
                    ->setKeyName($eKeyName)
                    ->setValue($value);
            continue;
        }

        $collectionId = $collection->getId();
        // check if document already belongs to this collection
        if (checkDocumentHasCollectionId($doc, $collectionId)) {
            // nothing to do
            $logger->info(
                "$logPrefix  Migrating subject (type '$type', value '$value') -- collection already assigned to "
                . "collection $collectionId."
            );
            continue;
        }

        // migrate subject to collections
        $logger->info("$logPrefix  Migrating subject (type '$type', value '$value') to collection $collectionId.");
        $doc->addCollection($collection);
    }

    if (count($removeSubjects) > 0) {
        // debug: removees
        foreach ($removeSubjects as $r) {
            $logger->debug(
                "$logPrefix  Removing subject (type '" . $r->getType() . "', value '" . $r->getValue() . "')"
            );
        }

        $newSubjects = array_filter(array_values($keepSubjects));
        foreach ($newSubjects as $k) {
            $logger->debug(
                "$logPrefix  Keeping subject (type '" . $k->getType() . "', value '" . $k->getValue() . "')"
            );
        }

        $doc->setSubject($newSubjects);
    }

    return $removeSubjects;
}

/**
 * @param string $name
 * @return EnrichmentKeyInterface
 * @throws NotFoundException
 */
function createEnrichmentKey($name)
{
    try {
        $eKey = EnrichmentKey::new();
        $eKey->setName($name)->store();
    } catch (Exception $e) {
    }

    return EnrichmentKey::get($name);
}

echo "\nConsult the log file $argv[1] for full details\n";
exit();
