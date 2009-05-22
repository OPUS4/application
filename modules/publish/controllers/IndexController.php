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
 * @package     Module_Publish
 * @author      Ralf Claussnitzer (ralf.claussnitzer@slub-dresden.de)
 * @author      Henning Gerhardt (henning.gerhardt@slub-dresden.de)
 * @author      Pascal-Nicolas Becker <becker@zib.de>
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Main entry point for this module.
 *
 * @category    Application
 * @package     Module_Publish
 */
class Publish_IndexController extends Zend_Controller_Action {

    /**
     * Redirector - defined for code completion
     *
     * @var Zend_Controller_Action_Helper_Redirector
     */
    protected $_redirector = null;

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
    public function indexAction() {
        $this->view->title = 'Publish';

        $form = new Overview();
        $action_url = $this->view->url(array('controller' => 'index', 'action' => 'create'));
        $form->setAction($action_url);
        $this->view->form = $form;
    }

    /**
     * Create, recreate and validate a document form. If it is valid store it.
     *
     * @return void
     */
    public function createAction() {
        $this->view->title = 'Publish (create)';

        if ($this->_request->isPost() === true) {
            $data = $this->_request->getPost();
            $form_builder = new Form_Builder();
            if (array_key_exists('selecttype', $data) === true) {
                // validate document type
                $form = new Overview();
                if ($form->isValid($data) === true) {
                    // TODO Do not use a hardcoded path
                    $filename = '../config/xmldoctypes/' .
                        $form->getValue('selecttype') .
                        '.xml';
                    if (file_exists($filename) === false) {
                        // TODO: error message
                        // file does not exists, back to select form
                        $this->_redirector->gotoSimple('index');
                    }
                    $type = new Opus_Document_Type($filename);
                    $document = new Opus_Document(null, $type);

                    // add standard field filter
                    $documentWithFilter = new Opus_Model_Filter;
                    $documentWithFilter ->setModel($document)
                        ->setBlacklist(array(
                            'IdentifierOpus3',
                            'Type',
                            'ServerState',
                            'ServerDateUnlocking',
                            'ServerDateValid',
                            'File'))
                        ->setSortOrder(array(
                            'TitleMain',
                            'TitleAbstract',
                            'Language',
                            'PersonAuthor',
                            'PersonContributor',
                            'PersonOther'));

                    $createForm = $form_builder->build($documentWithFilter);
                    $action_url = $this->view->url(array('controller' => 'index', 'action' => 'create'));
                    $form->setAction($action_url);
                    $this->view->form = $createForm;
                } else {
                    // submitted form data is not valid, back to select form
                    $this->view->form = $form;
                }
            } else if (array_key_exists('submit', $data) === false) {
                $form = $form_builder->buildFromPost($data);
                $action_url = $this->view->url(array('controller' => 'index', 'action' => 'create'));
                $form->setAction($action_url);
                $this->view->form = $form;
            } else {
                $form = $form_builder->buildFromPost($data);
                if ($form->isValid($data) === true) {
                    // retrieve old version from model
                    $model = $form_builder->getModelFromForm($form);
                    // overwrite old data in the model with the new data from the form
                    $form_builder->setFromPost($model, $form->getValues());
                    // go ahead to summary
                    $this->view->document_data = $model->toArray();
                    $this->view->title = 'Publish (summary)';
                    $summaryForm = new Summary();
                    $action_url = $this->view->url(array('controller' => 'index', 'action' => 'summary'));
                    $summaryForm->setAction($action_url);
                    $model_ser = $form_builder->compressModel($model);
                    $model_hidden = Form_Builder::HIDDEN_MODEL_ELEMENT_NAME;
                    $summaryForm->$model_hidden->setValue($model_ser);
                    $this->view->form = $summaryForm;
                } else {
                    $this->view->form = $form;
                }
            }
        } else {
            // action used directly go back to main index
            $this->_redirector->gotoSimple('index');
        }
    }

    public function summaryAction() {
        $this->view->title = 'Publish (summary)';
        if ($this->_request->isPost() === true) {
            $summaryForm = new Summary();
            $postdata = $this->_request->getPost();
            if ($summaryForm->isValid($postdata) === true) {
                $form_builder = new Form_Builder();
                $model_hidden = Form_Builder::HIDDEN_MODEL_ELEMENT_NAME;
                $model = $form_builder->uncompressModel($postdata[$model_hidden]);
                if (array_key_exists('submit', $postdata) === true) {
                    $id = $model->store();
                    $this->view->title = 'Publish (upload)';
                    $uploadForm = new FileUpload();
                    $action_url = $this->view->url(array('controller' => 'index', 'action' => 'upload'));
                    $uploadForm->setAction($action_url);
                    // TODO: Security save id to session not to form
                    // Actually it is possible to add Files to every document for everybody!
                    $uploadForm->DocumentId->setValue($id);
                    $this->view->form = $uploadForm;
                } else if (array_key_exists('back', $postdata) === true) {
                    $form = $form_builder->build($model);
                    $action_url = $this->view->url(array('controller' => 'index', 'action' => 'create'));
                    $form->setAction($action_url);
                    $this->view->title = 'Publish (create)';
                    $this->view->form = $form;
                } else {
                    // invalid form return to index
                    $this->_redirector->gotoSimple('index');
                }
            } else {
                // invalid form return to index
                $this->_redirector->gotoSimple('index');
            }
        } else {
            // on non post request redirect to index action
            $this->_redirector->gotoSimple('index');
        }
    }


    /**
     * Create form and handling file uploading
     *
     * @return void
     */
    public function uploadAction() {
        $this->view->title = 'Publish (upload)';
        $uploadForm = new FileUpload();
        $action_url = $this->view->url(array('controller' => 'index', 'action' => 'upload'));
        $uploadForm->setAction($action_url);
        // store uploaded data in application temp dir
        if ($this->_request->isPost() === true) {
            $data = $this->_request->getPost();
            if ($uploadForm->isValid($data) === true) {
                // This works only from Zend 1.7 on
                // $upload = $uploadForm->getTransferAdapter();
                $upload = new Zend_File_Transfer_Adapter_Http();
                $files = $upload->getFileInfo();
                // TODO: Validate document id, error message on fail
                $documentId = $uploadForm->getValue('DocumentId');
                $document = new Opus_Document($documentId);
                $this->view->message = 'Upload succussful!';

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
                $document->store();

                // reset input values fo re-displaying
                $uploadForm->reset();
                // re-insert document id
                $uploadForm->DocumentId->setValue($document->getId());
                $this->view->form = $uploadForm;
            } else {
                // invalid form, populate with transmitted data
                $uploadForm->populate($data);
                $this->view->form = $uploadForm;
            }
        } else {
            // on non post request redirect to index action
            $this->_redirector->gotoSimple('index');
        }
    }

}
