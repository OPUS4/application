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
        $this->view->title = $this->view->translate('admin_filemanager_index');

        $data = $this->_request->getPost();

        $gpg = new Opus_GPG();

        if (true === array_key_exists('signsubmit', $data))
        {
            $e = null;
            try {
                $gpg->signPublicationFile(new Opus_File($data['FileObject']), $data['password']);
            }
            catch (Exception $e) {
                $this->view->actionresult = $e->getMessage();
            }
            if ($e === null) {
                $this->view->actionresult = $this->view->translate('admin_filemanager_signsuccess');
            }
        }

        if (true === array_key_exists('deletesubmit', $data))
        {
            $e = null;
            try {
                $file = new Opus_File($data['FileObject']);
                // Really delete this file
                $file->doDelete($file->delete());
            }
            catch (Exception $e) {
                $this->view->actionresult = $e->getMessage();
            }
            if ($e === null) {
                $this->view->actionresult = $this->view->translate('admin_filemanager_deletesuccess');
            }
        }

        $requestData = $this->_request->getParams();
        $this->view->docId = $requestData['docId'];
    	$this->view->noFileSelected = false;

    	if (true === array_key_exists('docId', $requestData))
    	{
            $uploadForm = new FileUpload();
            $action_url = $this->view->url(array('controller' => 'filemanager', 'action' => 'index'));
            $uploadForm->setAction($action_url);
            // store uploaded data in application temp dir
            if (true === array_key_exists('uploadsubmit', $data)) {
                if ($uploadForm->isValid($data) === true) {
                    // This works only from Zend 1.7 on
                    // $upload = $uploadForm->getTransferAdapter();
                    $upload = new Zend_File_Transfer_Adapter_Http();
                    $files = $upload->getFileInfo();
                    // TODO: Validate document id, error message on fail
                    $documentId = $requestData['docId'];
                    $document = new Opus_Document($documentId);

                    // save each file
                    foreach ($files as $file) {
                        /* TODO: Uncaught exception 'Zend_File_Transfer_Exception' with message '"fileupload" not found by file transfer adapter
                        * if (!$upload->isValid($file)) {
                        *    $this->view->message = 'Upload failed: Not a valid file!';
                        *    break;
                        * }
                        */
                        $docfile = $document->addFile();
                        $docfile->setDocumentId($document->getId());
                        $docfile->setLabel($uploadForm->getValue('comment'));
                        $docfile->setLanguage($uploadForm->getValue('language'));
                        $docfile->setPathName($file['name']);
                        $docfile->setMimeType($file['type']);
                        $docfile->setTempFile($file['tmp_name']);
                        $docfile->setFromPost($file);
                    }
                    $e = null;
                    try {
                        $document->store();
                        $config = Zend_Registry::get('Zend_Config');

                        $searchEngine = $config->searchengine->engine;
                        if (empty($searchEngine) === true) {
                            $searchEngine = 'Lucene';
                        }
                        // Reindex
                        $engineclass = 'Opus_Search_Index_'.$searchEngine.'_Indexer';
                        $indexer = new $engineclass();
                        $indexer->removeDocumentFromEntryIndex($document);
                        $indexer->addDocumentToEntryIndex($document);
                    }
                    catch (Exception $e) {
                        $this->view->actionresult = $this->view->translate('admin_filemanager_uploadfailure');
                    }
                    if ($e === null) {
                        $this->view->actionresult = $this->view->translate('admin_filemanager_uploadsuccess');
                    }

                    // reset input values fo re-displaying
                    $uploadForm->reset();
                    // re-insert document id
                    $uploadForm->DocumentId->setValue($document->getId());
                }
                else {
                    // invalid form, populate with transmitted data
                    $uploadForm->populate($data);
                    $this->view->form = $uploadForm;
                }

            }
            $this->view->uploadform = $uploadForm;
            $document = new Opus_Document($requestData['docId']);
            $this->view->files = array();

            //searching for files, getting filenumbers and hashes
            $fileNumber = 0;
            $files = $document->getFile();
            if (true === is_array($files) && count($files) > 0) {
                $fileNumber = count($files);
                $this->view->fileNumber = $fileNumber;
            }
            // Iteration over all files, hashtypes and -values
            // Check if GPG for admin is enabled
            $config = Zend_Registry::get('Zend_Config');

            $use_gpg = $config->gpg->enable->admin;
            if (empty($use_gpg) === true) {
                $use_gpg = 0;
		    }
                
            // Initialize masterkey
            $masterkey = false;
                
            if ($use_gpg === '1') {                
                $gpg = new Opus_GPG();
                if ($gpg->getMasterkey() !== false) {
                    try {
                        $masterkey = $gpg->getMasterkey()->getPrimaryKey()->getFingerprint();
                    }
                    catch (Exception $e) {
                        // do nothing, masterkey is already set to false
                    }
                }
            }
            $this->view->verifyResult = array();
            $fileNames = array();
            $hashType = array();
            $hashSoll = array();
            $hashIst = array();
            $hashNumber = array();
            $fileForms = array();
            $verified = array();
            for ($fi = 0; $fi < $fileNumber; $fi++) {
                try {
                    $form = new SignatureForm();
                    $form->FileObject->setValue($document->getFile($fi)->getId());
                    $form->setAction($this->view->url(array('module' => 'admin', 'controller' => 'filemanager', 'action' => 'index', 'docId' => $requestData['docId']), null, true));

                    $fileNames[$fi] = $document->getFile($fi)->getPathName();
                    #if (file_exists($fileNames[$fi]) === false) {
                    #	$fileNumber--;
                    #	continue;
                    #}
                    if (true === is_array ($hashes = $document->getFile($fi)->getHashValue())) {
                        $countHash = count($hashes);
                        for ($hi = 0; $hi < $countHash; $hi++) {
                            $hashNumber[$fi] = $countHash;
                            $hashSoll[$fi][$hi] = $document->getFile($fi)->getHashValue($hi)->getValue();
                            $hashType[$fi][$hi] = $document->getFile($fi)->getHashValue($hi)->getType();
                            if (substr($hashType[$fi][$hi], 0, 3) === 'gpg') {
                                // Check if GPG is used
                                // GPG is not used 
                                // * by default
                                // * if admin has disabled it in config
                                // * if no masterkey has been found
                                // TODO * if GPG is not configured correctly
                                if ($use_gpg === '1') {
                                try {
                                    $this->view->verifyResult[$fileNames[$fi]] = $gpg->verifyPublicationFile($document->getFile($fi));
                                    foreach($this->view->verifyResult[$fileNames[$fi]] as $verifiedArray) {
                                        foreach($verifiedArray as $index => $verificationResult) {
                                            if ($index === 'result') {
                                                foreach ($verificationResult as $result) {
                                                    // Show key used for signature
                                                    if (true === is_object($result) && get_class($result) === 'Crypt_GPG_Signature') {
                                                        $verified[] = $result->getKeyFingerprint();
                                                    }
                                                }
                                            }
                                        }
                                    }
                                } catch (Exception $e) {
                                    $this->view->verifyResult[$fileNames[$fi]] = array('result' => array($e->getMessage()), 'signature' => $hashSoll[$fi][$hi]);
                                }
                                }
                            } else {
                                $hashIst[$fi][$hi] = 0;
                                if (true === $document->getFile($fi)->canVerify()) {
                                    $hashIst[$fi][$hi] = $document->getFile($fi)->getRealHash($hashType[$fi][$hi]);
                                }
                            }
                        }
                        if ($masterkey !== false && in_array($masterkey, $verified) === false) {
                            $fileForms[$fi] = $form;
                        }
                        else {
                        	$fileForms[$fi] = '';
                        }
                        $deleteForm = new DeleteForm();
                        $deleteForm->FileObject->setValue($document->getFile($fi)->getId());
                        $deleteForm->setAction($this->view->url(array('module' => 'admin', 'controller' => 'filemanager', 'action' => 'index', 'docId' => $requestData['docId']), null, true));
                        $fileForms[$fi] .= $deleteForm;
                    }
                }
                catch (Exception $e) {
                    $this->view->noFileSelected = $e->getMessage();
                }
                $this->view->hashType = $hashType;
                $this->view->hashSoll = $hashSoll;
                $this->view->hashIst = $hashIst;
                $this->view->hashNumber = $hashNumber;
                $this->view->fileNames = $fileNames;
                $this->view->fileNumber = $fileNumber;
                $this->view->fileForm = $fileForms;
                if ($fileNumber === 0) {
                    $this->view->actionresult = $this->view->translate('admin_filemanager_nofiles');
                }
    	    }
    	}
    	else
    	{
    		$this->view->noFileSelected = true;
    	}
    }
}
