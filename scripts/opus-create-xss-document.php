<?php
/*
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
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

// Bootstrapping
require_once dirname(__FILE__) . '/common/bootstrap.php';

$counter = 1;
function randString($counter) {
   $template = '<i><script language="javascript" type="text/javascript">alert(\'alert STRING\');</script>';
   return str_replace('STRING', $counter, $template);
}


// error handler function
function myErrorHandler($errno, $errstr, $errfile, $errline) {
   echo "WARNING: myErrorHandler($errno, '$errstr', '$errfile', $errline)\n";
   return true;
}
// set to the user defined error handler
$oldErrorHandler = set_error_handler("myErrorHandler");



//
// Creating document, filling static fields.
//
$doc = new Opus_Document();
$doc->setType(randString($counter++));
$doc->setServerState('published');
$doc->setServerDatePublished('01.01.1900');
$doc->setLanguage('deu'.randString($counter++));
$doc->setThesisDateAccepted('01.02.2003');
$doc->setPublishedYear('2010');
$doc->setPublishedDate('28.09.2010');
$doc->setCompletedYear('2010');
$doc->setCompletedDate('27.09.2010');
$doc->setPublisherName(randString($counter++));
$doc->setPublisherPlace(randString($counter++));
$doc->setPageNumber(randString($counter++));
$doc->setPageFirst(randString($counter++));
$doc->setPageLast(randString($counter++));
$doc->setVolume(randString($counter++));
$doc->setIssue(randString($counter++));
$doc->setCreatingCorporation(randString($counter++));
$doc->setContributingCorporation(randString($counter++));



//
// Persons
//
$submitter = new Opus_Person();
$submitter->getField('Email')->setValidator(null);
$submitter->setFirstName(randString($counter++))
    ->setLastName(randString($counter++))
    ->setEmail(randString($counter++))
    ->setAcademicTitle(randString($counter++))
    ->setDateOfBirth(randString($counter++))
    ->setPlaceOfBirth(randString($counter++));
$doc->addPersonSubmitter($submitter);

$author = new Opus_Person();
$author->getField('Email')->setValidator(null);
$author->setFirstName(randString($counter++))
    ->setLastName(randString($counter++))
    ->setEmail(randString($counter++))
    ->setAcademicTitle(randString($counter++))
    ->setDateOfBirth(randString($counter++))
    ->setPlaceOfBirth(randString($counter++));
$doc->addPersonAuthor($author);

$referee = new Opus_Person();
$referee->setFirstName('Gyro'.randString($counter++));
$referee->setLastName('Gearloose'.randString($counter++));
$referee->setAcademicTitle('Prof. Dr.'.randString($counter++));
$doc->addPersonReferee($referee);

$editor = new Opus_Person();
$editor->setFirstName('Bob'.randString($counter++));
$editor->setLastName('Foster'.randString($counter++));
$doc->addPersonEditor($editor);

$advisor = new Opus_Person();
$advisor->setFirstName('Fred'.randString($counter++));
$advisor->setLastName('Clever'.randString($counter++));
$doc->addPersonAdvisor($advisor);

$translator = new Opus_Person();
$translator->setFirstName('Erika'.randString($counter++));
$translator->setLastName('Fuchs'.randString($counter++));
$doc->addPersonTranslator($translator);

$contributor = new Opus_Person();
$contributor->setFirstName('Jeff'.randString($counter++));
$contributor->setLastName('Smart'.randString($counter++));
$contributor->store();
$doc->addPersonContributor($contributor);


//
// Titles
//
foreach (array('addTitleMain', 'addTitleAbstract', 'addTitleParent', 'addTitleSub', 'addTitleAdditional') AS
         $titleMethod) {
   $doc->$titleMethod()
      ->setValue(randString($counter++))
      ->setLanguage(randString($counter++));
   $doc->$titleMethod()
      ->setValue(randString($counter++))
      ->setLanguage('deu');
   $doc->$titleMethod()
      ->setValue(randString($counter++))
      ->setLanguage('eng');
}



//
// Collections
//
$institutesRole = new Opus_CollectionRole();
$institutesRole->setName('institutes'.randString($counter++).rand())
                   ->setOaiName('institutes'.randString($counter++).rand())
                   ->setPosition(1)
                   ->setVisible(1)
                   ->setVisibleBrowsingStart(1)
                   ->setDisplayBrowsing('Name')
                   ->setVisibleFrontdoor(1)
                   ->setDisplayFrontdoor('Name')
                   ->setVisibleOai('Name')
                   ->store();

$instituteName='Institut für empirische Forschung ' . randString($counter++);
$instituteCollections = Opus_Collection::fetchCollectionsByRoleName($institutesRole->getId(), $instituteName);
if (count($instituteCollections) >=1) {
    $instituteCollection = $instituteCollections[0];
}
else {
    $rootCollection = $institutesRole->getRootCollection();
    if (is_null($rootCollection) === true) {
        $rootCollection = $institutesRole->addRootCollection();
        $rootCollection->setVisible(1)->store();
        $institutesRole->store();
    }
    $instituteCollection = $rootCollection->addLastChild();
    $instituteCollection->setVisible(1)
                        ->setName(randString($counter++))
                        ->store();
}
$doc->addCollection($instituteCollection);



//
// Identifiers
//
$oldOpusId = $doc->addIdentifierOpus3();
$oldOpusId->setValue(randString($counter++));

// empty URN will be automaticaly replace by new URN.
$urn = $doc->addIdentifierUrn();
$urn->setValue('urn:nbn:de:kobv:nn-opus-173:'.randString($counter++));

$isbn = $doc->addIdentifierIsbn();
$isbn->setValue('978-3-86680-192-9');

$issn = $doc->addIdentifierIssn();
$issn->setValue('1234-5678');

$doc->addIdentifierOpac()->setValue(randString($counter++));



//
// DnbInstitutes
//
$dnbInstitute = new Opus_DnbInstitute();
$dnbInstitute->setName(randString($counter++).rand())
          ->setAddress(randString($counter++))
          ->setCity(randString($counter++))
          ->setPhone(randString($counter++))
          ->setDnbContactId(randString($counter++))
          ->setIsGrantor(1)
          ->store();

$doc->setThesisGrantor($dnbInstitute);
$doc->setThesisPublisher($dnbInstitute);



//
// Subjects
//
$doc->addSubject()->setType('swd')
   ->setValue(randString($counter++));

foreach (array('uncontrolled', 'msc', 'ddc') AS $type) {
   $doc->addSubject()->setType($type)
   ->setLanguage(randString($counter++))
   ->setValue(randString($counter++))
   ->setExternalKey(randString($counter++));
   $doc->addSubject()->setType($type)
   ->setLanguage("eng\0".randString($counter++))
   ->setValue(randString($counter++))
   ->setExternalKey(randString($counter++));
   $doc->addSubject()->setType($type)
   ->setLanguage("deu")
   ->setValue(randString($counter++))
   ->setExternalKey(randString($counter++));
   $doc->addSubject()->setType($type)
   ->setLanguage("eng")
   ->setValue(randString($counter++))
   ->setExternalKey(randString($counter++));
}



//
// Notes
//
$doc->addNote()
   ->setVisibility('public')
   ->setMessage(randString($counter++));
$doc->addNote()
   ->setVisibility('private')
   ->setMessage(randString($counter++));



//
// Licenses
//
$lic = new Opus_Licence();
$lic->setActive(1);
$lic->setLanguage('deu'.randString($counter++));
$lic->setLinkLicence(randString($counter++));
$lic->setNameLong(randString($counter++));
$lic->store();
$doc->setLicence($lic);



//
// Storing...
//
$doc->store();
print("Document stored. ID: " . $doc->getId() . "\n");
