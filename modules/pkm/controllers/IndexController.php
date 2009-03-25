<?php
/**
 * Index Controller for all actions dealing with encryption and signatures
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
 * @package     Module_Pkm
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Pkm_IndexController extends Zend_Controller_Action
{
	/**
	 * Just to be there. No actions taken.
	 *
	 * @return void
	 *
	 */
    public function indexAction()
    {
    	$this->view->title = $this->view->translate('pkm_modulename');
    	try {
    	    $gpg = new OpusGPG();
    	}
    	catch (Exception $e) {
    		$this->view->excp = $this->view->translate('pkm_module_failure');
    	}
    }

	/**
	 * Lists all keys from the internal keyring
	 *
	 * @return void
	 *
	 */
    public function listkeysAction()
    {
    	$this->view->title = $this->view->translate('pkm_list_keys');
    	
    	$gpg = new OpusGPG();
    	
    	$this->view->masterkey = $gpg->getMasterKey();
    	$this->view->keys = $gpg->getKeys();
    }

	/**
	 * Verifies a signature for a file or a couple of files
	 *
	 * @return void
	 *
	 */
    public function verifyAction()
    {
    	$this->view->title = $this->view->translate('pkm_verify_signatures');
    	
    	$gpg = new OpusGPG();
    	
    	$data = $this->_request->getParams();
    	
    	$this->view->noFileSelected = false;
    	
    	if (true === array_key_exists('docId', $data))
    	{    	
    	    try {
    	        $this->view->verifyResult = $gpg->verifyPublication($data['docId']);
    	    }
    	    catch (Exception $e) {
    	    	$this->view->noFileSelected = true;
    	    }
    	}
    	else
    	{
    		$this->view->noFileSelected = true;
    	}
    }

	/**
	 * Shows a complete key
	 *
	 * @return void
	 *
	 */
    public function showkeyAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();

    	$gpg = new OpusGPG();
    	$data = $this->_request->getParams();

    	if (true === array_key_exists('fingerprint', $data))
    	{
        	// Send plain text response.
            $this->getResponse()->setHeader('Content-Type', 'text/plain; charset=UTF-8', true);
            $this->getResponse()->setBody($gpg->exportPublicKey($data['fingerprint']));
    	}    	
    }
}