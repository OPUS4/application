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
 * @package     Import
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Opus3SignatureImport {
    /**
     * Holds the path to the fulltexts in Opus3
     *
     * @var string  Defaults to null.
     */
    protected $_path = null;
    
    /**
     * Holds the path to the fulltexts in Opus3 for this certain ID
     *
     * @var string  Defaults to null.
     */
    protected $_tmpPath = null;
    

    /**
     * Do some initialization on startup of every action
     *
     * @param string $fulltextPath Path to the Opus3-fulltexts
     * @return void
     */
    public function __construct($fulltextPath) {
        // Initialize member variables.
        $this->_path = $fulltextPath;
    }
    
    /**
     * Loads an old Opus ID
     *
     * @param Opus_Document $object Opus-Document for that the files should be registered
     * @return void
     */
    public function loadSignatureFiles($id) {
        $object = new Opus_Document($id);
        $this->_tmpPath = null;
        $opusId = $object->getIdentifierOpus3()->getValue();

        // Search the ID-directory in signaturefiles tree
        $this->searchDir($this->_path, $opusId);
        
        foreach ($object->getFile() as $file) {
            $sigfiles = $this->getFiles($this->_tmpPath, $file->getPathName());
            if (count($sigfiles) > 0) {
                $key = 0;
                foreach ($sigfiles as $signatureFile) {
                    $registered = false;
                    $signature = implode("", file($signatureFile));
                    // check if this signature has been registered
                    $hashes = $file->getHashValue();
                    foreach ($hashes as $hash) {
                        if (substr($hash->getType(), 0, 4) === 'gpg-') {
                            $key++;
                            if ($signature === $hash->getValue()) {
                                $registered = true;
                            }
                        }
                    }
                    // if not, add the signature
                    if ($registered === false) {
                        $hash = new Opus_HashValues();
                        $hash->setType('gpg-' . $key);
                        $hash->setValue($signature);

                        $file->addHashValue($hash);
                    }
                    unset($signatureFile);
                }
            }
            unset($file);
        }
        // Store signature(s) directly
        $object->store();
    }
    
    private function getFiles($from, $filename) {
        if (!is_dir($from)) {
            return false; 
        }
     
        $files = array();
     
        if ($dh = opendir($from)) {
            while (false !== ($file = readdir($dh))) {
                // Skip '.' and '..' and '.svn' (that should not exist, but if it does...) and .asc files
                // (they shouldnt be here also)
                if ($file == '.' || $file == '..' || $file === '.svn') {
                    continue; 
                }
                $path = $from . '/' . $file;
                if (is_dir($path)) {
                    $files += $this->getFiles($path, $filename); 
                }
                else {
                    // Ignore files in the main directory, OPUS3 stores in subdirectories only
                    if ($from !== $this->_tmpPath) {
                        if (strstr($path, $filename) !== false && (ereg("\.asc$", $path) !== false)) {
                            $files[] = $path;
                        }
                    }
                }
            }
            closedir($dh);
        }
        return $files;
    }
    
    private function searchDir($from, $search) {
        if (! is_dir($from)) {
            return false; 
        }
     
        if ( $dh = opendir($from)) {
            while (false !== ($file = readdir($dh))) {
                // Skip '.' and '..'
                if ($file == '.' || $file == '..') {
                    continue; 
                }
                $path = $from . '/' . $file;
                if ($file === $search) {
                    $this->_tmpPath = $path;
                    return true;
                }
                else if (is_dir($path)) {
                    $this->searchDir($path, $search);
                }
            }
            closedir($dh);
        }
        return false;
    }
}