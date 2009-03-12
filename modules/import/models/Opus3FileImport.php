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
 * @package     Module_Import
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Opus3FileImport 
{
    /**
     * Holds the path to the fulltexts in Opus3
     *
     * @var string  Defaults to null.
     */
    protected $_path = null;

    /**
     * Do some initialization on startup of every action
     *
     * @param string $fulltextPath Path to the Opus3-fulltexts
     * @return void
     */
    public function __construct($fulltextPath)
    {
        // Initialize member variables.
        $this->_path = $fulltextPath;
    }
    
    /**
     * Loads an old Opus ID
     *
     * @param string $opusId Id of the document in the old Opus-system
     * @return void
     */
    public function loadFiles($opusId)
    {
        // Search the ID-directory in fulltext tree
        $path = $this->searchDir($this->_path, $opusId);
        echo "Found Files for $opusId in $path";
        $files = $this->getFiles($path);
        
        print_r($files);
        
        //$files = array();
        
        $filename = '';
        $finfo = finfo_open(FILEINFO_MIME);
        $mimeType = finfo_file($finfo, $filename);
        
        // if you got it, build a Opus_File-Object
        $file = new Opus_File();
        $file->setDocumentId($opusId);
        $file->setLabel($filename);
        $file->setPathName($filename);
        $file->setMimeType($mimeType);
        $file->setTempFile($filename);
        $files[] = $file;
        // look if there are other files
        // return all files in an array
        return $files;
    }
    
    private function getFiles($from) 
    {
        if(! is_dir($from))
            return false;
     
        $files = array();
     
        if( $dh = opendir($from))
        {
            while( false !== ($file = readdir($dh)))
            {
                // Skip '.' and '..'
                if( $file == '.' || $file == '..')
                    continue;
                $path = $from . '/' . $file;
                if (is_file($file)) {
                	echo "Datei gefunden: " . $path;
                	$files[] = $path;
                }
                else if( is_dir($path) ) {
                	echo "Entering $path";
                    $files .= $this->getFiles($path);
                }
            }
            closedir($dh);
        }
        return $files;
    }
    
    private function searchDir($from, $search)
    {
        if(! is_dir($from))
            return false;
     
        if( $dh = opendir($from))
        {
            while( false !== ($file = readdir($dh)))
            {
                // Skip '.' and '..'
                if( $file == '.' || $file == '..')
                    continue;
                $path = $from . '/' . $file;
                if ($file === $search) {
                	$returnpath = $path;
                	break;
                }
                else if( is_dir($path) ) {
                    $this->searchDir($path, $search);
                }
            }
            closedir($dh);
        }
        return $returnpath;
    }
}