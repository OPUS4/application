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
 * @package     Module_Admin
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Admin_Model_FileImport {

    private $__importFolder = '../workspace/incoming';

    /**
     * Lists files in import folder.
     */
    public function listFiles() {
        return Controller_Helper_Files::listFiles($this->__importFolder);
    }

    /**
     *
     * @param string $docId
     * @param array $files
     * @throws Application_Exception in case database contains no document with id $docID
     */
    public function addFilesToDocument($docId, $files) {
        $document = null;
        try {
            $document = new Opus_Document($docId);
        }
        catch (Opus_Model_NotFoundException $e) {
            throw new Application_Exception('no document found for id ' . $docId, null, $e);
        }
        
        $log = Zend_Registry::get('Zend_Log');
        $validFilenames = $this->getNamesOfIncomingFiles();
        
        foreach ($files as $file) {
            $log->debug('check filename ' . $file);
            if (array_key_exists($file, $validFilenames)) {
                $pathname = $this->__importFolder . DIRECTORY_SEPARATOR . $validFilenames[$file];
                $log->info('import file ' . $pathname);
                
                $docfile = $document->addFile();
                $docfile->setTempFile($pathname);
                $docfile->setPathName($validFilenames[$file]);
                $docfile->setLabel($validFilenames[$file]);
                try {
                    $document->store();
                    $log->info('import of file ' . $pathname . ' successful');
                }
                catch (Exception $e) {
                    $log->err('import of file ' . $pathname . ' failed: ' . $e->getMessage());
                }
            }
        }
    }

    private function getNamesOfIncomingFiles() {
        $incomingFilenames = array();
        foreach ($this->listFiles() as $file) {
            $filename = $file['name'];
            $incomingFilenames[$filename] = $filename;
        }
        return $incomingFilenames;
    }
}
?>