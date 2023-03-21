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
 * @copyright   Copyright (c) 2021, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Config;
use Opus\Common\Document;
use Opus\Common\Model\NotFoundException;

/**
 * Class for checking workspace files.
 *
 * This class checks if all the items in workspace/files directory are a directory and throws an exception for the items
 * which are not.
 */
class Application_Job_CheckWorkspaceFilesJob implements Application_Job_JobInterface
{
    /** @var int */
    private $startTime;

    /** @var int */
    private $errors = 0;

    /** @var string */
    private $file;

    /** @var string */
    private $filesPath;

    /**
     * @return int
     */
    public function run()
    {
        $filesPath = $this->getFilesPath();

        echo "INFO: Scanning directory '$filesPath'...\n";

        // Iterate over all files
        $count        = 0;
        $this->errors = 0;

        foreach (glob($filesPath . DIRECTORY_SEPARATOR . "*") as $file) {
            $this->file = $file;
            if ($count > 0 && $count % 100 === 0) {
                echo "INFO: checked $count entries with " . round($count / (microtime(true) - $this->startTime)) . " entries/seconds.\n";
            }
            $count++;

            $matches = [];
            if (preg_match('/\/([0-9]+)$/', $file, $matches) !== 1) {
                continue;
            }

            if (! is_dir($file)) {
                echo "ERROR: expected directory: $file\n";
                $this->errors++;
                continue;
            }

            $id = $matches[1];
            $d  = $this->checkDocument($id);
        }

        echo "INFO: Checked a total of $count entries with " . round($count / (microtime(true) - $this->startTime)) . " entries/seconds.\n";

        if ($this->errors !== 0) {
            throw new Exception("Found $this->errors ERRORs in workspace files directory '$filesPath'!\n");
        }

        return $count;
    }

    /**
     * Check if document with specified id exists and can be fetched.
     *
     * @param int $id
     * @return Document
     */
    private function checkDocument($id)
    {
        try {
            return Document::get($id);
        } catch (NotFoundException $e) {
            echo "ERROR: No document $id found for workspace path '$this->file'!\n";
            $this->errors++;
        }
    }

    /**
     * Get files directory.
     *
     * @return string filesPath
     */
    private function getFilesPath()
    {
        $this->startTime = microtime(true);

        if ($this->filesPath === null) {
            $config          = Config::get();
            $this->filesPath = realpath($config->workspacePath . DIRECTORY_SEPARATOR . "files");

            if ($this->filesPath === false || empty($this->filesPath)) {
                throw new Exception("Failed scanning workspace files path.\n");
            }
        }
        return $this->filesPath;
    }

    /**
     * @param string $path
     */
    public function setFilesPath($path)
    {
        $this->filesPath = $path;
    }
}
