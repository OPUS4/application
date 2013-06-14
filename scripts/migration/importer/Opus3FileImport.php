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
     * @var string
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
    protected $numExtension = array();


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
     * @param $document-id, $roleid
     * @return integer
     */
    public function loadFiles($id, $roleid = null) {
        $this->tmpPath = null;
        $this->tmpFiles = array();
	$this->filesImported = array();

	$this->tmpDoc = new Opus_Document($id);
        $opus3Id = $this->tmpDoc->getIdentifierOpus3(0)->getValue();

        $this->roleId = $roleid;
        $this->tmpPath = $this->searchDir($this->path, $opus3Id);

        if (is_null($this->tmpPath)) { return 0; }

    	foreach($this->find_all_files($this->tmpPath) as $f) {
	    array_push($this->tmpFiles, $f);
	}

	sort($this->tmpFiles);
	
	foreach ($this->tmpFiles as $f) {
            $this->saveFile($f);
        }

        $this->tmpDoc->store();

        foreach ($this->tmpDoc->getFile() as $f) {
            $this->removeFileFromRole($f, 'guest');
            $this->appendFileToRole($f);
        }

        return count($this->tmpDoc->getFile());
    }

    /*
     * Search for tmpPath for specified Path and Opus3Id
     *
     * @param Directory and OpusId
     * @return string
     */

    private function searchDir($root, $id) {
        $seeds = array('.', 'campus', 'incoming');
    	foreach ($seeds as $s) {
            foreach (scandir($root. "/" . $s) as $year) {
                if (!preg_match('/^[0-9]{4}$/', $year)) { continue; }
                foreach (scandir($root. "/" . $s . "/" . $year) as $i) {
                    if ($i == $id) {
                        $this->logger->log_debug("Opus3FileImport", "Directory for Opus3Id '" . $id . "' : '" . $root . "/" . $s . "/" . $year . "/" . $i  . "'");
                        return $root . "/" . $s . "/" . $year . "/" . $i;
                    }
                }
            }
	}
        return null;
    }

    /*
     * Search all Files in specified directory
     *
     * @param directory
     * @return array
     */
     
    private function find_all_files($dir) {
        $root = scandir($dir);
        foreach($root as $value)  {
            if($value === '.' || $value === '..' || $value === '.svn') {continue;}
            if(is_file("$dir/$value")) {$result[]="$dir/$value";continue;}
            foreach($this->find_all_files("$dir/$value") as $value) {
		$result[]=$value;
            }
        }
        return $result;
    }


    /*
     * Set File-Proprerties and save File to Document
     *
     * @param filename
     * @return boolean
     */

    private function saveFile($f) {
        if (!$this->isValidFile($f)) { return false; }
	$subdir = $this->getSubdir($f);
	$label = null;
	
	$visibleInOai = $this->getVisibilityInOai();
        $visibleInFrontdoor = $this->getVisibilityInFrontdoor($subdir);
        $pathName = $this->getPathName($subdir, basename($f));
        
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
    * Remove Access -Right from a user
    *
    * @param name
    * @return void
    */

    private function removeFileFromRole($file, $name = null)  {
        $role = null;
        if (!is_null($name)) {
            if (Opus_UserRole::fetchByname($name)) {
                $role = Opus_UserRole::fetchByname($name);
                $role->removeAccessFile($file->getId());
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

    private function appendFileToRole($file)  {
        if (!is_null($this->roleId)) {
            $role = new Opus_UserRole($this->roleId);
            $role->appendAccessFile($file->getId());
            $this->logger->log_debug("Opus3FileImport", "Role '" . $role . "' for File '" . $file->getPathName() . "'");
            $role->store();
        }
    }


   /*
    * Get SubDirectory from a full Filename according to the 'global' Fulltext-Directory
    *
    * @param file
    * @return string
    */

    private function getSubdir($f)  {
        if ($this->tmpPath == dirname($f)) { return; }
        return substr(dirname($f), strlen($this->tmpPath) + 1);
    }

    /*
    * Get OAI-Visibility according to the Role
    *
    * @param string
    * @return boolean
    */

    private function getVisibilityInOai()  {
        if (!is_null($this->roleId)) {
            $role = new Opus_UserRole($this->roleId);
            if ($role->getName() == 'guest') {
		return true;
            }
        }
        return false;
    }

    /*
    * Get Frontdoor-Visibility according to the Subdir
    *
    * @param string
    * @return boolean
    */

    private function getVisibilityInFrontdoor($subdir)  {
	if (strpos($subdir , "original") === 0) {
            return false;
	}    
        return true;
    }

    /*
    * Get Pathname according to the Subdir and Basename
    *
    * @param string, string
    * @return string
    */

    private function getPathName($subdir, $basename) {
        if (strlen($subdir) == 0) { return $basename; }

        $name = str_replace('/', '_', $subdir)."_".$basename;
	
	if (strpos($subdir , "original") === 0) {
            return $name;
	}
	
	return substr($name, strpos($name, '_') + 1);
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
    * Returns Label for File from a full Filename according to FileExtension
    *
    * @param file
    * @return string
    */

    private function getLabel($f)  {
        $extension = substr(strrchr ($f, "."), 1);
        if (array_key_exists($extension, $this->numExtension) === false) { $this->numExtension[$extension] = 0; }
        $this->numExtension[$extension]++;
        $label = "Dokument_" . $this->numExtension[$extension] . "." . $extension;
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

