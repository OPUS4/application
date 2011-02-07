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
 * @package     Application - Module Publish
 * @author      Susanne Gottwald <gottwald@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Publish_2_IndexController$
 */

/**
 * Main entry point for this module.
 *
 * @category    Application
 * @package     Module_Publish
 */
class Publish_IndexController extends Controller_Action {

    public $session;
    public $log;
    CONST LABEL = "_label";

    /**
     * Renders the first form:
     * a list of available document types (that can be configured in config.ini
     * and different upload fields
     * 
     * @return void
     *
     */
    public function indexAction() {
        $this->log = Zend_Registry::get('Zend_Log');

        $this->session = new Zend_Session_Namespace('Publish');

        //unset all possible session content
        $this->session->unsetAll();

        $this->view->title = $this->view->translate('publish_controller_index');
        $this->view->subtitle = $this->view->translate('publish_controller_index_sub');

        $form = new Publish_Form_PublishingFirst($this->view);

        //set action_url and give it to the view
        $action_url = $this->view->url(array('controller' => 'form', 'action' => 'upload'));
        $form->setAction($action_url);
        $this->view->action_url = $action_url;

        $form->setMethod('post');

        //give the form to the view and the view variables for different rendering
        $this->view->form = $form;
        $this->_setFirstFormViewVariables($form);

        //initialize session variables
        $this->session->documentType = "";
        $this->session->documentId = "";
        $this->session->fullText = 0;
        $this->session->chooseSpecialCollection = "";
        $this->session->countCollections = 1;
        $this->session->collectionHistory = array();        
    }

    /**
     * method to set the different variables and arrays for the view and the templates
     * @param <Zend_Form> $form
     */
    private function _setFirstFormViewVariables($form) {
        //Todo: Code is duplicated in Form Controller... 
        $errors = $form->getMessages();

        //first form single fields for view placeholders
        foreach ($form->getElements() AS $currentElement => $value) {
            //single field name (for calling with helper class)
            $elementAttributes = $form->getElementAttributes($currentElement); //array
            $this->view->$currentElement = $elementAttributes;
        }

        //Upload-Field
        $displayGroup = $form->getDisplayGroup('documentUpload');
        $this->session->numdocumentUpload = 2;
        $groupName = $displayGroup->getName();
        $groupFields = array(); //Fields
        $groupHiddens = array(); //Hidden fields for adding and deleting fields
        $groupButtons = array(); //Buttons

        foreach ($displayGroup->getElements() AS $groupElement) {

            $elementAttributes = $form->getElementAttributes($groupElement->getName()); //array
            if ($groupElement->getType() === 'Zend_Form_Element_Submit') {
                //buttons
                $groupButtons[$elementAttributes["id"]] = $elementAttributes;
            }
            else if ($groupElement->getType() === 'Zend_Form_Element_Hidden') {
                //hidden fields
                $groupHiddens[$elementAttributes["id"]] = $elementAttributes;
            }
            else {
                //normal fields
                $groupFields[$elementAttributes["id"]] = $elementAttributes;
            }
        }
        $group[] = array();
        $group["Fields"] = $groupFields;
        $group["Hiddens"] = $groupHiddens;
        $group["Buttons"] = $groupButtons;

        $group["Name"] = $groupName;
        $this->view->$groupName = $group;

        $this->session->publishFiles = array();
        $this->view->MAX_FILE_SIZE = $this->session->maxFileSize;
    }

}