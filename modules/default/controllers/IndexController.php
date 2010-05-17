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
 * @package     Module_Default
 * @author      Ralf Claussnitzer (ralf.claussnitzer@slub-dresden.de)
 * @author      Felix Ostrowski (ostrowski@hbz-nrw.de)
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * This controller is called on every initial
 * page request. It currently configures the view for greeting the user and
 * sets up the main menu.
 *
 * @category    Application
 * @package     Module_Default
 */
class IndexController extends Zend_Controller_Action {

	/**
	 * Redirect to default module.
	 * Default module is configurable in config.ini
	 *
	 * @return void
	 *
	 */
	public function indexAction() {
   		$config = Zend_Registry::get('Zend_Config');

		$module = $config->startmodule;
		if (empty($module) === true) {
			$module = 'home';
		}
		
		$this->_helper->getHelper('Redirector')->gotoSimple('index', 'index', $module);
	}
	
	/**
	 * add a PPN to a OPUS Document
	 */
	public function addppnAction() {
		$data = $this->_request->getParams();
		$id = null;
		if (array_key_exists('urn', $data) === true) {
			if ($data['urn'] !== '') {
    			// find record by URN
	    		$ids = Opus_Document::getDocumentByIdentifier($data['urn']);
		    	// get the first ID as result
			    if (count($ids) > 0) {
			        $id = $ids[0];
			    }
			}
		}
		if (array_key_exists('url', $data) === true && $id === null) {
			if ($data['url'] !== '') {
    			// find record by URL
	    		$ids = Opus_Document::getDocumentByIdentifier(str_replace('<>', '/', $data['url']), 'url');
		    	// get the first ID as result
			    if (count($ids) > 0) {
			        $id = $ids[0];
			    }
			}
		}
		if ($id !== null) {
			$doc = new Opus_Document($id);
			$identifier = new Opus_Identifier();
			$identifier->setValue($data['ppn']);
			$doc->addIdentifierOpac($identifier);
			$doc->store();
		}
		
		$config = Zend_Registry::get('Zend_Config');

		$module = $config->startmodule;
		if (empty($module) === true) {
			$module = 'home';
		}
		
		$this->_helper->getHelper('Redirector')->gotoSimple('index', 'index', $module);
		
	}

}
