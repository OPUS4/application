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
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2009-2011 OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id: Opus3FileImport.php 5890 2010-09-26 17:13:48Z tklein $
 */

class Opus3FileImport {
   /**
    * Holds Zend-Configurationfile
    *
    * @var file
    */
    protected $config = null;

    /**
     * Holds the path to the fulltexts in Opus3
     *
     * @var string  Defaults to null.
     */
    protected $_path = null;

    /**
     * Holds the specified document
     *
     * @var string  Defaults to null.
     */
    protected $_tmpDoc = null;
    
    /**
     * Holds the path to the fulltexts in Opus3 for this certain ID
     *
     * @var string  Defaults to null.
     */
    protected $_tmpPath = null;

    /**
     * Holds the files to the fulltexts in Opus3
     *
     * @var string  Defaults to null.
     */
    protected $_tmpFiles = array();


    /**
     * Holds the logfile for Importer
     *
     * @var string  Path to logfile
     */
    protected $logfile = null;

    /**
     * Holds the filehandle of the logfile
     *
     * @var file  Fileandle logfile
     */
    protected $_logfile;

    

    /**
     * Do some initialization on startup of every action
     *
     * @param string $fulltextPath Path to the Opus3-fulltexts
     * @return void
     */
    public function __construct($fulltextPath)  {
        $this->config = Zend_Registry::get('Zend_Config');
        $this->logfile = $this->config->import->logfile;
        $this->_path = $fulltextPath;
        try {
            $this->_logfile= @fopen($this->logfile, 'a');
            if (!$this->_logfile) {
                throw new Exception("ERROR Opus3FileImport: Could not create '".$this->logfile."'\n");
            }
        } catch (Exception $e){
            echo $e->getMessage();
        }
    }

    public function log($string) {
        echo $string;
        fputs($this->_logfile, $string);
    }

    public function finalize() {
        fclose($this->_logfile);
    }
    
    /**
     * Loads an old Opus ID
     *
     * @param Opus_Document $object Opus-Document for that the files should be registered
     * @return void
     */
    public function loadFiles($id) {
        $this->_tmpDoc = new Opus_Document($id);
        $opus3Id = $this->_tmpDoc->getIdentifierOpus3(0)->getValue();
   	
    	$this->searchDir($this->_path, $opus3Id);

        $this->_tmpFiles = array();
        $this->getFiles($this->_tmpPath);

        $number = $this->saveFiles();
        return $number;
    }

    /*
     * Initialize tmpPath for Opus3Id
     *
     * @param Directory and OpusId
     * @return void
     */

    private function searchDir($from, $search) {

        //echo "Search in ".$from." for id ".$search. "\n";

        if(!is_dir($from)) {
            return;
        }

        $handle = opendir($from);
        while ($file = readdir($handle)) {
            // Skip '.' , '..' and '.svn'
            if( $file == '.' || $file == '..' || $file === '.svn') {
                continue;
            }

            $path = $from . '/' . $file;

            // Workaround for Opus3-Id === year
            if ( is_dir($path) && $from ===  $this->_path) {
                $this->searchDir($path, $search);
            }

            // If correct directory found: take it
            else if ( is_dir($path) && $file === $search) {
                $this->_tmpPath = $path;
                $this->log("DEBUG OPus3FileImport: Directory for Opus3Id '".$search."' : ".$path."\n");
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
     * Get Files in specified path
     *
     * @param Directory
     * @return void
     */

    private function getFiles($from)  {
        if(! is_dir($from)) { 
            return;
        }

        $handle = opendir($from);
        while ($file = readdir($handle)) {
            // Skip '.' , '..' and '.svn'
            if( $file == '.' || $file == '..' || $file === '.svn') {
                continue;
            }
            
            $path = $from . '/' . $file;

            // If directory: call function recursively
            if (is_dir($path))  {
                $this->getFiles($path);
            }

            // If file: take it
            else  {   
                array_push($this->_tmpFiles, $path);
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

        if (count($this->_tmpFiles) === 0) {
           return 0;
        }

        $lang = $this->_tmpDoc->getLanguage();
        $total = 0;

        $numSuffix = array();
        $filesImported = array();

        foreach ($this->_tmpFiles as $f) {
                        
            // Exclude 'index.html' and files starting with '.'
            if (basename($f) == 'index.html' || strpos(basename($f), '.') === 0) {
                $this->log("DEBUG Opus3FileImport: Skipped File '".basename($f)."'\n");
                continue;
            }

            // ERROR: File with same Basnemae already imported
            if (array_search(basename($f), $filesImported) !== false) {
                $this->log("ERROR Opus3FileImport: File ".basename($f)." already imported.\n");
                continue;

            }

            // ERROR: Filename has no Extension
            if (strrchr ($f, ".") === false) {
                $this->log("ERROR Opus3FileImport: File ".basename($f)." has no extension and will be ignored.\n");
                continue;
            }

            $suffix = substr (strrchr ($f, "."), 1);
            if (array_key_exists($suffix, $numSuffix) === false) { $numSuffix[$suffix] = 0; }
            $numSuffix[$suffix]++;
            $label = "Dokument_" . $numSuffix[$suffix] . "." . $suffix;

            array_push($filesImported, basename($f));
            $this->log("DEBUG Opus3FileImport: File ".basename($f)." imported.\n");

            $file = $this->_tmpDoc->addFile();
            $file->setPathName(basename($f));
            $file->setLabel($label);
            $file->setTempFile($f);
            $file->setLanguage($lang);

            // Check if a '.bem_' file exists and make a filecomment
            $comment_file = dirname($f) . "/.bem_" . basename($f);
            if (file_exists($comment_file)) {
                $fileArray = file($comment_file);
                $comment = utf8_encode(implode(' ', $fileArray));
                $file->setComment($comment);
            }

            $total++;
        }

        // store the object
        if ($total > 0) {
            // TODO: Get collections before import files (must be fixed in framework)
            //$this->_tmpDoc->getCollection();
            $this->_tmpDoc->store();
        }

        return $total;
    }
}

