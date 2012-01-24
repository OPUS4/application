<?php

// Bootstrapping.
require_once dirname(__FILE__) . '/common/bootstrap.php';

// test data.
$d = new Opus_Document();
$d->addSubject()->setType('msc')->setValue('00-XX');
$d->addSubject()->setType('msc')->setValue('00-XX');
$d->addSubject()->setType('ddc')->setValue('10');
$d->addSubject()->setType('uncontrolled')->setValue('foo');
$d->store();

$d = new Opus_Document();
$d->addSubject()->setType('msc')->setValue('01-XX');
$d->store();

$d = new Opus_Document();
$d->addSubject()->setType('msc')->setValue('12345');
$d->store();

$d = new Opus_Document();
$d->addSubject()->setType('msc')->setValue('00-XX');
$d->addCollection(new Opus_Collection(7688));
$d->addCollection(new Opus_Collection(7653));
$d->store();

// load collections (and check existence)
$mscRole   = Opus_CollectionRole::fetchByName('msc');
if (!is_object($mscRole)) {
    echo "WARNING: MSC collection does not exist.  Cannot migrate SubjectMSC.\n";
}

$ddcRole   = Opus_CollectionRole::fetchByName('ddc');
if (!is_object($ddcRole)) {
    echo "WARNING: DDC collection does not exist.  Cannot migrate SubjectDDC.\n";
}

// Iterate over all documents.
$docFinder = new Opus_DocumentFinder();
foreach ($docFinder->ids() AS $docId) {

   $doc = null;
   try {
      $doc = new Opus_Document($docId);
   }
   catch (Opus_Model_NotFoundException $e) {
      continue;
   }

   if (is_object($mscRole)) {
       $removeMscSubjects = migrateSubjectToCollection($doc, 'msc', $mscRole->getId());
   }
   if (is_object($ddcRole)) {
       $removeDdcSubjects = migrateSubjectToCollection($doc, 'ddc', $ddcRole->getId());
   }

   $doc->store();
}

function checkDocumentHasCollectionId($doc, $collectionId) {
   foreach ($doc->getCollection() AS $c) {
      if ($c->getId() === $collectionId) {
         return true;
      }
   }
   return false;
}

function migrateSubjectToCollection($doc, $subjectType, $roleId) {
   $logPrefix = sprintf("[docId % 5d] ", $doc->getId());

   $keepSubjects   = array();
   $removeSubjects = array();
   foreach ($doc->getSubject() AS $subject) {
      $keepSubjects[$subject->getId()] = $subject;

      $type = $subject->getType();
      $value = $subject->getValue();

      if ($type !== $subjectType) {
         // echo "$logPrefix   Skipping subject (type '$type', value '$value')\n";
         continue;
      }

      // check if (unique) collection for subject value exists
      $collections = Opus_Collection::fetchCollectionsByRoleNumber($roleId, $value);
      if (!is_array($collections) or count($collections) < 1) {
         echo "$logPrefix WARNING: No collection found for value '$value'\n";
         continue;
      }

      if (count($collections) > 1) {
         echo "$logPrefix WARNING: Ambiguous collections for value '$value'\n";
         continue;
      }

      $collection   = $collections[0];
      $collectionId = $collection->getId();
      // check if document already belongs to this collection
      if (checkDocumentHasCollectionId($doc, $collectionId)) {
         echo "$logPrefix NOTICE: Only removing subject (type '$type', value '$value') -- collection already assigned (collections $collectionId)...\n";
         $doc->addCollection($collection);
         $keepSubjects[$subject->getId()] = false;
         $removeSubjects[] = $subject;
         continue;
      }

      echo "$logPrefix NOTICE: Migrating subject (type '$type', value '$value') to (collection $collectionId)\n";
      $doc->addCollection($collection);
      $keepSubjects[$subject->getId()] = false;
      $removeSubjects[] = $subject;
   }

   if (count($removeSubjects) > 0) {
      // debug: removees
      foreach ($removeSubjects AS $r) {
         echo "$logPrefix DEBUG: Not keeping (type '".$r->getType()."', value '".$r->getValue()."')\n";
      }
   
      $doc->setSubject(array_values($keepSubjects));
      echo "\n";
   }

   return $removeSubjects;
}

exit();
