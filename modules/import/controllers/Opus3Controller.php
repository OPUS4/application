<?php
/**
 * Index controller for Import module
 * 
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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Import_Opus3Controller extends Zend_Controller_Action
{
	/**
	 * Set forms to select an import action to the view
	 *
	 * @return void
	 *
	 */
     public function indexAction()
    {
    	$this->view->title = $this->view->translate('import_modulename');
    }

	/**
	 * Imports metadata from an Opus3-Repository from an XML-Dump
	 *
	 * @return void
	 *
	 */
	public function importAction()
	{
		$stylesheetPath = $this->view->getScriptPath('opus3');
		$stylesheet = $this->getRequest()->getPost('xmlformat');
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
		
		$upload = new Zend_File_Transfer_Adapter_Http();
        $files = $upload->getFileInfo();
		$importData = new DOMDocument;
		print_r($this->getRequest()->getPost());
		print_r($files);
		$importData->load($files['xmldump']['tmp_name']);

		$import = new XMLImport($xslt, $stylesheetPath);
		$result = $import->import($importData);
		
		// get the files for all successfully imported entries
		
		#print_r($result);
		$this->view->numberOfEntries = count($result['success']);
		$this->view->numberOfFailures = count($result['failure']);
		$this->view->importiere = $result['success'];
		$this->view->importfehler = $result['failure'];
	}
	
	/**
	 * Finds files from the Opus3-Repository and builds them in XML
	 *
	 * @return void
	 *
	 */
	public function getFiles()
	{
		$fileImport = new Opus3FileImport('/www/tubdok_test/htdocs/volltexte');
		$fileImport->loadFiles(82); 
		//<File PathName="" SortOrder="" Label="" FileType="" MimeType="" Language="" DocumentId=""/>
	}
}
