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
 * @author      Felix Ostrowski <ostrowski@hbz-nrw.de>
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id: IndexController.php 4541 2010-07-15 11:12:28Z tklein $
 */

/**
 * Main entry point for this module.
 *
 * @category    Application
 * @package     Module_Publish
 */
class Publish_1_IndexController extends Controller_Action {
    /**
     * @todo: extends Zend_Controller_Action ausreichend?
     */
     
    /**
     * Renders a list of available document types.
     *
     * @return void
     *
     */
    public function indexAction() {
        $this->view->title = $this->view->translate('publish_controller_index');
        
        $form = new Publishing();
        $action_url = $this->view->url(array('controller' => 'index', 'action' => 'check'));
        $form->setAction($action_url)
                ->setMethod('post')
                ->setDecorators(array('FormElements', array('Description', array('placement' => 'prepend','tag' => 'h2')), 'Form'));
        
        $this->view->form = $form;
    }    
       
    /**
     * displays and checks the publishing form contents and calls deposit to store the data
     * uses check_array
     * @return <type>
     */
    public function checkAction()
    {
        if ($this->getRequest()->isPost() === true){
            //create form object
            $form = new Publishing();
            //check if variables are valid
            if (!$form->isValid($this->getRequest()->getPost())){
                $this->view->form = $form;
                //show errors
                return $this->render('check');
            }

            //summery the variables
            $this->view->title = $this->view->translate('publish_controller_check');

            //send form values to check view
            $formValues = $form->getValues();
            $this->view->formValues = $formValues;

            $upload = new Zend_File_Transfer_Adapter_Http();
            $files = $upload->getFileInfo();
            $file = $files['fileupload'];


            $type = $formValues['Type'];
            $document = new Opus_Document(null, $type);
            $document->store();

            $docfile = $document->addFile();
            $docfile->setDocumentId($document->getId());
            $docfile->setPathName($file['name']);
            $docfile->setMimeType($file['type']);
            $docfile->setTempFile($file['tmp_name']);
            $docfile->setFromPost($file);
            $docId = $document->store();

            //finally: deposit the data!
            $depositForm = new Publishing();
            $action_url = $this->view->url(array('controller' => 'index', 'action' => 'deposit'));
            $depositForm->setAction($action_url);
            $depositForm->setMethod('post');

            foreach ($formValues as $key => $value) {
                if ($key != 'send') {
                    $hidden = $depositForm->createElement('hidden', $key);
                    $hidden->setValue($value);
                    $depositForm->addElement($hidden);
                }
                else {
                    //do not send the field "send" with the form
                    $depositForm->removeElement('send');
                }
            }
            //send the created document id as hidden field
            $hiddenDocId = $depositForm->createElement('hidden', 'DocumentId');
            $hiddenDocId->setValue($docId);
            $deposit = $depositForm->createElement('submit', 'deposit');
            $depositForm->addElement($deposit)
                    ->addElement($hiddenDocId);

            //send form to view
            $this->view->form = $depositForm;
        }
    }
       
    /**
     * stores a delivered form as document in the database
     * uses check_array
     */
    public function depositAction(){
        $this->view->title = $this->view->translate('publish_controller_index');
        $this->view->subtitle = $this->view->translate('publish_controller_deposit_successful');
        
        if ($this->getRequest()->isPost() === true){
            
            $formValues = $this->getRequest()->getPost();
            $documentId = $formValues['DocumentId'];
            //tape already stored
            //$Type = $formValues['Type'];
            $TitleMain = $formValues['TitleMain'];
            $FirstName = $formValues['AuthorFirstName'];
            $LastName = $formValues['AuthorLastName'];
            $PublishedYear = $formValues['PublishedYear'];
            $TitleAbstract = $formValues['TitleAbstract'];
            
            $document = new Opus_Document($documentId);

            $TitleMainAdd = $document->addTitleMain();
            $TitleMainAdd->setValue($TitleMain);

            $TitleAbstracAdd = $document->addTitleAbstract();
            $TitleAbstracAdd->setValue($TitleAbstract);

            $document->setPublishedYear($PublishedYear);

            $person = new Opus_Person();
            $person->setFirstName($FirstName);
            $person->setLastName($LastName);
            $document->addPersonAuthor($person);

            $document->store();
        }
    }
    
   }
