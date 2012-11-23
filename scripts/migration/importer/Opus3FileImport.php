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
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2009-2011 OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

require_once 'Opus3ImportLogger.php';

class Opus3FileImport {
   /**
    * Holds Zend-Configurationfile
    *
    * @var file
    */
    protected $config = null;

   /**
    * Holds Logger
    *
    * @var file
    */
    protected $logger = null;

    /**
     * Holds the path to the fulltexts in Opus3
     *
     * @var string  Defaults to null.
     */
    protected $path = null;

    /**
     * Holds the specified document
     *
     * @var string  Defaults to null.
     */
    protected $tmpDoc = null;

    /**
     * Holds the roleId for this document
     *
     * @var int Defaults to null.
     */
    protected $roleId = null;
    
    /**
     * Holds the path to the fulltexts in Opus3 for this certain ID
     *
     * @var string  Defaults to null.
     */
    protected $tmpPath = null;

    /**
     * Holds the files to the fulltexts in Opus3
     *
     * @var array
     */
    protected $tmpFiles = array();


    /**
     * Counts Suffixes per Document
     *
     * @var array
     */
    protected $numSuffix = array();


    /**
     * Holds the imported files per Document
     *
     * @var array
     */
    protected $filesImported = array();

    /**
     * Do some initialization on startup of every action
     *
     * @param string $fulltextPath Path to the Opus3-fulltexts
     * @return void
     */
    public function __construct($fulltextPath)  {
        $this->config = Zend_Registry::get('Zend_Config');
        $this->logger = new Opus3ImportLogger();
        $this->path = $fulltextPath;
    }

    public function finalize() {
        $this->logger->finalize();
    }    

    /**
     * Loads an old Opus ID
     *
     * @param Opus_Document $object Opus-Document for that the files should be registered
     * @return void
     */
    public function loadFiles($id, $roleid = null) {
        $this->tmpPath = null;
        $this->tmpFiles = array();

        $this->tmpDoc = new Opus_Document($id);
        $opus3Id = $this->tmpDoc->getIdentifierOpus3(0)->getValue();

        $this->roleId = $roleid;

        // Sets $this->tmpPath
        $this->searchDir($this->path, $opus3Id);

        if (!is_null($this->tmpPath)) {
            // Sets $this->tmpFiles
            $this->searchFiles($this->tmpPath);

            /* Sort Files alphanumerical */
            sort($this->tmpFiles);

            $number = $this->saveFiles();
            $this->removeFilesFromRole('guest');
            $this->appendFilesToRole();

            return $number;
        }

        return;
    }

    /*
     * Search for tmpPath for specified Path and Opus3Id
     *
     * @param Directory and OpusId
     * @return void
     */

    private function searchDir($from, $search) {

        //echo "Search in ".$from." for id ".$search. "\n";

        if(!is_dir($from)) {
            return null;
        }

        $handle = opendir($from);
        while ($file = readdir($handle)) {
            // Skip '.' , '..' and '.svn' 
            if( $file == '.' || $file == '..' || $file === '.svn') {
                continue;
            }

            $path = $from . '/' . $file;

            // Workaround for Opus3-Id === year
            if ( is_dir($path) && $from ===  $this->path) {
                $this->searchDir($path, $search);
            }

            // If correct directory found: take it
            else if ( is_dir($path) && $file === $search) {
                $this->tmpPath = $path;
                $this->logger->log_debug("Opus3FileImport", "Directory for Opus3Id '" . $search . "' : '" . $path . "'");
            }
            
            // call function recursively
            else if( is_dir($path) ) {  
                $this->searchDir($path, $search);
            }
        }
        closedir($handle);
  
        return;
    }

    /*
     * Saerch for Files in specified path
     *
     * @param Directory
     * @return void
     */

    private function searchFiles($from)  {
        if(! is_dir($from)) { 
            return;
        }

        $handle = opendir($from);
        while ($file = readdir($handle)) {
            // Skip '.' , '..' and '.svn' and 'html'
            if( $file == '.' || $file == '..' || $file === '.svn') {
                continue;
            }
            
            $path = $from . '/' . $file;

            // If directory: call function recursively
            if (is_dir($path))  {
                $this->searchFiles($path);
            }

            // If file: take it
            else  {
                array_push($this->tmpFiles, $path);
            }
            
        }
        closedir($handle);

        return;
   }
   
   /*
    * Save Files to Opus-Document and return number of saved files
    * 
    * @param void 
    * @return int 
    */

    private function saveFiles()  {

        if (count($this->tmpFiles) === 0) {
           return 0;
        }

        $lang = $this->tmpDoc->getLanguage();
        $total = 0;

        $this->numSuffix = array();
        $this->filesImported = array();

        foreach ($this->tmpFiles as $f) {
            if ($this->saveFile($f)) { $total++; }
        }

        if ($total > 0) {
            $this->tmpDoc->store();
        }

        return $total;
    }


    private function saveFile($f) {
        if (!$this->isValidFile($f)) { return false; }

        $prefix = $this->getPrefix($f);
        $label = null;

        $visibleInOai = $this->getVisibilityInOai($prefix);
        $visibleInFrontdoor = $this->getVisibilityInFrontdoor($prefix);
        $pathName = $this->getPathName($prefix, basename($f));
        
        if ($pathName != iconv("UTF-8", "UTF-8//IGNORE", $pathName)) {
            $this->logger->log_error("Opus3FileImport", "Filename '" . $pathName . "' is corrupt. Changed to '" . utf8_encode($pathName) . "'.");
            $pathName = utf8_encode($pathName);
        }

        $this->logger->log_debug("Opus3FileImport", "Import '" . $pathName . "'");
        if ($visibleInFrontdoor) {
            $this->logger->log_debug("Opus3FileImport", "File '" . $pathName . "' visible");
            $label = $this->getLabel($f);
        }
        $comment = $this->getComment($f);

        $file = $this->tmpDoc->addFile();
        $lang = $this->tmpDoc->getLanguage();
        $file->setPathName($pathName);
        $file->setTempFile($f);
        $file->setLanguage($lang);
        $file->setVisibleInFrontdoor($visibleInFrontdoor);
        $file->setVisibleInOai($visibleInOai);

        if (!is_null($label)) { $file->setLabel($label); }
        if (!is_null($comment)) { $file->setComment($comment); }

        array_push($this->filesImported, $pathName);
        return true;
    }

    /*
    * Remove Access -Right from a user     *
    * @param name
    * @return void
    */

    private function removeFilesFromRole($name = null)  {
        $role = null;
        if (!is_null($name)) {
            if (Opus_UserRole::fetchByname($name)) {
                $role = Opus_UserRole::fetchByname($name);
                foreach ($this->tmpDoc->getFile() as $f) {
                    $role->removeAccessFile($f->getId());
                }
                $role->store();
            }
        }
   }

   /*
    * Append Files to existing Role
    *
    * @param roleid
    * @return void
    */

    private function appendFilesToRole()  {
        // Check if file have limited access
        if (!is_null($this->roleId)) {
            $role = new Opus_UserRole($this->roleId);
            foreach ($this->tmpDoc->getFile() as $f) {
                $role->appendAccessFile($f->getId());
                $this->logger->log_debug("Opus3FileImport", "Role '" . $role . "' for File '" . $f->getPathName() . "'");
            }
            $role->store();
        }
    }


   /*
    * Get Prefix from a full Filename according to the Fulltext-Directory
    *
    * @param file
    * @return string
    */

    private function getPrefix($f)  {
        if ($this->tmpPath == dirname($f)) { return; }
        return substr(dirname($f), strlen($this->tmpPath) + 1);
    }

    /*
    * Get OAI-Visibility according to the Prefix and Role
    *
    * @param string
    * @return boolean
    */

    private function getVisibilityInOai($s)  {
        if (!is_null($this->roleId)) {
            $role = new Opus_UserRole($this->roleId);
            if ($role->getName() == 'guest') {
                if (is_int(strpos($s , "pdf")) &&  strpos($s , "pdf") == 0) { return true; }
                if (is_int(strpos($s , "ps")) &&  strpos($s , "ps") == 0) { return true; }
            }
        }
        return false;
    }

    /*
    * Get Frontdoor-Visibility according to the Prefix 
    *
    * @param string
    * @return boolean
    */

    private function getVisibilityInFrontdoor($s)  {
        if (is_int(strpos($s , "pdf")) &&  strpos($s , "pdf") == 0) { return true; }
        if (is_int(strpos($s , "ps")) &&  strpos($s , "ps") == 0) { return true; }
        return false;
    }

    /*
    * Get Pathname according to the Prefix and Basename
    *
    * @param string, string
    * @return string
    */

    private function getPathName($prefix, $basename) {
        if (strlen($prefix) == 0) { return $basename; }
        if ($prefix == "pdf" || $prefix == "ps") {
            return $basename;
        }

        $prefix = str_replace('/', '_', $prefix);
        if (is_int(strpos($prefix , "pdf")) &&  strpos($prefix , "pdf") == 0) {
            $prefix = str_replace('pdf_', '', $prefix);
        }
        elseif (is_int(strpos($prefix , "ps")) &&  strpos($prefix , "ps") == 0){
            $prefix = str_replace('ps_', '', $prefix);
        }
        
        return $prefix."_".$basename;
    }

    /*
    * Checks if File is valid to import
    *
    * @param string
    * @return boolean
    */

    private function isValidFile($f)  {
        // Exclude 'index.html' and files starting with '.'
        if (basename($f) == 'index.html' || strpos(basename($f), '.') === 0) {
            $this->logger->log_debug("Opus3FileImport", "Skipped File '" . basename($f) . "'");
            return false;
        }

        // ERROR: File with same Basnemae already imported
        if (array_search(basename($f), $this->filesImported) !== false) {
            $this->logger->log_error("Opus3FileImport", "File '" . basename($f) . "' already imported");
            return false;

        }

        // ERROR: Filename has no Extension
        if (strrchr ($f, ".") === false) {
            $this->logger->log_error("Opus3FileImport", "File '" . basename($f) . "' has no extension and will be ignored");
            return false;
        }

        return true;
   }


   /*
    * Returns Label for File from a full Filename according to the Suffix
    *
    * @param file
    * @return string
    */

    private function getLabel($f)  {
        $suffix = substr(strrchr ($f, "."), 1);
        if (array_key_exists($suffix, $this->numSuffix) === false) { $this->numSuffix[$suffix] = 0; }
        $this->numSuffix[$suffix]++;
        $label = "Dokument_" . $this->numSuffix[$suffix] . "." . $suffix;

        return $label;
    }

    /*
    * Returns Comment for File if a '.bem_' file exists
    *
    * @param file
    * @return string
    */

    private function getComment($f)  {
        $comment_file = dirname($f) . "/.bem_" . basename($f);
        if (file_exists($comment_file)) {
            $fileArray = file($comment_file);
            return utf8_encode(implode(' ', $fileArray));
        }
        return null;
    }
}

