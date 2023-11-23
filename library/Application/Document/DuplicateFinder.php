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

use Opus\Common\Config;
use Opus\Common\Document;
use Opus\Common\DocumentInterface;
use Opus\Common\Repository;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Removes duplicate documents identified by DOI.
 *
 * TODO rename function to findDuplicateDocuments
 * TODO make "action" optional (and configurable in the long run)
 * TODO logging
 * TODO dry-run -> need to generate a report (CSV)
 */
class Application_Document_DuplicateFinder
{
    /** @var OutputInterface */
    private $output;

    /** @var ProgressBar */
    private $progressBar;

    /** @var resource */
    private $csvFile;

    /** @var bool */
    private $dryRun;

    /** @var string */
    private $fromDate;

    /** @var string */
    private $untilDate;

    /** @var bool */
    private $remove;

    /**
     * @param string[] $listOfDoi
     */
    public function removeDuplicateDocuments($listOfDoi)
    {
        $progressBar = $this->getProgressBar();
        foreach ($listOfDoi as $doi) {
            $this->removeDuplicateDocument($doi);
            if ($progressBar) {
                $progressBar->advance();
            }
        }
    }

    /**
     * @param string $doi
     */
    public function removeDuplicateDocument($doi)
    {
        $output = $this->getOutput();

        $output->write("Checking $doi ... ", false, OutputInterface::VERBOSITY_VERBOSE);

        $docIds = $this->findDocuments($doi);

        $docCount = count($docIds);

        $output->write("found {$docCount} document(s)", false, OutputInterface::VERBOSITY_VERBOSE);

        if (count($docIds) > 1) {
            if ($output->isVerbose()) {
                $output->write(' - ' . implode(', ', $docIds) . ' ');
            }

            foreach ($docIds as $docId) {
                $doc = Document::get($docId);
                $this->writeCsv($doi, $doc);
            }

            // TODO log if more than 2 documents were found
            $doc         = $this->getNewestDocument($docIds);
            $docId       = $doc->getId();
            $serverState = $doc->getServerState();

            if ($doc->getServerState() === Document::STATE_UNPUBLISHED) {
                if ($output->isVerbose()) {
                    $output->write("- REMOVE document <fg=yellow>{$docId}</>");
                }
                $this->performAction($doc);
            } else {
                $output->write(
                    "- KEEP document <fg=yellow>{$docId}</> in state '{$serverState}'",
                    false,
                    OutputInterface::VERBOSITY_VERBOSE
                );
            }
        } else {
            $this->writeCsv($doi);
        }

        $output->writeln('', OutputInterface::VERBOSITY_VERBOSE);
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
     * @param string                 $doi
     * @param DocumentInterface|null $doc
     */
    protected function writeCsv($doi, $doc = null)
    {
        $csvFile = $this->getCsvFile();

        if ($csvFile !== null) {
            if ($doc !== null) {
                $baseLink    = $this->getBaseLink();
                $docId       = $doc->getId();
                $dateCreated = $doc->getServerDateCreated()->getDateTime();

                $data = [
                    $doi,
                    $docId,
                    "<$baseLink/$docId>",
                    $dateCreated->format('Y-m-d'),
                    $doc->getServerState(),
                ];
            } else {
                $data = [
                    $doi,
                    'NOT_FOUND',
                    '',
                    '',
                    '',
                ];
            }

            fputcsv($csvFile, $data);
        }
    }

    /**
     * @return string|null
     */
    protected function getBaseLink()
    {
        $config = Config::get();

        if (isset($config->url)) {
            $url = $config->url ?? '';
            return rtrim($url, "/ \n\r\t\v\x00");
        }

        return null;
    }

    /**
     * @param DocumentInterface $doc
     *
     * TODO move action into separate classes for different actions (report, mark, delete, ...)
     */
    protected function performAction($doc)
    {
        if (! $this->isDryRunEnabled() && $this->isRemoveEnabled()) {
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
        if ($this->output === null) {
            $this->output = new ConsoleOutput();
        }
        return $this->output;
    }

    /**
     * @param string $fromDate
     * @return $this
     */
    public function setFromDate($fromDate)
    {
        $this->fromDate = $fromDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getFromDate()
    {
        return $this->fromDate;
    }

    /**
     * @param string $untilDate
     * @return $this
     */
    public function setUntilDate($untilDate)
    {
        $this->untilDate = $untilDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getUntilDate()
    {
        return $this->untilDate;
    }

    /**
     * @param bool $enabled
     * @return $this
     */
    public function setRemoveEnabled($enabled)
    {
        $this->remove = $enabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRemoveEnabled()
    {
        return $this->remove;
    }

    /**
     * @param resource $csvFile
     * @return $this
     */
    public function setCsvFile($csvFile)
    {
        $this->csvFile = $csvFile;
        return $this;
    }

    /**
     * @return resource|null
     */
    public function getCsvFile()
    {
        return $this->csvFile;
    }

    /**
     * @param ProgressBar|null $progressBar
     * @return $this
     */
    public function setProgressBar($progressBar)
    {
        $this->progressBar = $progressBar;
        return $this;
    }

    /**
     * @return ProgressBar|null
     */
    public function getProgressBar()
    {
        return $this->progressBar;
    }
}
