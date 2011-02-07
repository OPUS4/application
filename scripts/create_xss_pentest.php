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
 * @author      Pascal-Nicolas Becker <becker@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

// Bootstrapping
require_once dirname(__FILE__) . '/common/bootstrap.php';

$counter = 1;
function randString($counter) {
   $template = '<script language="javascript" type="text/javascript">alert(\'alert STRING\');</script>';
   return str_replace('STRING', $counter, $template);
}


$doc = new Opus_Document();
$doc->setType(randString($counter++));
$doc->setServerState('published');
$doc->setServerDatePublished('01.01.1900');

// damn API. $doc->addPersonSubmiter() doesn't work for link models!
// -> we should change this in 4.x
$submitter = new Opus_Person();
$submitter->setFirstName(randString($counter++))
    ->setLastName(randString($counter++))
    ->setEmail(randString($counter++))
    ->setDateOfBirth('13.03.1920')
    ->setPlaceOfBirth(randString($counter++));
$doc->addPersonSubmitter($submitter);

$author = new Opus_Person();
$author->setFirstName(randString($counter++))
    ->setLastName(randString($counter++))
    ->setAcademicTitle(randString($counter++));
$doc->addPersonAuthor($author);

$doc->setLanguage('deu'.randString($counter++));

$titleMain = $doc->addTitleMain();
$titleMain->setValue(randString($counter++));
$titleMain->setLanguage('deu'.randString($counter++));
$titleMain2 = $doc->addTitleMain();
$titleMain2->setValue(randString($counter++));
$titleMain2->setLanguage('eng'.randString($counter++));

$abstract = $doc->addTitleAbstract();
$abstract->setValue(randString($counter++));
$abstract->setLanguage('deu'.randString($counter++));

$titleSub = $doc->addTitleSub();
$titleSub->setValue(randString($counter++));
$titleSub->setLanguage('deu'.randString($counter++));

$titleAdditional = $doc->addTitleAdditional();
$titleAdditional->setValue(randString($counter++));
$titleAdditional->setLanguage('deu'.randString($counter++));

$titleParent = $doc->addTitleParent();
$titleParent->setValue(randString($counter++));
$titleParent->setLanguage('fra'.randString($counter++));

$doc->setPageNumber(randString($counter++));
$doc->setPageFirst(randString($counter++));
$doc->setPageLast(randString($counter++));

$doc->setVolume(randString($counter++));
$doc->setIssue(randString($counter++));

$instituteName='Institut für empirische Forschung ' . randString($counter++);
$institutesRole = Opus_CollectionRole::fetchByName('institutes');
if (is_null($institutesRole) === true) {
    $institutesRole = new Opus_CollectionRole();
    $institutesRole->setName('institutes'.randString($counter++))
                   ->setOaiName('institutes'.randString($counter++))
                   ->setPosition(1)
                   ->setVisible(1)
                   ->setVisibleBrowsingStart(1)
                   ->setDisplayBrowsing('Name')
                   ->setVisibleFrontdoor(1)
                   ->setDisplayFrontdoor('Name')
                   ->setVisibleOai('Name')
                   ->setDisplayOai('Name')
                   ->store();
}

$instituteCollections = Opus_Collection::fetchCollectionsByRoleName($institutesRole->getId(), $instituteName);
if (count($instituteCollections) >=1) {
    $instituteCollection = $instituteCollections[0];
} else {
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

$doc->setPublishedYear('2010');
$doc->setPublishedDate('28.09.2010');

$doc->setPublisherName(randString($counter++));
$doc->setPublisherPlace(randString($counter++));

$doc->setCompletedYear('2010');
$doc->setCompletedDate('27.09.2010');

$o3id = $doc->addIdentifierOpus3();
$o3id->setValue(randString($counter++));

// empty URN will be automaticaly replace by new URN.
$urn = $doc->addIdentifierUrn();
$urn->setValue('urn:nbn:de:kobv:nn-opus-173:'.randString($counter++));

$isbn = $doc->addIdentifierIsbn();
$isbn->setValue('978-3-86680-192-9');

$issn = $doc->addIdentifierIssn();
$issn->setValue('1234-5678');

$doc->addIdentifierOpac()->setValue(randString($counter++));

$doc->setThesisDateAccepted('01.02.2003');



$dnbInstitute=new Opus_DnbInstitute();
$dnbInstitute->setName(randString($counter++));
foreach(Opus_DnbInstitute::getGrantors() as $grantor) {
    if ($dnbInstitute->getName() === $grantor->getName()) {
        $dnbInstitute = $grantor;
        break;
    }
}
if (is_null($dnbInstitute->getId()) === true) {
        $dnbInstitute->setCity(randString($counter++))->setIsGrantor(1)->store();
}
$doc->setThesisGrantor($dnbInstitute);
$doc->setThesisPublisher($dnbInstitute);

$referee = new Opus_Person();
$referee->setFirstName('Gyro'.randString($counter++));
$referee->setLastName('Gearloose'.randString($counter++));
$referee->setAcademicTitle('Prof. Dr.'.randString($counter++));
$doc->addPersonReferee($referee );

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

$doc->setCreatingCorporation(randString($counter++));
$doc->setContributingCorporation(randString($counter++));

$swd = $doc->addSubjectSwd();
$swd->setValue(randString($counter++));

$free_subject_deu = $doc->addSubjectUncontrolled();
$free_subject_deu->setLanguage(randString($counter++))->setValue(randString($counter++));

$free_subject_eng = $doc->addSubjectUncontrolled();
$free_subject_eng->setLanguage("eng\0".randString($counter++))->setValue(randString($counter++));

$note1 = $doc->addNote();
$note1->setVisibility('public')->setMessage(randString($counter++));
$note2 = $doc->addNote();
$note2->setVisibility('private')->setMessage(randString($counter++));

$licences = Opus_Licence::getAll();
if (count($licences) >= 1) {
    $lic = $licences[0];
} else {
    $lic = new Opus_Licence();
    $lic->setActive(1);
    $lic->setLanguage('deu'.randString($counter++));
    $lic->setLinkLicence(randString($counter++));
    $lic->setNameLong(randString($counter++));
    $lic->store();
}
$doc->setLicence($lic);

$doc->setServerDateUnlocking("23.05.1949");

$ddc = $doc->addSubjectDDC();
$ddc->setValue('Allgemeines, Wissenschaft' . randString($counter++))->setLanguage('deu'.randString($counter++))->setExternalKey(randString($counter++));

$doc->store();
print("Document stored. ID: " . $doc->getId() . "\n");

