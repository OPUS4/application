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
 * @copyright   Copyright (c) 2023, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Document;
use Opus\Common\DocumentInterface;
use Opus\Common\Repository;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Removes duplicate documents identified by DOI.
 *
 * TODO logging
 * TODO dry-run -> need to generate a report
 */
class Application_Document_DuplicateFinder
{
    /** @var OutputInterface */
    private $output;

    /** @var bool */
    private $dryRun;

    /** @var bool */
    private $verbose;

    /** @var string */
    private $fromDate;

    /** @var string */
    private $untilDate;

    /**
     * @param string[] $listOfDoi
     */
    public function removeDuplicateDocuments($listOfDoi)
    {
        foreach ($listOfDoi as $doi) {
            $this->removeDuplicateDocument($doi);
        }
    }

    /**
     * @param string $doi
     */
    public function removeDuplicateDocument($doi)
    {
        $output  = $this->getOutput();
        $verbose = $this->isVerboseEnabled();

        if ($verbose) {
            $output->write("Checking $doi ... ");
        }

        $docIds = $this->findDocuments($doi);

        $docCount = count($docIds);

        if ($verbose) {
            $output->write("found {$docCount}");
        }

        if (count($docIds) > 1) {
            if ($verbose) {
                $output->write(' - ' . implode(', ', $docIds));
            }

            // TODO log if more than 2 documents were found
            $doc = $this->getNewestDocument($docIds);

            if ($doc->getServerState() === Document::STATE_UNPUBLISHED) {
                if ($verbose) {
                    $output->write("REMOVE {$doc->getId}");
                }
                $this->performAction($doc);
            } else {
                if ($verbose) {
                    $output->write('');
                }
            }
        }

        if ($verbose) {
            $output->writeln('');
        }
    }

    /**
     * @param string $doi
     * @return int[]
     */
    public function findDocuments($doi)
    {
        $finder = Repository::getInstance()->getDocumentFinder();

        $finder->setIdentifierValue('doi', $doi);

        return $finder->getIds();
    }

    /**
     * @param int[] $docIds
     * @return DocumentInterface
     */
    public function getNewestDocument($docIds)
    {
        $doc = null;

        foreach ($docIds as $docId) {
            $nextDoc = Document::get($docId);
            if ($doc === null) {
                $doc = $nextDoc;
            } else {
                switch ($doc->getServerDateCreated()->compare($nextDoc->getServerDateCreated())) {
                    case -1:
                        $doc = $nextDoc;
                        break;

                    case 0:
                        // if ServerDateCreated is the same keep the document with the higher database ID
                        if ($doc->getId() < $nextDoc->getId()) {
                            $doc = $nextDoc;
                        }
                        break;

                    default:
                        break;
                }
            }
        }

        return $doc;
    }

    /**
     * @param DocumentInterface $doc
     *
     * TODO move action into separate classes for different actions (report, mark, delete, ...)
     */
    protected function performAction($doc)
    {
        if (! $this->isDryRunEnabled()) {
            $doc->delete();
        }
    }

    /**
     * @param bool $enabled
     */
    public function setDryRunEnabled($enabled)
    {
        $this->dryRun = $enabled;
    }

    /**
     * @return bool
     */
    public function isDryRunEnabled()
    {
        return $this->dryRun;
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param bool $enabled
     */
    public function setVerboseEnabled($enabled)
    {
        $this->verbose = $enabled;
    }

    /**
     * @return bool
     */
    public function isVerboseEnabled()
    {
        return $this->verbose;
    }
}
