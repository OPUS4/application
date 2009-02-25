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
 * @version     $Id: IndexController.php 1948 2009-02-17 15:17:01Z claussnitzer $
 */

class Import_Opus3Controller extends Zend_Controller_Action
{

    /**
     * Holds xml representation of document information to be processed.
     *
     * @var DomDocument  Defaults to null.
     */
    protected $_xml = null;

    /**
     * Holds the stylesheet for the transformation.
     *
     * @var DomDocument  Defaults to null.
     */
    protected $_xslt = null;

    /**
     * Holds the xslt processor.
     *
     * @var DomDocument  Defaults to null.
     */
    protected $_proc = null;

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
     * Do some initialization on startup of every action
     *
     * @return void
     */
    public function init()
    {
        // Module outputs plain Xml, so rendering and layout are disabled.
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();

        // Initialize member variables.
        $this->_xml = new DomDocument;
        $this->_xslt = new DomDocument;
    }

	/**
	 * Imports metadata from an Opus3-Repository from an XML-Dump
	 *
	 * @return void
	 *
	 */
	public function importAction()
	{
		$stylesheet = $this->getRequest()->getPost('xmlformat');
		$this->createXsltProcessor($stylesheet);
		$upload = new Zend_File_Transfer_Adapter_Http();
        $files = $upload->getFileInfo();
		$this->_xml->load($files['xmldump']['tmp_name']);
		// output transformed XML-Document containing all Documents in Opus 4-XML format
		$documentsXML = new DOMDocument;
		//echo $this->_proc->transformToXml($this->_xml);
		$documentsXML->loadXML($this->_proc->transformToXml($this->_xml));
		$doclist = $documentsXML->getElementsByTagName('Opus_Document');
		foreach ($doclist as $document) 
		{
			echo "Importing " . $documentsXML->saveXML($document) . '<br/>';
			$doc = Opus_Document::fromXml($documentsXML->saveXML($document));
			$doc->store();
		}
	}

	/**
	 * Sets the stylesheet to use for input file transformation
	 *
	 * @return void
	 *
	 */
    private function createXsltProcessor($stylesheet) 
    {
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
    	$this->_xslt->load($this->view->getScriptPath('opus3') . '/' . $xslt);
        $this->_proc = new XSLTProcessor;
        $this->_proc->registerPhpFunctions();
        $this->_proc->importStyleSheet($this->_xslt);
    }
}
