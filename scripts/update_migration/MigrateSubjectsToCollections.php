#!/usr/bin/env php5
<?php

/** This file is part of OPUS. The software OPUS has been originally developed
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
 * @author      Thoralf Klein <tklein@zib.de>
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 **/

// Bootstrapping.
require_once dirname(__FILE__) . '/../common/bootstrap.php';

// Parse arguments.
global $argc, $argv;

if (count($argv) != 2) {
    echo "usage: " . __FILE__ . " logfile.log\n";
    exit(-1);
}

echo "\nmigrating classification subjects -- can take a while";

// Initialize logger.
$logfileName = $argv[1];

$logfile = @fopen($logfileName, 'a', false);
$writer = new Zend_Log_Writer_Stream($logfile);
$formatter = new Zend_Log_Formatter_Simple('%timestamp% %priorityName%: %message%' . PHP_EOL);
$writer->setFormatter($formatter);
$logger = new Zend_Log($writer);


// load collections (and check existence)
$mscRole = Opus_CollectionRole::fetchByName('msc');
if (! is_object($mscRole)) {
    $logger->warn("MSC collection does not exist.  Cannot migrate SubjectMSC.");
}

$ddcRole = Opus_CollectionRole::fetchByName('ddc');
if (! is_object($ddcRole)) {
    $logger->warn("DDC collection does not exist.  Cannot migrate SubjectDDC.");
}

// create enrichment keys (if neccessary)
createEnrichmentKey('MigrateSubjectMSC');
createEnrichmentKey('MigrateSubjectDDC');

// Iterate over all documents.
$docFinder = new Opus_DocumentFinder();
$changedDocumentIds = [];
foreach ($docFinder->ids() as $docId) {
    $doc = null;
    try {
        $doc = new Opus_Document($docId);
    } catch (Opus_Model_NotFoundException $e) {
        continue;
    }

    $removeMscSubjects = [];
    $removeDdcSubjects = [];
    try {
        if (is_object($mscRole)) {
            $removeMscSubjects = migrateSubjectToCollection($doc, 'msc', $mscRole->getId(), 'MigrateSubjectMSC');
        }

        if (is_object($ddcRole)) {
            $removeDdcSubjects = migrateSubjectToCollection($doc, 'ddc', $ddcRole->getId(), 'MigrateSubjectDDC');
        }
    } catch (Exception $e) {
        $logger->err("fatal error while parsing document $docId: " . $e);
        continue;
    }

    if (count($removeMscSubjects) > 0 or count($removeDdcSubjects) > 0) {
        $changedDocumentIds[] = $docId;

        try {
            $doc->unregisterPlugin('Opus_Document_Plugin_Index');
            $doc->store();
            $logger->info("changed document $docId");
        } catch (Exception $e) {
            $logger->err("fatal error while STORING document $docId: " . $e);
        }
    }
}
$logger->info("changed " . count($changedDocumentIds) . " documents: " . implode(",", $changedDocumentIds));

function checkDocumentHasCollectionId($doc, $collectionId)
{
    foreach ($doc->getCollection() as $c) {
        if ($c->getId() === $collectionId) {
            return true;
        }
    }
    return false;
}

function migrateSubjectToCollection($doc, $subjectType, $roleId, $eKeyName)
{
    global $logger;
    $logPrefix = sprintf("[docId % 5d] ", $doc->getId());

    $keepSubjects = [];
    $removeSubjects = [];
    foreach ($doc->getSubject() as $subject) {
        $keepSubjects[$subject->getId()] = $subject;

        $type = $subject->getType();
        $value = $subject->getValue();

        if ($type !== $subjectType) {
            // $logger->debug("$logPrefix  Skipping subject (type '$type', value '$value')");
            continue;
        }

        // From now on, every subject will be migrated
        $keepSubjects[$subject->getId()] = false;
        $removeSubjects[] = $subject;

        // check if (unique) collection for subject value exists
        $collections = Opus_Collection::fetchCollectionsByRoleNumber($roleId, $value);
        if (! is_array($collections) or count($collections) < 1) {
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

function createEnrichmentKey($name)
{
    try {
        $eKey = new Opus_EnrichmentKey();
        $eKey->setName($name)->store();
    } catch (Exception $e) {
    }

    return new Opus_EnrichmentKey($name);
}

echo "\nConsult the log file $argv[1] for full details\n";
exit();
