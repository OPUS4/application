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

use Opus\Common\Date;
use Opus\Common\DnbInstitute;
use Opus\Common\Document;
use Opus\Common\DocumentInterface;
use Opus\Common\FileInterface;
use Opus\Common\Identifier;
use Opus\Common\Licence;
use Opus\Common\Model\ModelException;
use Opus\Common\Person;
use Opus\Common\Subject;
use Opus\Common\Title;
use Opus\Common\TitleAbstract;

class Frontdoor_Model_HtmlMetaTagsTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database', 'view'];

    /** @var Frontdoor_Model_HtmlMetaTags */
    private $htmlMetaTags;

    /** @var Date */
    private $currDate;

    public function setUp(): void
    {
        parent::setUp();
        $this->htmlMetaTags = new Frontdoor_Model_HtmlMetaTags(
            $this->getConfig(),
            'http://localhost/opus'
        );

        $this->currDate = Date::getNow();
    }

    public function testCreateTagsForMinimalDocument()
    {
        $doc = $this->createTestDocument();
        $doc->setLanguage('deu');
        $doc->setPublishedYear('2048');
        $docId = $doc->store();

        $result = $this->htmlMetaTags->createTags($doc);

        $this->assertCount(8, $result);
        $this->assertContains(['DC.date', '2048'], $result);
        $this->assertContains(['DC.issued', '2048'], $result);
        $this->assertContains(['citation_date', '2048'], $result);
        $this->assertContains(['citation_publication_date', '2048'], $result);
        $this->assertContains(['DC.language', 'deu'], $result);
        $this->assertContains(['citation_language', 'deu'], $result);

        $this->assertFrontdoorUrl($result, $docId);
    }

    public function testCreateTagsForJournalPaper()
    {
        $doc = $this->createJournalPaper();
        $this->handleJournalPaper($doc);
    }

    public function testCreateTagsForCustomTypeJournalPaper()
    {
        $this->htmlMetaTags->setConfig($this->adjustConfiguration(
            ['metatags' => ['mapping' => ['journal_paper' => ['customdoctype']]]]
        ));

        $doc = $this->createTestDoc('customdoctype');
        $this->handleJournalPaper($doc);
    }

    /**
     * @param DocumentInterface $doc
     */
    private function handleJournalPaper($doc)
    {
        $result = $this->htmlMetaTags->createTags($doc);

        $this->assertCount(61, $result);
        $this->assertCommonMetaTags($result, $doc->getId());
        $this->assertParentTitle($result, 'journal');
        $this->assertVolumeAndIssue($result);
        $this->assertPages($result);
        $this->assertIssn($result);
    }

    public function testCreateTagsForConferencePaper()
    {
        $doc = $this->createConferencePaper();
        $this->handleConferencePaper($doc);
    }

    public function testCreateTagsForCustomTypeConferencePaper()
    {
        $this->htmlMetaTags->setConfig($this->adjustConfiguration(
            ['metatags' => ['mapping' => ['conference_paper' => ['customdoctype']]]]
        ));

        $doc = $this->createTestDoc('customdoctype');
        $this->handleConferencePaper($doc);
    }

    /**
     * @param DocumentInterface $doc
     */
    private function handleConferencePaper($doc)
    {
        $result = $this->htmlMetaTags->createTags($doc);

        $this->assertCount(61, $result);
        $this->assertCommonMetaTags($result, $doc->getId());
        $this->assertParentTitle($result, 'conference');
        $this->assertVolumeAndIssue($result);
        $this->assertPages($result);
        $this->assertIssn($result);
    }

    public function testCreateTagsForThesis()
    {
        $doc = $this->createThesis();
        $this->handleThesis($doc);
    }

    public function testCreateTagsForCustomTypeThesis()
    {
        $this->htmlMetaTags->setConfig($this->adjustConfiguration(
            ['metatags' => ['mapping' => ['thesis' => ['customdoctype']]]]
        ));

        $doc = $this->createTestDoc('customdoctype');
        $this->handleThesis($doc);
    }

    /**
     * @param DocumentInterface $doc
     */
    private function handleThesis($doc)
    {
        $result = $this->htmlMetaTags->createTags($doc);

        $this->assertCount(52, $result);
        $this->assertCommonMetaTags($result, $doc->getId());
        $this->assertThesisPublisher($doc, $result);
        $this->assertDocumentType($result, $doc->getType());
    }

    public function testCreateTagsForWorkingPaper()
    {
        $doc = $this->createWorkingPaper();
        $this->handleWorkingPaper($doc);
    }

    public function testCreateTagsForCustomTypeWorkingPaper()
    {
        $this->htmlMetaTags->setConfig($this->adjustConfiguration(
            ['metatags' => ['mapping' => ['working_paper' => ['customdoctype']]]]
        ));

        $doc = $this->createTestDoc('customdoctype');
        $this->handleWorkingPaper($doc);
    }

    /**
     * @param DocumentInterface $doc
     */
    private function handleWorkingPaper($doc)
    {
        $result = $this->htmlMetaTags->createTags($doc);

        $this->assertCount(57, $result);
        $this->assertCommonMetaTags($result, $doc->getId());
        $this->assertVolumeAndIssue($result);
        $this->assertIssn($result);
        $this->assertInstitution($result, 'crea');
    }

    public function testCreateTagsForWorkingPaperWithContributingCorporation()
    {
        $doc = $this->createWorkingPaper();
        $doc->setCreatingCorporation('');
        $doc->store();

        $this->handleWorkingPaperWithContributingCorporation($doc);
    }

    public function testCreateTagsForCustomTypeWorkingPaperWithContributingCorporation()
    {
        $this->htmlMetaTags->setConfig($this->adjustConfiguration(
            ['metatags' => ['mapping' => ['working_paper' => ['customdoctype']]]]
        ));

        $doc = $this->createTestDoc('customdoctype');
        $doc->setCreatingCorporation('');
        $doc->store();

        $this->handleWorkingPaperWithContributingCorporation($doc);
    }

    /**
     * @param DocumentInterface $doc
     */
    private function handleWorkingPaperWithContributingCorporation($doc)
    {
        $result = $this->htmlMetaTags->createTags($doc);

        // prüft nur, ob citation_technical_report_institution richtig gesetzt
        $this->assertInstitution($result, 'cont');
    }

    public function testCreateTagsForWorkingPaperWithPublisher()
    {
        $doc = $this->createWorkingPaper();
        $doc->setCreatingCorporation('');
        $doc->setContributingCorporation('');
        $doc->store();

        $this->handleWorkingPaperWithPublisher($doc);
    }

    public function testCreateTagsForCustomTypeWorkingPaperWithPublisher()
    {
        $this->htmlMetaTags->setConfig($this->adjustConfiguration(
            ['metatags' => ['mapping' => ['working_paper' => ['customdoctype']]]]
        ));

        $doc = $this->createTestDoc('customdoctype');
        $doc->setCreatingCorporation('');
        $doc->setContributingCorporation('');
        $doc->store();

        $this->handleWorkingPaperWithPublisher($doc);
    }

    /**
     * @param DocumentInterface $doc
     */
    private function handleWorkingPaperWithPublisher($doc)
    {
        $result = $this->htmlMetaTags->createTags($doc);

        // prüft nur, ob citation_technical_report_institution richtig gesetzt
        $this->assertInstitution($result, 'publisherName');
    }

    public function testCreateTagsForBook()
    {
        $doc = $this->createBook();
        $this->handleBook($doc);
    }

    public function testCreateTagsForCustomTypeBook()
    {
        $this->htmlMetaTags->setConfig($this->adjustConfiguration(
            ['metatags' => ['mapping' => ['book' => ['customdoctype']]]]
        ));

        $doc = $this->createTestDoc('customdoctype');
        $this->handleBook($doc);
    }

    /**
     * @param DocumentInterface $doc
     */
    private function handleBook($doc)
    {
        $result = $this->htmlMetaTags->createTags($doc);

        $this->assertCount(51, $result);
        $this->assertCommonMetaTags($result, $doc->getId());
        $this->assertParentTitle($result, 'inbook');
    }

    public function testCreateTagsForBookPart()
    {
        $doc = $this->createBookPart();
        $this->handleBookPart($doc);
    }

    public function testCreateTagsForCustomTypeBookPart()
    {
        $this->htmlMetaTags->setConfig($this->adjustConfiguration(
            ['metatags' => ['mapping' => ['book_part' => ['customdoctype']]]]
        ));

        $doc = $this->createTestDoc('customdoctype');
        $this->handleBookPart($doc);
    }

    /**
     * @param DocumentInterface $doc
     */
    private function handleBookPart($doc)
    {
        $result = $this->htmlMetaTags->createTags($doc);

        $this->assertCount(55, $result);
        $this->assertCommonMetaTags($result, $doc->getId());
        $this->assertPages($result);
        $this->assertParentTitle($result, 'inbook');
    }

    public function testCreateTagsForOther()
    {
        $doc   = $this->createOther();
        $docId = $doc->getId();

        $result = $this->htmlMetaTags->createTags($doc);

        $this->assertCount(51, $result);
        $this->assertCommonMetaTags($result, $docId);
        $this->assertIssn($result);
    }

    /**
     * @param array $tags
     * @param int   $docId
     */
    private function assertCommonMetaTags($tags, $docId)
    {
        $this->assertAuthors($tags);
        $this->assertDates($tags);
        $this->assertMainTitles($tags);
        $this->assertPublisher($tags);
        $this->assertCommonIdentifiers($tags, $docId);
        $this->assertSubjects($tags);
        $this->assertLanguage($tags);
        $this->assertFile($tags, $docId);
        $this->assertFrontdoorUrl($tags, $docId);
        $this->assertAbstract($tags);
        $this->assertUrn($tags, $docId);
        $this->assertLicenceLink($tags);
    }

    /**
     * @param array $tags
     */
    private function assertAuthors($tags)
    {
        $this->assertContains(['DC.creator', 'lastName-0, firstName-0'], $tags);
        $this->assertContains(['citation_author', 'lastName-0, firstName-0'], $tags);
        $this->assertContains(['author', 'lastName-0, firstName-0'], $tags);

        $this->assertContains(['DC.creator', 'lastName-1'], $tags);
        $this->assertContains(['citation_author', 'lastName-1'], $tags);
        $this->assertContains(['author', 'lastName-1'], $tags);

        $this->assertContains(['DC.creator', 'lastName-2, firstName-2'], $tags);
        $this->assertContains(['citation_author', 'lastName-2, firstName-2'], $tags);
        $this->assertContains(['author', 'lastName-2, firstName-2'], $tags);
    }

    /**
     * @param array $tags
     */
    private function assertDates($tags)
    {
        $currDateStr = $this->currDate->getYear() . '-' . $this->currDate->getMonth() . '-' . $this->currDate->getDay();
        $this->assertContains(['DC.date', $currDateStr], $tags);
        $this->assertContains(['DC.issued', $currDateStr], $tags);
        $this->assertContains(['citation_date', $currDateStr], $tags);
        $this->assertContains(['citation_publication_date', $currDateStr], $tags);
    }

    /**
     * @param array $tags
     */
    private function assertMainTitles($tags)
    {
        $this->assertContains(['DC.title', 'titlemain-deu', ['lang' => 'de']], $tags);
        $this->assertContains(['citation_title', 'titlemain-deu', ['lang' => 'de']], $tags);
        $this->assertContains(['title', 'titlemain-deu', ['lang' => 'de']], $tags);

        $this->assertContains(['DC.title', 'titlemain-eng : titlesub-eng', ['lang' => 'en']], $tags);
        $this->assertContains(['citation_title', 'titlemain-eng : titlesub-eng', ['lang' => 'en']], $tags);
        $this->assertContains(['title', 'titlemain-eng : titlesub-eng', ['lang' => 'en']], $tags);
    }

    /**
     * @param array $tags
     */
    private function assertPublisher($tags)
    {
        $this->assertContains(['DC.publisher', 'publisherName'], $tags);
        $this->assertContains(['citation_publisher', 'publisherName'], $tags);
    }

    /**
     * @param array  $tags
     * @param string $publicationType
     */
    private function assertParentTitle($tags, $publicationType)
    {
        $this->assertContains(['DC.relation.ispartof', 'titleparent-eng'], $tags);
        $this->assertContains(["citation_{$publicationType}_title", 'titleparent-eng'], $tags);
    }

    /**
     * @param array $tags
     */
    private function assertVolumeAndIssue($tags)
    {
        $this->assertContains(['DC.citation.volume', 'volume'], $tags);
        $this->assertContains(['citation_volume', 'volume'], $tags);

        $this->assertContains(['DC.citation.issue', 'issue'], $tags);
        $this->assertContains(['citation_issue', 'issue'], $tags);
    }

    /**
     * @param array $tags
     */
    private function assertPages($tags)
    {
        $this->assertContains(['DC.citation.spage', 'pageFirst'], $tags);
        $this->assertContains(['citation_firstpage', 'pageFirst'], $tags);

        $this->assertContains(['DC.citation.epage', 'pageLast'], $tags);
        $this->assertContains(['citation_lastpage', 'pageLast'], $tags);
    }

    /**
     * @param array $tags
     * @param int   $docId
     */
    private function assertCommonIdentifiers($tags, $docId)
    {
        $this->assertContains(['DC.identifier', 'doi' . $docId], $tags);
        $this->assertContains(['citation_doi', 'doi' . $docId], $tags);

        $this->assertContains(['DC.identifier', 'isbn'], $tags);
        $this->assertContains(['citation_isbn', 'isbn'], $tags);
    }

    /**
     * @param array $tags
     * @param int   $docId
     */
    private function assertFrontdoorUrl($tags, $docId)
    {
        $this->assertContains(['DC.identifier', 'http://localhost/opus/frontdoor/index/index/docId/' . $docId], $tags);
        $this->assertContains(['citation_abstract_html_url', 'http://localhost/opus/frontdoor/index/index/docId/' . $docId], $tags);
    }

    /**
     * @param array $tags
     */
    private function assertIssn($tags)
    {
        $this->assertContains(['DC.identifier', 'issn'], $tags);
        $this->assertContains(['citation_issn', 'issn'], $tags);
    }

    /**
     * @param array $tags
     * @param int   $docId
     */
    private function assertUrn($tags, $docId)
    {
        $this->assertContains(['DC.identifier', 'urn' . $docId], $tags);
        $this->assertContains(['DC.identifier', 'https://nbn-resolving.org/urn' . $docId], $tags);
    }

    /**
     * @param array $tags
     */
    private function assertSubjects($tags)
    {
        $this->assertContains(['DC.subject', 'value1'], $tags);
        $this->assertContains(['citation_keywords', 'value1'], $tags);

        $this->assertContains(['DC.subject', 'value2'], $tags);
        $this->assertContains(['citation_keywords', 'value2'], $tags);

        $this->assertContains(['keywords', 'value1, value2'], $tags);
    }

    /**
     * @param array $tags
     */
    private function assertLanguage($tags)
    {
        $this->assertContains(['DC.language', 'deu'], $tags);
        $this->assertContains(['citation_language', 'deu'], $tags);
    }

    /**
     * @param array $tags
     */
    private function assertAbstract($tags)
    {
        $this->assertContains(['DC.description', 'abstract1-deu', ['lang' => 'de']], $tags);
        $this->assertContains(['description', 'abstract1-deu', ['lang' => 'de']], $tags);

        $this->assertContains(['DC.description', 'abstract2-deu', ['lang' => 'de']], $tags);
        $this->assertContains(['description', 'abstract2-deu', ['lang' => 'de']], $tags);
    }

    /**
     * @param array $tags Tags
     */
    private function assertLicenceLink($tags)
    {
        $this->assertContains(['DC.rights', 'https://creativecommons.org/licenses/by-nc/4.0/deed.de'], $tags);
    }

    /**
     * @param array $tags
     * @param int   $docId
     */
    private function assertFile($tags, $docId)
    {
        $fileUrl = 'http://localhost/opus/files/' . $docId . '/HtmlMetaTagsTest.pdf';
        $this->assertContains(['DC.identifier', $fileUrl], $tags);
        $this->assertContains(['citation_pdf_url', $fileUrl], $tags);

        $fileUrl = 'http://localhost/opus/files/' . $docId . '/HtmlMetaTagsTest.ps';
        $this->assertContains(['DC.identifier', $fileUrl], $tags);
        $this->assertContains(['citation_ps_url', $fileUrl], $tags);

        $fileUrl = 'http://localhost/opus/files/' . $docId . '/HtmlMetaTagsTest.txt';
        $this->assertContains(['DC.identifier', $fileUrl], $tags);
        $this->assertContains(['citation_pdf_url', $fileUrl], $tags);
    }

    /**
     * @param DocumentInterface $doc
     * @param array             $tags
     */
    private function assertThesisPublisher($doc, $tags)
    {
        $thesisPublisher = $doc->getThesisPublisher();
        $publisherName   = $thesisPublisher[0]->getModel()->getName();
        $this->assertContains(['DC.publisher', $publisherName], $tags);
        $this->assertContains(['citation_dissertation_institution', $publisherName], $tags);
    }

    /**
     * @param array  $tags
     * @param string $docType
     */
    private function assertDocumentType($tags, $docType)
    {
        $this->assertContains(['citation_dissertation_name', $docType], $tags);
    }

    /**
     * @param array  $tags
     * @param string $value
     */
    private function assertInstitution($tags, $value)
    {
        $this->assertContains(['DC.publisher', $value], $tags);
        $this->assertContains(['citation_technical_report_institution', $value], $tags);
    }

    /**
     * @return DocumentInterface
     */
    private function createJournalPaper()
    {
        return $this->createTestDoc('article');
    }

    /**
     * @return DocumentInterface
     * @throws ModelException
     */
    private function createConferencePaper()
    {
        return $this->createTestDoc('conferenceobject');
    }

    /**
     * @return DocumentInterface
     * @throws ModelException
     */
    private function createThesis()
    {
        return $this->createTestDoc('bachelorthesis');
    }

    /**
     * @return DocumentInterface
     * @throws ModelException
     */
    private function createWorkingPaper()
    {
        return $this->createTestDoc('workingpaper');
    }

    /**
     * @return DocumentInterface
     * @throws ModelException
     */
    private function createBook()
    {
        return $this->createTestDoc('book');
    }

    /**
     * @return DocumentInterface
     * @throws ModelException
     */
    private function createBookPart()
    {
        return $this->createTestDoc('bookpart');
    }

    /**
     * @return DocumentInterface
     * @throws ModelException
     */
    private function createOther()
    {
        return $this->createTestDoc('unknowndoctype');
    }

    /**
     * @param string $docType
     * @return DocumentInterface
     * @throws ModelException
     */
    private function createTestDoc($docType)
    {
        $doc = $this->createTestDocument();
        $doc->setType($docType);
        $doc->setLanguage('deu');
        $doc->setPublisherName('publisherName');
        $doc->setVolume('volume');
        $doc->setIssue('issue');
        $doc->setPageFirst('pageFirst');
        $doc->setPageLast('pageLast');
        $doc->setCreatingCorporation('crea');
        $doc->setContributingCorporation('cont');
        $doc->setPublishedDate($this->currDate);
        $doc->setServerState('published');
        // hier bereits store aufrufen, weil wir die DocId für URN und DOI brauchen
        $docId = $doc->store();

        $doc = Document::get($docId);
        $this->addAuthors($doc, 3);
        $this->addTitles($doc);
        $this->addAbstracts($doc);
        $this->addIdentifiers($doc);
        $this->addSubjects($doc);
        $this->addFile($doc);
        $this->addThesisPublisher($doc);
        $this->addLicence($doc);
        $doc->store();

        return Document::get($docId);
    }

    /**
     * @param DocumentInterface $doc
     * @param int               $num
     */
    private function addAuthors($doc, $num)
    {
        $authors = [];
        for ($i = 0; $i < $num; $i++) {
            $author = Person::new();
            $author->setLastName('lastName-' . $i);
            if ($i % 2 === 0) {
                // nur jeder zweite Autor bekommt einen Vornamen
                $author->setFirstName('firstName-' . $i);
            }
            $authors[] = $author;
        }
        $doc->setPersonAuthor($authors);
    }

    /**
     * @param DocumentInterface $doc
     */
    private function addTitles($doc)
    {
        $titles = [];
        $title  = Title::new();
        $title->setType('main');
        $title->setLanguage('deu');
        $title->setValue('titlemain-deu');
        $titles[] = $title;

        $title = Title::new();
        $title->setType('main');
        $title->setLanguage('eng');
        $title->setValue('titlemain-eng');
        $titles[] = $title;

        $doc->setTitleMain($titles);

        $titles = [];
        $title  = Title::new();
        $title->setType('sub');
        $title->setLanguage('eng');
        $title->setValue('titlesub-eng');
        $titles[] = $title;

        $doc->setTitleSub($titles);

        $titles = [];
        $title  = Title::new();
        $title->setType('parent');
        $title->setLanguage('deu');
        $title->setValue('titleparent-eng');
        $titles[] = $title;

        $doc->setTitleParent($titles);
    }

    /**
     * @param DocumentInterface $doc
     */
    private function addAbstracts($doc)
    {
        $abstracts = [];

        $abstr = TitleAbstract::new();
        $abstr->setType('abstract');
        $abstr->setLanguage('deu');
        $abstr->setValue('abstract1-deu');
        $abstracts[] = $abstr;

        $abstr = TitleAbstract::new();
        $abstr->setType('abstract');
        $abstr->setLanguage('deu');
        $abstr->setValue('abstract2-deu');
        $abstracts[] = $abstr;

        $doc->setTitleAbstract($abstracts);
    }

    /**
     * @param DocumentInterface $doc
     */
    private function addIdentifiers($doc)
    {
        $identifers = [];

        $identifer = Identifier::new();
        $identifer->setType('doi');
        $identifer->setValue('doi' . $doc->getId());
        $identifers[] = $identifer;

        $identifer = Identifier::new();
        $identifer->setType('urn');
        $identifer->setValue('urn' . $doc->getId());
        $identifers[] = $identifer;

        $identifer = Identifier::new();
        $identifer->setType('issn');
        $identifer->setValue('issn');
        $identifers[] = $identifer;

        $identifer = Identifier::new();
        $identifer->setType('isbn');
        $identifer->setValue('isbn');
        $identifers[] = $identifer;

        $doc->setIdentifier($identifers);
    }

    /**
     * @param DocumentInterface $doc
     */
    private function addSubjects($doc)
    {
        $subjects = [];

        $subject = Subject::new();
        $subject->setType('type1');
        $subject->setValue('value1');
        $subject->setLanguage('deu');
        $subjects[] = $subject;

        $subject = Subject::new();
        $subject->setType('type2');
        $subject->setValue('value2');
        $subject->setLanguage('deu');
        $subjects[] = $subject;

        $doc->setSubject($subjects);
    }

    /**
     * @param DocumentInterface $doc
     */
    private function addLicence($doc)
    {
        $licence = Licence::get(3);
        $doc->setLicence($licence);
    }

    /**
     * @param DocumentInterface $doc
     */
    private function addFile($doc)
    {
        $config = $this->getConfig();
        $path   = $config->workspacePath . DIRECTORY_SEPARATOR . uniqid();
        mkdir($path, 0777, true);

        $doc->addFile($this->createFile($path, 'HtmlMetaTagsTest.pdf', "%PDF-1.1\ntest"));
        $doc->addFile($this->createFile($path, 'HtmlMetaTagsTest.ps', "%!PS-Adobe-2.0\ntest"));
        $doc->addFile($this->createFile($path, 'HtmlMetaTagsTest.txt', "test"));

        $file = $this->createFile($path, 'invisible.txt', "invisible file");
        $file->setVisibleInFrontdoor(0);
        $doc->addFile($file);
    }

    /**
     * @param string $path
     * @param string $fileName
     * @param string $header
     * @return FileInterface
     * @throws ModelException
     * @throws Zend_Exception
     */
    private function createFile($path, $fileName, $header)
    {
        $filepath = $path . DIRECTORY_SEPARATOR . $fileName;
        $fp       = fopen($filepath, "wb");
        fwrite($fp, $header);
        fclose($fp);

        return $this->createOpusTestFile($fileName, $filepath);
    }

    /**
     * @param DocumentInterface $doc
     */
    private function addThesisPublisher($doc)
    {
        $institute = DnbInstitute::get(3);
        $doc->setThesisPublisher($institute);
    }

    public function testGetMetatagsType()
    {
        $metaTags = $this->htmlMetaTags;

        $document = Document::get(146);
        $book     = $this->createBook();

        $metaTags->getMetatagsType($document);

        $this->assertEquals('thesis', $metaTags->getMetatagsType($document));
        $this->assertEquals('book', $metaTags->getMetatagsType($book));
    }

    public function testGetMappingConfig()
    {
        $metaTags = $this->htmlMetaTags;

        $config = $metaTags->getMappingConfig();

        $this->assertCount(16, $config);
        $this->assertCount(6, array_unique($config));

        // a sample check
        $this->assertArrayHasKey('article', $config);
        $this->assertEquals('journal_paper', $config['article']);
    }

    public function testGetMappingConfigCustomDocumentType()
    {
        $this->htmlMetaTags->setConfig($this->adjustConfiguration([
            'metatags' => ['mapping' => ['book' => ['mybooktype']]],
        ]));

        $metaTags = $this->htmlMetaTags;

        $config = $metaTags->getMappingConfig();

        $this->assertArrayHasKey('mybooktype', $config);
        $this->assertEquals('book', $config['mybooktype']);
    }

    public function testGetMappingConfigDefaultOverride()
    {
        $this->htmlMetaTags->setConfig($this->adjustConfiguration([
            'metatags' => ['mapping' => ['book' => ['article']]],
        ]));

        $metaTags = $this->htmlMetaTags;

        $config = $metaTags->getMappingConfig();

        $this->assertArrayHasKey('article', $config);
        $this->assertEquals('book', $config['article']);
    }
}
