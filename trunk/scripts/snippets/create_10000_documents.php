<?php

for ($i = 1; $i < 10000; $i++) {

  $d = new Opus_Document();
  $d->setServerState('published');
  $d->setType('preprint');
  $d->setLanguage('deu');

  $title = $d->addTitleMain();
  $title->setLanguage('deu');
  $title->setValue('title-' . rand());

  $date = new Opus_Date();
  $date->setNow();
  $date->setYear(1990 + ($i%23));
  $d->setPublishedDate($date);

  $p = new Opus_Person();
  $p->setFirstName("foo-" . ($i%7));
  $p->setLastName("bar-" . ($i%5));
  $p = $d->addPersonAuthor($p);

  $c = new Opus_Collection(15990 + ($i%103));
  $d->addCollection($c);

  $s = $d->addSubjectDDC();
  $s->setValue($i%97);

  $docId = $d->store();
  echo "docId: $docId\n";
}

exit();
