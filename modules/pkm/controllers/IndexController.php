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
     * Do some initialization on startup of every action
     *
     * @return void
     */
    public function init()
    {
        $this->_redirector = $this->_helper->getHelper('Redirector');
    }

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
    	
        $uploadForm = new KeyUploadForm();
        $action_url = $this->view->url(array("controller" => "index", "action" => "addkey"));
        $uploadForm->setAction($action_url);
        $this->view->form = $uploadForm;
    	
    	$gpg = new OpusGPG();
    	
    	$this->view->masterkey = $gpg->getMasterKey();
    	$this->view->keys = $gpg->getKeys();
    }

	/**
	 * Adds a key to the internal keyring
	 *
	 * @return void
	 *
	 */
    public function addkeyAction()
    {
    	$gpg = new OpusGPG();
    	
        $upload = new Zend_File_Transfer_Adapter_Http();
        $files = $upload->getFileInfo();

        // save the file
        foreach ($files as $file) {
            $gpg->importKeyFile($file['tmp_name']);
        }
    	
    	$this->_redirector->gotoSimple('listkeys');
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

    	$data = $this->_request->getParams();
    	
    	$gpg = new OpusGPG();
    	
    	if (true === array_key_exists('docId', $data))
    	{
        	try {
        	    $doc = new Opus_Document($data['docId']);
    	    }
        	catch (Exception $e)
        	{
    	    	$this->view->noTitleSelected = true;
    	    }
    	
    	    $this->view->verifyResult = array();
    	
    	    foreach ($doc->getFile() as $file) 
    	    {
    		    try {
    		        $this->view->verifyResult[$file->getPathName()] = $gpg->verifyPublicationFile($file);
    		    }
    		    catch (Exception $e) {
    		    	$this->view->verifyResult[$file->getPathName()] = array(array($e->getMessage()));
    		    }
    	    }
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
    	$gpg = new OpusGPG();
    	$data = $this->_request->getParams();

    	if (true === array_key_exists('fingerprint', $data))
    	{
        	try {
        	    $this->_helper->viewRenderer->setNoRender(true);
                $this->_helper->layout()->disableLayout();

            	// Send plain text response.
                $this->getResponse()->setHeader('Content-Type', 'text/plain; charset=UTF-8', true);
                $this->getResponse()->setBody($gpg->exportPublicKey($data['fingerprint']));
        	}
        	catch (Exception $e) {
        		$this->getResponse()->setBody($e->getMessage());
        	}
    	}    	
    }

	/**
	 * Signs a key with the internal key
	 * not supported by Crypt_GPG 1.0.0
	 * so not yet implemented
	 *
	 * @return void
	 *
	 */
    public function signkeyAction()
    {
    }

	/**
	 * Removes a key from keyring
	 *
	 * @return void
	 *
	 */
    public function deletekeyAction()
    {
    	$gpg = new OpusGPG();
    	$data = $this->_request->getParams();

    	if (true === array_key_exists('fingerprint', $data))
    	{
        	try {
                $gpg->deleteKey($data['fingerprint']);
        	}
        	catch (Exception $e) {
        		$this->view->actionresult = $e->getMessage();
        	}
    	}

    	$this->_redirector->gotoSimple('listkeys');
    }

	/**
	 * Disables an internal key
	 *
	 * @return void
	 *
	 */
    public function disablekeyAction()
    {
    	$gpg = new OpusGPG();
    	$data = $this->_request->getParams();

    	if (true === array_key_exists('fingerprint', $data))
    	{
        	try {
                $gpg->disableKey($data['fingerprint']);
        	}
        	catch (Exception $e) {
        		$this->view->actionresult = $e->getMessage();
        	}
    	}

    	$this->_redirector->gotoSimple('listkeys');
    }

	/**
	 * Lists all files of a publication
	 *
	 * @return void
	 *
	 */
    public function listfilesAction()
    {
    	$data = $this->_request->getParams();
    	
    	$this->view->noFileSelected = false;
    	
    	if (true === array_key_exists('docId', $data))
    	{    	
    	    try {
    	        $doc = new Opus_Document($data['docId']);
    	    }
    	    catch (Exception $e) {
    	    	$this->view->noFileSelected = true;
    	    }
    	
    	        foreach ($doc->getFile() as $file) 
    	        {
    	        	$form = new SignatureForm();
    	        	$form->FileObject->setValue(base64_encode(serialize($file)));
    	        	$form->setAction($this->view->url(array("controller" => "index", "action" => "signfile")));
    		        
    		        $this->view->files .= $file->getPathName();
    		        $this->view->files .= $form;
    	        }
    	}
    	else
    	{
    		$this->view->noFileSelected = true;
    	}
    }

	/**
	 * Signs a file of a publication
	 *
	 * @return void
	 *
	 */
    public function signfileAction()
    {
    	$gpg = new OpusGPG();
    	$data = $this->_request->getPost();

    	if (true === array_key_exists('FileObject', $data))
    	{
        	try {
                $gpg->signPublicationFile(unserialize(base64_decode($data['FileObject'])), $data['password']);
        	}
        	catch (Exception $e) {
        		$this->view->actionresult = $e->getMessage();
        	}
    	}

    	//$this->_redirector->gotoSimple('verify');
    }
}