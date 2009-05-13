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
 * @package     Module_Admin
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Admin_FilemanagerController extends Zend_Controller_Action
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
        $gpg = new Opus_GPG();
        $data = $this->_request->getPost();

        if (true === array_key_exists('FileObject', $data))
        {
            $e = null;
            try {
                $gpg->signPublicationFile(unserialize(base64_decode($data['FileObject'])), $data['password']);
            }
            catch (Exception $e) {
                $this->view->actionresult = $e->getMessage();
            }
            if ($e === null) {
                $this->view->actionresult = 'Successfully signed file!';
            }
        }

        $requestData = $this->_request->getParams();

    	$this->view->noFileSelected = false;

    	if (true === array_key_exists('docId', $requestData))
    	{
    	    try {
    	        $doc = new Opus_Document($requestData['docId']);
    	    }
    	    catch (Exception $e) {
    	    	$this->view->noFileSelected = true;
    	    }

            $this->view->files = array();
            $this->view->verifyResult = array();

    	    foreach ($doc->getFile() as $file)
    	    {
    	            $index = 0;
    	        	$form = new SignatureForm();
    	        	$form->FileObject->setValue(base64_encode(serialize($file)));
    	        	$form->setAction($this->view->url(array('module' => 'admin', 'controller' => 'filemanager', 'action' => 'index', 'docId' => $requestData['docId']), null, true));

    		        $this->view->files[$index] = $file->getPathName();
    		        $this->view->files[$index] .= $form;

    		        try {
                        $this->view->verifyResult[$file->getPathName()] = $gpg->verifyPublicationFile($file);
                    }
                    catch (Exception $e) {
                        $this->view->verifyResult[$file->getPathName()] = array(array($e->getMessage()));
                    }
                    $index++;
    	        }
    	}
    	else
    	{
    		$this->view->noFileSelected = true;
    	}
    }
}
