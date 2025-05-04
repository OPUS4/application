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
 * @copyright   Copyright (c) 2025, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Document;
use Opus\Common\DocumentInterface;
use Opus\Common\Enrichment;
use Opus\Common\Model\ModelException;
use Opus\Common\Repository;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Validates all ORCID iDs in database.
 *
 * TODO move out of Application
 * TODO find better name
 * TODO generate report
 * TODO validate all ORCID iDs of a document at once (isValid & getValidationErrors)
 */
class Application_Orcid_ValidateAllIdentifierOrcid
{
    public const ORCID_ERROR_CODE = 'DOC_ERROR_ORCID';

    public const ERROR_ENRICHMENT = 'opus_document_errors';

    /** @var bool Enable tagging documents with invalid ORCID iDs. */
    private $tagDocuments = false;

    /** @var int[] Database IDs of processed documents. */
    private $taggedDocuments = [];

    /** @var int[] Clean documents. */
    private $cleanDocuments = [];

    /** @var OutputInterface */
    private $output;

    public function run()
    {
        $personRepository = Repository::getInstance()->getModelRepository(Person::class);

        $results = $personRepository->getAllIdentifierOrcid();

        $output = $this->getOutput();

        $progressBar = null;

        if ($this->tagDocuments) {
            $progressBar = new ProgressBar($output, count($results));
        }

        $validator = new Application_Form_Validate_Orcid();

        foreach ($results as $item) {
            $orcidId = $item['orcidId'];
            $docId   = $item['documentId'];

            $valid = $validator->isValid($orcidId);

            if (! $valid && ! in_array($docId, $this->taggedDocuments)) {
                $document = Document::get($docId);

                if ($this->tagDocuments) {
                    $this->addTag($document);

                    // Tag documents only once
                    $this->taggedDocuments[] = $docId;
                } else {
                    $output->writeln("{$orcidId} (<fg=yellow>{$docId}</>)");
                }
            } else {
                if ($this->tagDocuments && ! in_array($docId, $this->cleanDocuments)) {
                    $this->cleanDocuments[] = $docId;
                }
            }

            if ($progressBar !== null) {
                $progressBar->advance();
            }
        }

        if ($this->tagDocuments) {
            $this->removeTagsFromCleanDocuments();
        }

        if ($progressBar !== null) {
            $progressBar->finish();
            $output->writeln('');
        }
    }

    /**
     * @param DocumentInterface $doc
     */
    public function addTag($doc)
    {
        try {
            $otherTags = $doc->getEnrichmentValue(self::ERROR_ENRICHMENT);
            if (! is_array($otherTags)) {
                $otherTags = [$otherTags];
            }
            $alreadyTagged = $otherTags !== null && in_array(self::ORCID_ERROR_CODE, $otherTags);
        } catch (ModelException $ex) {
            $alreadyTagged = false;
        }

        if (! $alreadyTagged) {
            $tag = Enrichment::new();
            $tag->setKeyName(self::ERROR_ENRICHMENT);
            $tag->setValue(self::ORCID_ERROR_CODE);
            $doc->addEnrichment($tag);
            $doc->setLifecycleListener(null);
            $doc->store();
        }
    }

    /**
     * Removes error tag from document.
     *
     * @param DocumentInterface $doc
     */
    public function removeTag($doc)
    {
        $enrichments = $doc->getEnrichment();

        $remainingEnrichments = [];

        foreach ($enrichments as $enrichment) {
            if ($enrichment->getKeyName() !== self::ERROR_ENRICHMENT || $enrichment->getValue() !== self::ORCID_ERROR_CODE) {
                $remainingEnrichments[] = $enrichment;
            }
        }

        $doc->setEnrichment($remainingEnrichments);
        $doc->setLifecycleListener(null);
        $doc->store();
    }

    protected function removeTagsFromCleanDocuments()
    {
        $documents = array_diff($this->cleanDocuments, $this->taggedDocuments);
        foreach ($documents as $docId) {
            $doc = Document::get($docId);
            $this->removeTag($doc);
        }
    }

    /**
     * @return bool
     */
    public function isTaggingEnabled()
    {
        return $this->tagDocuments;
    }

    /**
     * @param bool $enabled
     * @return $this
     */
    public function setTaggingEnabled($enabled)
    {
        $this->tagDocuments = $enabled;
        return $this;
    }

    /**
     * @param OutputInterface $output
     * @return $this
     */
    public function setOutput($output)
    {
        $this->output = $output;
        return $this;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput()
    {
        if ($this->output === null) {
            $this->output = new NullOutput();
        }

        return $this->output;
    }

    /**
     * @return int[]
     */
    public function getTaggedDocuments()
    {
        return $this->taggedDocuments;
    }

    /**
     * @return int[]
     */
    public function getCleanedDocuments()
    {
        return array_diff($this->cleanDocuments, $this->taggedDocuments);
    }
}
