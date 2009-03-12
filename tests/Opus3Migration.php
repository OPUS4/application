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
// Configure include path.
set_include_path('.' . PATH_SEPARATOR
            . PATH_SEPARATOR . dirname(__FILE__)
            . PATH_SEPARATOR . dirname(dirname(__FILE__)) . '/library'
            . PATH_SEPARATOR . get_include_path());

// Zend_Loader is'nt available yet. We have to do a require_once
// in order to find the bootstrap class.
require_once 'Application/Bootstrap.php';

/**
 * Bootstraps and runs an import from Opus3
 *
 * @category    Import
 */
class Opus3Migration extends Application_Bootstrap {

    protected $importfile;
    protected $path;
    protected $format = 'mysqldump';

    public function setImportfile($importfile) {
    	$this->importfile = $importfile;
    }
    
    public function setFulltextPath($path) {
    	$this->path = $path;
    }
    
    public function setFormat($format) {
    	$this->format = $format;
    }
    
    /**
     * Starts an Opus console.
     *
     * @return void
     */
    public function _run() {
		$stylesheetPath = '../modules/import/views/scripts/opus3';
		$stylesheet = $this->format;
    	// Set the stylesheet to use for XML-input transformation
    	switch ($stylesheet)
    	{
    		case 'mysqldump':
    			$xslt = 'opus3.xslt';
    			break;
    		case 'phpmyadmin':
                $xslt = 'opus3.phpmyadmin.xslt';
                break;
    		default:
    			$xslt = 'opus3.xslt';
    			break;    		    
    	}
		
		$importData = new DOMDocument;
		//print_r($this->getRequest()->getPost());
		//print_r($files);
		$importData->load($this->importfile);

		$import = new XMLImport($xslt, $stylesheetPath);
		$result = $import->import($importData);
		
		// get the files for all successfully imported entries
		foreach ($result['success'] as $imported)
		{
			$fileList = $this->getFiles($imported['oldid']);
			//print_r($fileList);
			//$doc = new Opus_Document($imported['newid']);
            //if (true === is_array($doc->getLanguage()))
            //{
        	    //$lang = $doc->getLanguage(0);
            //}
            //else
            //{
        	    //$lang = $doc->getLanguage();
            //}
			//foreach ($fileList as $file)
			//{
			    //$file->setLanguage($lang);
			    //$doc->addFile($file);
			//}
			//$doc->store();
		}

    }

	/**
	 * Finds files from the Opus3-Repository and builds them in XML
	 *
	 * @return void
	 *
	 */
	public function getFiles($opusid)
	{
		$fileImport = new Opus3FileImport($this->path);
		$files = $fileImport->loadFiles($opusid); 
		return $files;
	}

}

// Start migration
$import = new Opus3Migration;
$import->setImportfile($argv[1]);
$import->setFulltextPath($argv[2]);
if ($argc === 4) $import->setFormat($argv[3]);
$import->run(dirname(dirname(__FILE__)), Opus_Bootstrap_Base::CONFIG_TEST,
    dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'config');