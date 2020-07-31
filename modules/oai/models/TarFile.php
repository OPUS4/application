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
 * @package     Module_Oai
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Oai_Model_TarFile extends Oai_Model_AbstractFile
{

    public function __construct($docId, $filesToInclude, $filesPath, $tempPath, $logger = null)
    {
        $this->setLogger($logger);
        $numberOfFiles = count($filesToInclude);
        if ($numberOfFiles < 2) {
            $this->logErrorMessage("unexpected number of files to process: $numberOfFiles");
            throw new Oai_Model_Exception('unexpected number of files to include: at least two files were expected');
        }
        $this->_path = $this->getTar($filesToInclude, $docId, $filesPath, $tempPath);
        $this->_mimeType = 'application/x-tar';
        $this->_extension = '.tar';
    }

    private function getTar($filesToInclude, $docId, $filesPath, $tempPath)
    {
        $tarball = $tempPath . uniqid($docId, true) . '.tar';
        $phar = null;
        try {
            $phar = new PharData($tarball);
        } catch (UnexpectedValueException $e) {
            $this->logErrorMessage(
                'could not create tarball archive file ' . $tarball . ' due to insufficient file system permissions: '
                . $e->getMessage()
            );
                throw new Oai_Model_Exception('error while creating tarball container: could not open tarball');
        }

        foreach ($filesToInclude as $file) {
            $filePath = $filesPath . $docId . DIRECTORY_SEPARATOR;
            try {
                $phar->addFile($filePath . $file->getPathName(), $file->getPathName());
            } catch (Exception $e) {
                $this->logErrorMessage(
                    'could not add ' . $file->getPathName() . ' to tarball archive file: ' . $e->getMessage()
                );
                throw new Oai_Model_Exception('error while creating tarball container: could not add file to tarball');
            }
        }

        return $tarball;
    }
}
