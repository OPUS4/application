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

use Opus\Common\DocumentInterface;

/**
 * TODO new types have to be added as functions -> make extendable? how is it used when rendering?
 *      Basically each type could be a separate class. Refactor for later so new types or metatags won't necessarily
 *      require changing core classes (this class here).
 * TODO how to handle configuration - during production requests it is not a problem, but for tests the configuration
 *      is sometimes manipulated and setting the configuration in the constructor means that those updates only work
 *      if the same configuration object is manipulated, which isn't always possible - one solution would be to make
 *      sure, that the configuration for tests is always modifiable
 */
class Frontdoor_Model_HtmlMetaTags
{
    /** @var Zend_Config */
    private $config;

    /** @var string */
    private $fullUrl;

    /**
     * Mapping of document types to meta tags types.
     *
     * @var array
     */
    private $mapping;

    /**
     * @param Zend_Config $config
     * @param string      $fullUrl
     */
    public function __construct($config, $fullUrl)
    {
        $this->config  = $config;
        $this->fullUrl = $fullUrl;
    }

    /**
     * @param DocumentInterface $document
     * @return array Array mit Metatag-Paaren
     */
    public function createTags($document)
    {
        $metas = [];
        $this->handleAuthors($document, $metas);
        $this->handleDates($document, $metas);
        $this->handleTitles($document, $metas);
        $this->handleSimpleAttribute($document->getPublisherName(), ['DC.publisher', 'citation_publisher'], $metas);

        if ($this->isJournalPaper($document)) {
            $this->handleJournalTitle($document, $metas);
        }

        if ($this->isJournalPaper($document) || $this->isConferencePaper($document) || $this->isWorkingPaper($document)) {
            $this->handleSimpleAttribute($document->getVolume(), ['DC.citation.volume', 'citation_volume'], $metas);
            $this->handleSimpleAttribute($document->getIssue(), ['DC.citation.issue', 'citation_issue'], $metas);
        }

        if ($this->isJournalPaper($document) || $this->isConferencePaper($document) || $this->isBookPart($document)) {
            $this->handleSimpleAttribute($document->getPageFirst(), ['DC.citation.spage', 'citation_firstpage'], $metas);
            $this->handleSimpleAttribute($document->getPageLast(), ['DC.citation.epage', 'citation_lastpage'], $metas);
        }

        $this->handleIdentifierDoi($document, $metas);

        if (
            $this->isJournalPaper($document) ||
            $this->isConferencePaper($document) ||
            $this->isWorkingPaper($document) ||
            $this->isOther($document)
        ) {
            $this->handleIdentifierIssn($document, $metas);
        }

        $this->handleIdentifierIsbn($document, $metas);
        $this->handleKeywords($document, $metas);

        if ($this->isThesis($document)) {
            $this->handleSimpleAttribute($document->getType(), ['citation_dissertation_name'], $metas);
            $this->handleThesisPublisher($document, $metas);
        }

        if ($this->isWorkingPaper($document)) {
            $this->handleInstitution($document, $metas);
        }

        $this->handleSimpleAttribute($document->getLanguage(), ['DC.language', 'citation_language'], $metas);

        if ($this->isConferencePaper($document)) {
            $this->handleConferenceTitle($document, $metas);
        }

        if ($this->isBook($document) || $this->isBookPart($document)) {
            $this->handleBookTitle($document, $metas);
        }

        $this->handleFulltextUrls($document, $metas);
        $this->handleFrontdoorUrl($document, $metas);
        $this->handleAbstracts($document, $metas);
        $this->handleIdentifierUrn($document, $metas);
        $this->handleLicences($document, $metas);
        return $metas;
    }

    /**
     * @param DocumentInterface $document
     * @param array             $metas Array mit Metatag-Paaren
     */
    private function handleAuthors($document, &$metas)
    {
        foreach ($document->getPersonAuthor() as $author) {
            $lastname = trim($author->getLastName());
            if ($lastname !== '') {
                $name = $lastname;

                $firstname = $author->getFirstName();
                $firstname = $firstname !== null ? trim($firstname) : null;
                if ($firstname !== null && $firstname !== '') {
                    $name .= ", " . $firstname;
                }

                $metas[] = ['DC.creator', $name];
                $metas[] = ['citation_author', $name];
                $metas[] = ['author', $name];
            }
        }
    }

    /**
     * @param DocumentInterface $document
     * @param array             $metas Array mit Metatag-Paaren
     */
    private function handleDates($document, &$metas)
    {
        $dateStr = null;

        $datePublished = $document->getPublishedDate();
        if ($datePublished !== null) {
            $dateStr = $datePublished->getDateTime()->format('Y-m-d');
        } else {
            $dateStr = $document->getPublishedYear();
        }

        if ($dateStr !== null) {
            $metas[] = ["DC.date", $dateStr];
            $metas[] = ["DC.issued", $dateStr];
            $metas[] = ["citation_date", $dateStr];
            $metas[] = ["citation_publication_date", $dateStr];
        }
    }

    /**
     * @param DocumentInterface $document
     * @param array             $metas Array mit Metatag-Paaren
     */
    private function handleTitles($document, &$metas)
    {
        $subtitlesByLang = [];
        $subtitles       = $document->getTitleSub();
        if (! empty($subtitles)) {
            // Aufspaltung der Untertitel nach Sprache (eigentlich darf pro Sprache höchstens
            // ein Untertitel existieren)
            foreach ($subtitles as $subtitle) {
                $subtitleValue = trim($subtitle->getValue());
                if ($subtitleValue !== '') {
                    $lang = $subtitle->getLanguage();
                    if (array_key_exists($lang, $subtitlesByLang)) {
                        // eigentlich kann dieser Fall gar nicht auftreten, wenn die Eingabe der
                        // Untertitel über die Webapplikation geschieht, weil sich mehrere Untertitel
                        // in der gleichen Sprache nicht speichern lassen: für Robustheit wird
                        // dieser Fall hier aber dennoch behandelt
                        $subtitlesByLang[$lang][] = $subtitleValue;
                    } else {
                        $subtitlesByLang[$lang] = [$subtitleValue];
                    }
                }
            }
        }

        foreach ($document->getTitleMain() as $title) {
            $titleValue = trim($title->getValue());
            if ($titleValue !== '') {
                // gibt es einen "zugehörigen" Untertitel in der Sprache des Haupttitels, dann wird
                // der Untertitel mit Doppelpunkt an den Haupttitel angefügt
                $lang = $title->getLanguage();
                if (array_key_exists($lang, $subtitlesByLang)) {
                    $subtitles = $subtitlesByLang[$lang];
                    // i.d.R. enthält $subtitles nur ein Element
                    foreach ($subtitles as $subtitle) {
                        $titleValue .= " : " . $subtitle;
                    }
                }

                $helper = new Application_View_Helper_LanguageWebForm();
                $lang   = $helper->languageWebForm($lang);

                $metas[] = ['DC.title', $titleValue, ['lang' => $lang]];
                $metas[] = ['citation_title', $titleValue, ['lang' => $lang]];
                $metas[] = ['title', $titleValue, ['lang' => $lang]];
            }
        }
    }

    /**
     * @param DocumentInterface $document
     * @param array             $metas Array mit Metatag-Paaren
     */
    private function handleJournalTitle($document, &$metas)
    {
        foreach ($document->getTitleParent() as $titleParent) {
            $title = trim($titleParent->getValue());
            if ($title !== '') {
                $metas[] = ['DC.relation.ispartof', $title];
                $metas[] = ['citation_journal_title', $title];
            }
        }
    }

    /**
     * @param DocumentInterface $document
     * @param array             $metas Array mit Metatag-Paaren
     */
    private function handleAbstracts($document, &$metas)
    {
        foreach ($document->getTitleAbstract() as $abstract) {
            $abstractValue = trim($abstract->getValue());
            if ($abstractValue !== '') {
                $lang    = $abstract->getLanguage();
                $helper  = new Application_View_Helper_LanguageWebForm(); // TODO avoid object creation
                $lang    = $helper->languageWebForm($lang);
                $metas[] = ['DC.description', $abstractValue, ['lang' => $lang]];
                $metas[] = ['description', $abstractValue, ['lang' => $lang]];
                $metas[] = ['dcterms.abstract', $abstractValue, ['lang' => $lang]];
            }
        }
    }

    /**
     * @param DocumentInterface $document
     * @param array             $metas Array mit Metatag-Paaren
     */
    private function handleLicences($document, &$metas)
    {
        foreach ($document->getLicence() as $docLicence) {
            $metas[] = ['DC.rights', $docLicence->getModel()->getLinkLicence()];
        }
    }

    /**
     * @param DocumentInterface $document
     * @param array             $metas Array mit Metatag-Paaren
     */
    private function handleIdentifierUrn($document, &$metas)
    {
        $config = $this->getConfig();
        foreach ($document->getIdentifierUrn() as $identifier) {
            $identifierValue = trim($identifier->getValue());
            if ($identifierValue !== '') {
                $metas[] = ['DC.identifier', $identifierValue];
                if (isset($config, $config->urn->resolverUrl)) {
                    $metas[] = ['DC.identifier', $config->urn->resolverUrl . $identifierValue];
                }
            }
        }
    }

    /**
     * @param DocumentInterface $document
     * @param array             $metas Array mit Metatag-Paaren
     */
    private function handleIdentifierDoi($document, &$metas)
    {
        foreach ($document->getIdentifierDoi() as $identifier) {
            $identifierValue = trim($identifier->getValue());
            if ($identifierValue !== '') {
                $metas[] = ['DC.identifier', $identifierValue];
                $metas[] = ['citation_doi', $identifierValue];
            }
        }
    }

    /**
     * @param DocumentInterface $document
     * @param array             $metas Array mit Metatag-Paaren
     */
    private function handleIdentifierIssn($document, &$metas)
    {
        foreach ($document->getIdentifierIssn() as $identifier) {
            $identifierValue = trim($identifier->getValue());
            if ($identifierValue !== '') {
                $metas[] = ['DC.identifier', $identifierValue];
                $metas[] = ['citation_issn', $identifierValue];
            }
        }
    }

    /**
     * @param DocumentInterface $document
     * @param array             $metas Array mit Metatag-Paaren
     */
    private function handleIdentifierIsbn($document, &$metas)
    {
        foreach ($document->getIdentifierIsbn() as $identifier) {
            $identifierValue = trim($identifier->getValue());
            if ($identifierValue !== '') {
                $metas[] = ['DC.identifier', $identifierValue];
                $metas[] = ['citation_isbn', $identifierValue];
            }
        }
    }

    /**
     * @param DocumentInterface $document
     * @param array             $metas Array mit Metatag-Paaren
     */
    private function handleFrontdoorUrl($document, &$metas)
    {
        $frontdoorUrl = $this->fullUrl . '/frontdoor/index/index/docId/' . $document->getId();
        $metas[]      = ['DC.identifier', $frontdoorUrl];
        $metas[]      = ['citation_abstract_html_url', $frontdoorUrl];
    }

    /**
     * @param DocumentInterface $document
     * @param array             $metas Array mit Metatag-Paaren
     */
    private function handleFulltextUrls($document, &$metas)
    {
        if (Application_Xslt::embargoHasPassed($document)) {
            $config       = $this->getConfig();
            $baseUrlFiles = $this->fullUrl;
            if (isset($config, $config->deliver->url->prefix)) {
                $baseUrlFiles .= $config->deliver->url->prefix;
            } else {
                $baseUrlFiles .= '/files';
            }

            foreach ($document->getFile() as $file) {
                if (
                    (! $file->exists())
                    || (! $file->getVisibleInFrontdoor())
                    || (! Application_Xslt::fileAccessAllowed($file->getId()))
                ) {
                    continue;
                }

                $metas[] = ['DC.identifier', "$baseUrlFiles/" . $document->getId() . "/" . $file->getPathName()];

                $keyName = null;
                switch ($file->getMimeType()) {
                    case 'application/pdf':
                        $keyName = 'citation_pdf_url';
                        break;
                    case 'application/postscript':
                        $keyName = 'citation_ps_url';
                        break;
                    default:
                        $keyName = 'citation_pdf_url';
                        break;
                }
                if ($keyName !== null) {
                    $metas[] = [$keyName, "$baseUrlFiles/" . $document->getId() . "/" . $file->getPathName()];
                }
            }
        }
    }

    /**
     * @param DocumentInterface $document
     * @param array             $metas Array mit Metatag-Paaren
     */
    private function handleKeywords($document, &$metas)
    {
        $subjectsArray = [];
        foreach ($document->getSubject() as $subject) {
            $subjectValue = trim($subject->getValue());
            if ($subjectValue !== '') {
                $metas[]         = ['DC.subject', $subjectValue];
                $metas[]         = ['citation_keywords', $subjectValue];
                $subjectsArray[] = $subjectValue;
            }
        }
        if (! empty($subjectsArray)) {
            $subjectsArray = array_unique($subjectsArray);
            $metas[]       = ['keywords', implode(", ", $subjectsArray)];
        }
    }

    /**
     * @param string $value Wert des Metatags
     * @param array  $keys Array mit Metatag-Schlüsseln
     * @param array  $metas Array mit Metatag-Paaren
     */
    private function handleSimpleAttribute($value, $keys, &$metas)
    {
        $value = $value !== null ? trim($value) : '';
        if ($value !== '') {
            foreach ($keys as $key) {
                $metas[] = [$key, $value];
            }
        }
    }

    /**
     * @param DocumentInterface $document
     * @param array             $metas Array mit Metatag-Paaren
     */
    private function handleThesisPublisher($document, &$metas)
    {
        foreach ($document->getThesisPublisher() as $publisher) {
            $publisherName = trim($publisher->getName());
            if ($publisherName !== '') {
                $metas[] = ['DC.publisher', $publisherName];
                $metas[] = ['citation_dissertation_institution', $publisherName];
            }
        }
    }

    /**
     * @param DocumentInterface $document
     * @param array             $metas Array mit Metatag-Paaren
     */
    private function handleInstitution($document, &$metas)
    {
        $metaValue = trim($document->getCreatingCorporation() ?? '');
        if ($metaValue === '') {
            $metaValue = trim($document->getContributingCorporation() ?? '');
        }
        if ($metaValue === '') {
            $metaValue = trim($document->getPublisherName() ?? '');
        }
        if ($metaValue !== '') {
            $metas[] = ['DC.publisher', $metaValue];
            $metas[] = ['citation_technical_report_institution', $metaValue];
        }
    }

    /**
     * @param DocumentInterface $document
     * @param array             $metas Array mit Metatag-Paaren
     */
    private function handleConferenceTitle($document, &$metas)
    {
        foreach ($document->getTitleParent() as $title) {
            $titleTrimmed = trim($title->getValue());
            if ($titleTrimmed !== '') {
                $metas[] = ['DC.relation.ispartof', $titleTrimmed];
                $metas[] = ['citation_conference_title', $titleTrimmed];
            }
        }
    }

    /**
     * @param DocumentInterface $document
     * @param array             $metas Array mit Metatag-Paaren
     */
    private function handleBookTitle($document, &$metas)
    {
        foreach ($document->getTitleParent() as $title) {
            $titleTrimmed = trim($title->getValue());
            if ($titleTrimmed !== '') {
                $metas[] = ['DC.relation.ispartof', $titleTrimmed];
                $metas[] = ['citation_inbook_title', $titleTrimmed];
            }
        }
    }

    /**
     * @param DocumentInterface $document
     * @return bool
     */
    public function isJournalPaper($document)
    {
        return $this->getMetatagsType($document) === 'journal_paper';
    }

    /**
     * @param DocumentInterface $document
     * @return bool
     */
    public function isConferencePaper($document)
    {
        return $this->getMetatagsType($document) === 'conference_paper';
    }

    /**
     * @param DocumentInterface $document
     * @return bool
     */
    public function isThesis($document)
    {
        return $this->getMetatagsType($document) === 'thesis';
    }

    /**
     * @param DocumentInterface $document
     * @return bool
     */
    public function isWorkingPaper($document)
    {
        return $this->getMetatagsType($document) === 'working_paper';
    }

    /**
     * @param DocumentInterface $document
     * @return bool
     */
    public function isBook($document)
    {
        return $this->getMetatagsType($document) === 'book';
    }

    /**
     * @param DocumentInterface $document
     * @return bool
     */
    public function isBookPart($document)
    {
        return $this->getMetatagsType($document) === 'book_part';
    }

    /**
     * @param DocumentInterface $document
     * @return bool
     */
    public function isOther($document)
    {
        return $this->getMetatagsType($document) === 'other';
    }

    /**
     * @param DocumentInterface $document
     * @return string
     */
    public function getMetatagsType($document)
    {
        $mappingConfig = $this->getMappingConfig();
        $docType       = $document->getType();
        if (isset($mappingConfig[$docType])) {
            return $mappingConfig[$docType];
        } else {
            return 'other';
        }
    }

    /**
     * @return array
     */
    public function getMappingConfig()
    {
        $config = $this->getConfig();

        if ($this->mapping === null && isset($config)) {
            $mapping = [];

            // load default mappings
            if (isset($config->metatags->defaultMapping)) {
                $mapping = array_merge($mapping, $this->loadMapping($config->metatags->defaultMapping));
            }

            // load custom mapping
            if (isset($config->metatags->mapping)) {
                $mapping = array_merge($mapping, $this->loadMapping($config->metatags->mapping));
            }

            $this->mapping = $mapping;
        }

        return $this->mapping;
    }

    /**
     * @param Zend_Config $config
     * @return array
     */
    private function loadMapping($config)
    {
        $mapping = [];
        $types   = $config->toArray();
        foreach ($types as $metaTagType => $docTypes) {
            foreach ($types[$metaTagType] as $doctype) {
                $mapping[$doctype] = $metaTagType;
            }
        }
        return $mapping;
    }

    /**
     * @return Zend_Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param Zend_Config $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }
}
