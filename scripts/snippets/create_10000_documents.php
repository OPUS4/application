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
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * script to create 10000 documents, e.g., for performance testing
 */

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
