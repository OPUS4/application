#!/usr/bin/env php5
<?php

// Bootstrapping.
require_once dirname(__FILE__) . '/../common/bootstrap.php';

// Parse arguments.
global $argc, $argv;

if (count($argv) != 2) {
   echo "usage: " . __FILE__ . " logfile.log\n";
   exit(-1);
}

// Initialize logger.
$logfileName = $argv[1];

$logfile = @fopen($logfileName, 'a', false);
$writer = new Zend_Log_Writer_Stream($logfile);        
$formatter=new Zend_Log_Formatter_Simple('%timestamp% %priorityName%: %message%' . PHP_EOL);
$writer->setFormatter($formatter);
$logger = new Zend_Log($writer);
$logger->info('Script started...');

// load collections (and check existence)
$mscRole   = Opus_CollectionRole::fetchByName('msc');
if (!is_object($mscRole)) {
    $logger->warn("MSC collection does not exist.  Cannot migrate SubjectMSC.");
}

$ddcRole   = Opus_CollectionRole::fetchByName('ddc');
if (!is_object($ddcRole)) {
    $logger->warn("DDC collection does not exist.  Cannot migrate SubjectDDC.");
}

// create enrichment keys (if neccessary)
createEnrichmentKey('MigrateSubjectMSC');
createEnrichmentKey('MigrateSubjectDDC');

// Iterate over all documents.
$docFinder = new Opus_DocumentFinder();
$changedDocumentIds = array();
foreach ($docFinder->ids() AS $docId) {

   $doc = null;
   try {
      $doc = new Opus_Document($docId);
   }
   catch (Opus_Model_NotFoundException $e) {
      continue;
   }

   $removeMscSubjects = array();
   $removeDdcSubjects = array();
   try {
      
      if (is_object($mscRole)) {
         $removeMscSubjects = migrateSubjectToCollection($doc, 'msc', $mscRole->getId(), 'MigrateSubjectMSC');
      }
      
      if (is_object($ddcRole)) {
         $removeDdcSubjects = migrateSubjectToCollection($doc, 'ddc', $ddcRole->getId(), 'MigrateSubjectDDC');
      }
   }
   catch (Exception $e) {
      $logger->err("fatal error while parsing document $docId: " . $e);
      continue;
   }

   if (count($removeMscSubjects) > 0 or count($removeDdcSubjects) > 0) {
      $changedDocumentIds[] = $docId;

      try {
         $doc->unregisterPlugin('Opus_Document_Plugin_Index');
         $doc->store();
         $logger->info("changed document $docId");
      }
      catch (Exception $e) {
         $logger->err("fatal error while STORING document $docId: " . $e);
      }
   }

}
$logger->info("changed " . count($changedDocumentIds) . " documents: " . implode(",", $changedDocumentIds));

function checkDocumentHasCollectionId($doc, $collectionId) {
   foreach ($doc->getCollection() AS $c) {
      if ($c->getId() === $collectionId) {
         return true;
      }
   }
   return false;
}

function migrateSubjectToCollection($doc, $subjectType, $roleId, $eKeyName) {
   global $logger;
   $logPrefix = sprintf("[docId % 5d] ", $doc->getId());

   $keepSubjects   = array();
   $removeSubjects = array();
   foreach ($doc->getSubject() AS $subject) {
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
      if (!is_array($collections) or count($collections) < 1) {
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

      $collection   = $collections[0];
      $collectionId = $collection->getId();
      // check if document already belongs to this collection
      if (checkDocumentHasCollectionId($doc, $collectionId)) {
         // nothing to do
         $logger->info("$logPrefix  Migrating subject (type '$type', value '$value') -- collection already assigned (collections $collectionId).");         
         continue;
      }

      // migrate subject to collections
      $logger->info("$logPrefix  Migrating subject (type '$type', value '$value') to (collection $collectionId)");
      $doc->addCollection($collection);
   }

   if (count($removeSubjects) > 0) {
      // debug: removees
      foreach ($removeSubjects AS $r) {
         $logger->debug("$logPrefix  Removing subject (type '".$r->getType()."', value '".$r->getValue()."')");
      }

      $newSubjects = array_filter(array_values($keepSubjects));
      foreach ($newSubjects AS $k) {
         $logger->debug("$logPrefix  Keeping subject (type '".$k->getType()."', value '".$k->getValue()."')");
      }

      $doc->setSubject($newSubjects);
   }

   return $removeSubjects;
}

function createEnrichmentKey($name) {
   try {
      $eKey = new Opus_EnrichmentKey();
      $eKey->setName($name)->store();
   }
   catch (Exception $e) {
   }

   return new Opus_EnrichmentKey($name);
}

exit();
