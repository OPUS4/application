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
 * @package     Tests
 * @author      Sascha Szott <opus-development@saschaszott.de>
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Frontdoor_Model_HtmlMetaTagsTest extends ControllerTestCase
{

    protected $additionalResources = ['database', 'view'];

    /**
     * @var Frontdoor_Model_HtmlMetaTags
     */
    private $htmlMetaTags;

    /**
     * @var Opus_Date
     */
    private $currDate;

    /**
     * @var Opus_Document
     */
    private $testDoc;

    public function setUp()
    {
        parent::setUp();
        $this->htmlMetaTags = new Frontdoor_Model_HtmlMetaTags(
            Zend_Registry::get('Zend_Config'), 'http://localhost/opus');

        $this->currDate = new Opus_Date(new Zend_Date());
    }

    public function tearDown()
    {
        if (! is_null($this->testDoc)) {
            $this->testDoc->deletePermanent();
        }
        $this->deleteTempFiles();
        parent::tearDown();
    }

    public function testCreateTagsForMinimalDocument()
    {
        $this->testDoc = new Opus_Document();
        $this->testDoc->setLanguage('deu');
        $this->testDoc->setPublishedYear('2048');
        $docId = $this->testDoc->store();

        $result = $this->htmlMetaTags->createTags($this->testDoc);

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
        $this->testDoc = $this->createJournalPaper();
        $docId = $this->testDoc->getId();

        $result = $this->htmlMetaTags->createTags($this->testDoc);

        $this->assertCount(59, $result);
        $this->assertCommonMetaTags($result, $docId);
        $this->assertParentTitle($result, 'journal');
        $this->assertVolumeAndIssue($result);
        $this->assertPages($result);
        $this->assertIssn($result);
    }

    public function testCreateTagsForConferencePaper()
    {
        $this->testDoc = $this->createConferencePaper();
        $docId = $this->testDoc->getId();

        $result = $this->htmlMetaTags->createTags($this->testDoc);

        $this->assertCount(59, $result);
        $this->assertCommonMetaTags($result, $docId);
        $this->assertParentTitle($result, 'conference');
        $this->assertVolumeAndIssue($result);
        $this->assertPages($result);
        $this->assertIssn($result);
    }

    public function testCreateTagsForThesis()
    {
        $this->testDoc = $this->createThesis();
        $docId = $this->testDoc->getId();

        $result = $this->htmlMetaTags->createTags($this->testDoc);

        $this->assertCount(50, $result);
        $this->assertCommonMetaTags($result, $docId);
        $this->assertThesisPublisher($result);
        $this->assertDocumentType($result);
    }

    public function testCreateTagsForWorkingPaper()
    {
        $this->testDoc = $this->createWorkingPaper();
        $docId = $this->testDoc->getId();

        $result = $this->htmlMetaTags->createTags($this->testDoc);

        $this->assertCount(55, $result);
        $this->assertCommonMetaTags($result, $docId);
        $this->assertVolumeAndIssue($result);
        $this->assertIssn($result);
        $this->assertInstitution($result, 'creatingCorporation');
    }

    public function testCreateTagsForWorkingPaperWithContributingCorporation()
    {
        $this->testDoc = $this->createWorkingPaper();
        $this->testDoc->setCreatingCorporation('');
        $this->testDoc->store();

        $result = $this->htmlMetaTags->createTags($this->testDoc);

        // prüft nur, ob citation_technical_report_institution richtig gesetzt
        $this->assertInstitution($result, 'contributingCorporation');
    }

    public function testCreateTagsForWorkingPaperWithPublisher()
    {
        $this->testDoc = $this->createWorkingPaper();
        $this->testDoc->setCreatingCorporation('');
        $this->testDoc->setContributingCorporation('');
        $this->testDoc->store();

        $result = $this->htmlMetaTags->createTags($this->testDoc);

        // prüft nur, ob citation_technical_report_institution richtig gesetzt
        $this->assertInstitution($result, 'publisherName');
    }

    public function testCreateTagsForBook()
    {
        $this->testDoc = $this->createBook();
        $docId = $this->testDoc->getId();

        $result = $this->htmlMetaTags->createTags($this->testDoc);

        $this->assertCount(49, $result);
        $this->assertCommonMetaTags($result, $docId);
        $this->assertParentTitle($result, 'inbook');
    }

    public function testCreateTagsForBookPart()
    {
        $this->testDoc = $this->createBookPart();
        $docId = $this->testDoc->getId();

        $result = $this->htmlMetaTags->createTags($this->testDoc);

        $this->assertCount(53, $result);
        $this->assertCommonMetaTags($result, $docId);
        $this->assertPages($result);
        $this->assertParentTitle($result, 'inbook');
    }

    public function testCreateTagsForOther()
    {
        $this->testDoc = $this->createOther();
        $docId = $this->testDoc->getId();

        $result = $this->htmlMetaTags->createTags($this->testDoc);

        $this->assertCount(49, $result);
        $this->assertCommonMetaTags($result, $docId);
        $this->assertIssn($result);
    }

    /**
     * @param array $tags
     * @param int $docId
     */
    private function assertCommonMetaTags($tags, $docId)
    {
        $this->assertAuthors($tags);
        $this->assertDates($tags);
        $this->assertMainTitles($tags);
        $this->assertPublisher($tags);
        $this->assertCommonIdentifiers($tags);
        $this->assertSubjects($tags);
        $this->assertLanguage($tags);
        $this->assertFile($tags, $docId);
        $this->assertFrontdoorUrl($tags, $docId);
        $this->assertAbstract($tags);
        $this->assertUrn($tags);
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
        $this->assertContains(['DC.title', 'titlemain-deu'], $tags);
        $this->assertContains(['citation_title', 'titlemain-deu'], $tags);
        $this->assertContains(['title', 'titlemain-deu'], $tags);


        $this->assertContains(['DC.title', 'titlemain-eng : titlesub-eng'], $tags);
        $this->assertContains(['citation_title', 'titlemain-eng : titlesub-eng'], $tags);
        $this->assertContains(['title', 'titlemain-eng : titlesub-eng'], $tags);
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
     * @param array $tags
     * @param string $publicationType
     */
    private function assertParentTitle($tags, $publicationType)
    {
        $this->assertContains(['DC.relation.ispartof', 'titleparent-eng'], $tags);
        $this->assertContains(["citation_${publicationType}_title", 'titleparent-eng'], $tags);
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
     */
    private function assertCommonIdentifiers($tags)
    {
        $this->assertContains(['DC.identifier', 'doi'], $tags);
        $this->assertContains(['citation_doi', 'doi'], $tags);

        $this->assertContains(['DC.identifier', 'isbn'], $tags);
        $this->assertContains(['citation_isbn', 'isbn'], $tags);
    }

    /**
     * @param array $tags
     * @param int $docId
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
     */
    private function assertUrn($tags)
    {
        $this->assertContains(['DC.identifier', 'urn'], $tags);
        $this->assertContains(['DC.identifier', 'https://nbn-resolving.org/urn'], $tags);
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
        $this->assertContains(['DC.description', 'abstract1-deu'], $tags);
        $this->assertContains(['description', 'abstract1-deu'], $tags);

        $this->assertContains(['DC.description', 'abstract2-deu'], $tags);
        $this->assertContains(['description', 'abstract2-deu'], $tags);
    }

    /**
     * @param tags $tags
     */
    private function assertLicenceLink($tags)
    {
        $this->assertContains(['DC.rights', 'https://creativecommons.org/licenses/by-nc/4.0/deed.de'], $tags);
    }

    /**
     * @param array $tags
     * @param int $docId
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
     * @param array $tags
     */
    private function assertThesisPublisher($tags)
    {
        $thesisPublisher = $this->testDoc->getThesisPublisher();
        $publisherName = $thesisPublisher[0]->getModel()->getName();
        $this->assertContains(['DC.publisher', $publisherName], $tags);
        $this->assertContains(['citation_dissertation_institution', $publisherName], $tags);
    }

    /**
     * @param array $tags
     */
    private function assertDocumentType($tags)
    {
        $this->assertContains(['citation_dissertation_name', 'bachelorthesis'], $tags);
    }

    /**
     * @param array $tags
     */
    private function assertInstitution($tags, $value)
    {
        $this->assertContains(['DC.publisher', $value], $tags);
        $this->assertContains(['citation_technical_report_institution', $value], $tags);
    }

    /**
     * @return Opus_Document
     */
    private function createJournalPaper()
    {
        return $this->createTestDoc('article');
    }

    private function createConferencePaper()
    {
        return $this->createTestDoc('conferenceobject');
    }

    private function createThesis()
    {
        return $this->createTestDoc('bachelorthesis');
    }

    private function createWorkingPaper()
    {
        return $this->createTestDoc('workingpaper');
    }

    private function createBook()
    {
        return $this->createTestDoc('book');
    }

    private function createBookPart()
    {
        return $this->createTestDoc('bookpart');
    }

    private function createOther()
    {
        return $this->createTestDoc('unknowndoctype');
    }

    /**
     * @param string $docType
     * @return Opus_Document
     */
    private function createTestDoc($docType)
    {
        $doc = new Opus_Document();
        $doc->setType($docType);
        $doc->setLanguage('deu');
        $doc->setPublisherName('publisherName');
        $doc->setVolume('volume');
        $doc->setIssue('issue');
        $doc->setPageFirst('pageFirst');
        $doc->setPageLast('pageLast');
        $doc->setCreatingCorporation('creatingCorporation');
        $doc->setContributingCorporation('contributingCorporation');
        $doc->setPublishedDate($this->currDate);
        $doc->setServerState('published');

        $this->addAuthors($doc, 3);
        $this->addTitles($doc);
        $this->addAbstracts($doc);
        $this->addIdentifiers($doc);
        $this->addSubjects($doc);
        $this->addFile($doc);
        $this->addThesisPublisher($doc);
        $this->addLicence($doc);

        $docId = $doc->store();
        return new Opus_Document($docId);
    }

    /**
     * @param Opus_Document $doc
     * @param int $num
     */
    private function addAuthors($doc, $num)
    {
        $authors = [];
        for ($i = 0; $i < $num; $i++)
        {
            $author = new Opus_Person();
            $author->setLastName('lastName-' . $i);
            if ($i % 2 == 0) {
                // nur jeder zweite Autor bekommt einen Vornamen
                $author->setFirstName('firstName-' . $i);
            }
            $authors[] = $author;
        }
        $doc->setPersonAuthor($authors);
    }

    /**
     * @param Opus_Document $doc
     */
    private function addTitles($doc)
    {
        $titles = [];
        $title = new Opus_Title();
        $title->setType('main');
        $title->setLanguage('deu');
        $title->setValue('titlemain-deu');
        $titles[] = $title;

        $title = new Opus_Title();
        $title->setType('main');
        $title->setLanguage('eng');
        $title->setValue('titlemain-eng');
        $titles[] = $title;

        $doc->setTitleMain($titles);


        $titles = [];
        $title = new Opus_Title();
        $title->setType('sub');
        $title->setLanguage('eng');
        $title->setValue('titlesub-eng');
        $titles[] = $title;

        $doc->setTitleSub($titles);


        $titles = [];
        $title = new Opus_Title();
        $title->setType('parent');
        $title->setLanguage('deu');
        $title->setValue('titleparent-eng');
        $titles[] = $title;

        $doc->setTitleParent($titles);
    }

    /**
     * @param Opus_Document $doc
     */
    private function addAbstracts($doc)
    {
        $abstracts = [];

        $abstr = new Opus_TitleAbstract();
        $abstr->setType('abstract');
        $abstr->setLanguage('deu');
        $abstr->setValue('abstract1-deu');
        $abstracts[] = $abstr;

        $abstr = new Opus_TitleAbstract();
        $abstr->setType('abstract');
        $abstr->setLanguage('deu');
        $abstr->setValue('abstract2-deu');
        $abstracts[] = $abstr;

        $doc->setTitleAbstract($abstracts);
    }

    /**
     * @param Opus_Document $doc
     */
    private function addIdentifiers($doc)
    {
        $identifers = [];

        $identifer = new Opus_Identifier();
        $identifer->setType('doi');
        $identifer->setValue('doi');
        $identifers[] = $identifer;

        $identifer = new Opus_Identifier();
        $identifer->setType('urn');
        $identifer->setValue('urn');
        $identifers[] = $identifer;

        $identifer = new Opus_Identifier();
        $identifer->setType('issn');
        $identifer->setValue('issn');
        $identifers[] = $identifer;

        $identifer = new Opus_Identifier();
        $identifer->setType('isbn');
        $identifer->setValue('isbn');
        $identifers[] = $identifer;

        $doc->setIdentifier($identifers);
    }

    /**
     * @param Opus_Document $doc
     */
    private function addSubjects($doc)
    {
        $subjects = [];

        $subject = new Opus_Subject();
        $subject->setType('type1');
        $subject->setValue('value1');
        $subject->setLanguage('deu');
        $subjects[] = $subject;

        $subject = new Opus_Subject();
        $subject->setType('type2');
        $subject->setValue('value2');
        $subject->setLanguage('deu');
        $subjects[] = $subject;

        $doc->setSubject($subjects);
    }

    /**
     * @param Opus_Document $doc
     */
    private function addLicence($doc)
    {
        $licence = new Opus_Licence(3);
        $doc->setLicence($licence);
    }

    /**
     * @param Opus_Document $doc
     */
    private function addFile($doc)
    {
        $config = Zend_Registry::get('Zend_Config');
        $path = $config->workspacePath . DIRECTORY_SEPARATOR . uniqid();
        mkdir($path, 0777, true);

        $doc->addFile($this->createFile($path, 'HtmlMetaTagsTest.pdf', "%PDF-1.1\ntest"));
        $doc->addFile($this->createFile($path, 'HtmlMetaTagsTest.ps', "%!PS-Adobe-2.0\ntest"));
        $doc->addFile($this->createFile($path, 'HtmlMetaTagsTest.txt', "test"));

        $file = $this->createFile($path, 'invisible.txt', "invisible file");
        $file->setVisibleInFrontdoor(0);
        $doc->addFile($file);
    }

    private function createFile($path, $fileName, $header)
    {
        $filepath = $path . DIRECTORY_SEPARATOR . $fileName;
        $fp = fopen($filepath,"wb");
        fwrite($fp, $header);
        fclose($fp);

        $file = $this->createTestFile($fileName, $filepath);
        return $file;
    }

    /**
     * @param Opus_Document $doc
     */
    private function addThesisPublisher($doc)
    {
        $institute = new Opus_DnbInstitute(3);
        $doc->setThesisPublisher($institute);
    }

}
