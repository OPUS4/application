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
 * @author      Pascal-Nicolas Becker <becker@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 *
 * TODO use fromArray functionality to create document
 * TODO create test that verifies completeness (compare with describe function)
 */

$doc = new Opus_Document();
$doc->setType('all');
$doc->setServerState('published');
$doc->setServerDatePublished('1900-01-01');


// damn API. $doc->addPersonSubmiter() doesn't work for link models!
// -> we should change this in 4.x
$submitter = new Opus_Person();
$submitter->setFirstName('Donald')->setLastName('Duck')->setEmail('donald@example.org')->setDateOfBirth('1920-03-13')
    ->setPlaceOfBirth('Entenhausen');
$doc->addPersonSubmitter($submitter);

$author = new Opus_Person();
$author->setFirstName('Daniel')->setLastName('Düsentrieb')->setAcademicTitle('Dr.-Ing.');
$doc->addPersonAuthor($author);

$doc->setLanguage('deu');

$titleMain = $doc->addTitleMain();
$titleMain->setValue('Dokument zur empirischen Unterschung der OAI-Schnittstelle');
$titleMain->setLanguage('deu');
$titleMainEng = $doc->addTitleMain();
$titleMainEng->setValue('Document for empirical testing OAI interface');
$titleMainEng->setLanguage('eng');

$abstract = $doc->addTitleAbstract();
$abstract->setValue('Dokument, dass alle Daten enhält, um testen zu können, wie die OAI-Schnittstelle sie ausgibt.');
$abstract->setLanguage('deu');

$titleSub = $doc->addTitleSub();
$titleSub->setValue('Beispielhaft erleutert an OPUS 4.0.0');
$titleSub->setLanguage('deu');

$titleAdditional = $doc->addTitleAdditional();
$titleAdditional->setValue('OAI-Schnittstellen empirisch testen am Beispiel von OPUS 4.0');
$titleAdditional->setLanguage('deu');

$titleParent = $doc->addTitleParent();
$titleParent->setValue('Tester interface d\'OAI en OPUS 4.0');
$titleParent->setLanguage('fra');

$doc->setPageNumber('123');
$doc->setPageFirst('122');
$doc->setPageLast('124');

$doc->setVolume('4');
$doc->setIssue('18');

$instituteName='Institut für empirische Forschung';
$institutesRole = Opus_CollectionRole::fetchByName('institutes');
if (is_null($institutesRole) === true) {
    $institutesRole = new Opus_CollectionRole();
    $institutesRole->setName('institutes')
                   ->setOaiName('institutes')
                   ->setPosition(1)
                   ->setVisible(1)
                   ->setVisibleBrowsingStart(1)
                   ->setDisplayBrowsing('Name')
                   ->setVisibleFrontdoor(1)
                   ->setDisplayFrontdoor('Name')
                   ->setVisibleOai('Name')
                   ->store();
}
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
                        ->setName($instituteName)
                        ->store();
}
$doc->addCollection($instituteCollection);

$doc->setPublishedYear('2010');
$doc->setPublishedDate('2010-09-28');

$doc->setPublisherName('The Walt Disney Company');
$doc->setPublisherPlace('Burbank, CA');

$doc->setCompletedYear('2010');
$doc->setCompletedDate('2010-09-27');

$opusThreeId = $doc->addIdentifierOpus3();
$opusThreeId->setValue('1234');

// empty URN will be automaticaly replace by new URN.
$urn = $doc->addIdentifierUrn();
$urn->setValue('urn:nbn:de:kobv:nn-opus-173');

$isbn = $doc->addIdentifierIsbn();
$isbn->setValue('978-3-86680-192-9');

$issn = $doc->addIdentifierIssn();
$issn->setValue('1234-5678');

$doc->addIdentifierOpac()->setValue('OPAC-ID 001 1237890654');

// Valid Arxiv-Identifier from ArXiv.org Homepage: http://arxiv.org/help/arxiv_identifier
$arxiv = $doc->addIdentifierArxiv();
$arxiv->setValue('arXiv:0706.0001');

// Valid DOI Identifier from DOI Homepage: http://www.doi.org/
$doi = $doc->addIdentifierDoi();
$doi->setValue('10.1000/182');

// Valid Pubmed-Identifier from official Pubmed Tutorial: http://www.nlm.nih.gov/bsd/disted/pubmedtutorial/020_830.html
$pubmed = $doc->addIdentifierPubmed();
$pubmed->setValue('9382368');

$doc->setThesisDateAccepted('2003-02-01');

$dnbInstitute=new Opus_DnbInstitute();
$dnbInstitute->setName('Forschungsinstitut für Code Coverage');
foreach (Opus_DnbInstitute::getGrantors() as $grantor) {
    if ($dnbInstitute->getName() === $grantor->getName()) {
        $dnbInstitute = $grantor;
        break;
    }
}
if (is_null($dnbInstitute->getId()) === true) {
        $dnbInstitute->setCity('Mousetown')->setIsGrantor(1)->store();
}
$doc->setThesisGrantor($dnbInstitute);
$doc->setThesisPublisher($dnbInstitute);

$referee = new Opus_Person();
$referee->setFirstName('Gyro');
$referee->setLastName('Gearloose');
$referee->setAcademicTitle('Prof. Dr.');
$referee->store();
$doc->addPersonReferee($referee);

$editor = new Opus_Person();
$editor->setFirstName('Bob');
$editor->setLastName('Foster');
$editor->store();
$doc->addPersonEditor($editor);

$advisor = new Opus_Person();
$advisor->setFirstName('Fred');
$advisor->setLastName('Clever');
$advisor->store();
$doc->addPersonAdvisor($advisor);

$translator = new Opus_Person();
$translator->setFirstName('Erika');
$translator->setLastName('Fuchs');
$translator->store();
$doc->addPersonTranslator($translator);

$contributor = new Opus_Person();
$contributor->setFirstName('Jeff');
$contributor->setLastName('Smart');
$contributor->store();
$doc->addPersonContributor($contributor);

$doc->setCreatingCorporation('Walt Disney Creation Laboratories');
$doc->setContributingCorporation('Pixar Animation Studio');

$swd = $doc->addSubject()->setType('swd');
$swd->setValue('Test');

$freeSubjectDeu = $doc->addSubject()->setType('uncontrolled');
$freeSubjectDeu->setLanguage('deu')->setValue('Maustest');

$freeSubjectEng = $doc->addSubject()->setType('uncontrolled');
$freeSubjectEng->setLanguage('eng')->setValue('mouse test');

$note = $doc->addNote();
$note->setVisibility('public')->setMessage(
    'ein Dokument, dass noch eine Bemerkung braucht, weil im Abstract nicht alles gesagt wurde...'
);
$noteTwo = $doc->addNote();
$noteTwo->setVisibility('private')->setMessage('und noch eine Bemerkung zum Bearbeitungsstand.');

$licences = Opus_Licence::getAll();
if (count($licences) >= 1) {
    $lic = $licences[0];
}
else {
    $lic = new Opus_Licence();
    $lic->setActive(1);
    $lic->setLanguage('deu');
    $lic->setLinkLicence('http://www.test.de');
    $lic->setNameLong('Ein langer LizenzName');
    $lic->store();
}
$doc->setLicence($lic);

// check for enrichment keys before creating enrichments
$enrichmentKeys = Opus_EnrichmentKey::getAll();
$enrichmentKeyNames = array();
foreach ($enrichmentKeys as $enrichmentKey) {
   $enrichmentKeyNames[] = $enrichmentKey->getName();
}
$missingEnrichmentKeyNames = array_diff(
    array('SourceSwb','SourceTitle','ClassRvk','ContributorsName','Event', 'City', 'Country'), $enrichmentKeyNames
);
if (!empty($missingEnrichmentKeyNames)) {
   foreach ($missingEnrichmentKeyNames as $missingEnrichmentKeyName) {
      $newEnrichmentKey = new Opus_EnrichmentKey();
      $newEnrichmentKey->setName($missingEnrichmentKeyName);
      $newEnrichmentKey->store();
   }
}

// Some Enrichment-Fields from Opus3-Migration
$doc->addEnrichment()->setKeyName('SourceSwb')->setValue('http://www.test.de');
$doc->addEnrichment()->setKeyName('SourceTitle')->setValue('Dieses Dokument ist auch erschienen als ...');
$doc->addEnrichment()->setKeyName('ClassRvk')->setValue('LI 99660');
$doc->addEnrichment()->setKeyName('ContributorsName')->setValue('John Doe (Foreword) and Jane Doe (Illustration)');

// Additional Enrichment-Fields
$doc->addEnrichment()->setKeyName('Event')->setValue('Opus4 OAI-Event');
$doc->addEnrichment()->setKeyName('City')->setValue('Opus4 OAI-City');
$doc->addEnrichment()->setKeyName('Country')->setValue('Opus4 OAI-Country');

$doc->store();
print("Document stored. ID: " . $doc->getId() . "\n");

