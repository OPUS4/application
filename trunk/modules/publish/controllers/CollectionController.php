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
 * @author      Susanne Gottwald <gottwald@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Description of Publish_CollectionController
 *
 * @author Susanne Gottwald
 */
class Publish_CollectionController extends Controller_Action {

    public $session;
    public $log;
    public $document;

    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array()) {
        $this->log = Zend_Registry::get('Zend_Log');
        $this->session = new Zend_Session_Namespace('Publish');
        $this->document = new Opus_Document($this->session->documentId);

        parent::__construct($request, $response, $invokeArgs);
    }

    public function topAction() {

        $this->view->languageSelectorDisabled = true;

        $this->session->step = 1;
        $this->view->title = $this->view->translate('publish_controller_index');
        $this->view->subtitle = $this->view->translate('publish_controller_collection_sub');

        $form = new Publish_Form_PublishingThird();

        $action_url = $this->view->url(array('controller' => 'collection', 'action' => 'sub'));
        $form->setMethod('post');
        $form->setAction($action_url);

        $this->_setThirdFormViewVariables($form);
        $this->view->action_url = $action_url;
        $this->view->form = $form;
    }

    public function subAction() {

        if (!$this->getRequest()->isPost()) {
            return $this->_redirectTo('index', '', 'index');
        }

        $post = $this->getRequest()->getPost();

        if (array_key_exists('send', $post))
            $this->_forward('deposit', 'deposit');

        if (array_key_exists('abortCollection', $post)) {
            $this->session->step = 0;
            $this->_forward('check', 'form');
        }

        if (array_key_exists('goToParentCollection', $post)) {
            if ((int) $this->session->step >= 2)
                $this->session->step = $this->session->step - 1;
        }
        else if (array_key_exists('goToSubCollection', $post)) {
            $this->session->step = $this->session->step + 1;
        }
        else if (array_key_exists('chooseAnotherCollection', $post)) {

            //store inner node
            if (array_key_exists('collection' . $this->session->step, $post)) {
                $collIdToSave = (int) $post['collection' . $this->session->step];
            }

            //store leaf node
            else {
                $index = (int) ($this->session->step) - 1;
                $collIdToSave = (int) $this->session->collection['collection' . $index];
            }

            $this->document->addCollection(new Opus_Collection($collIdToSave));
            $this->document->store();

            $this->session->countCollections = (int) $this->session->countCollections + 1;
            $this->_forward('top');
        }

        foreach ($post AS $p => $v) {
            $this->log->debug("Post: " . $p . " => " . $v);
            $this->session->collection[$p] = $v;
        }

        $this->view->title = $this->view->translate('publish_controller_index');
        $this->view->subtitle = $this->view->translate('publish_controller_collection_sub');

        $form = new Publish_Form_PublishingThird();
        $action_url = $this->view->url(array('controller' => 'collection', 'action' => 'sub'));
        $form->setMethod('post');
        $form->setAction($action_url);
        $this->_setThirdFormViewVariables($form);
        $this->view->action_url = $action_url;
        $this->view->form = $form;
    }

    /**
     * method to set the different variables and arrays for the view and the templates
     * @param <Zend_Form> $form
     */
    private function _setThirdFormViewVariables($form) {
        $errors = $form->getMessages();

        //group fields and single fields for view placeholders
        foreach ($form->getElements() AS $currentElement => $value) {
            //single field name (for calling with helper class)
            $elementAttributes = $form->getElementAttributes($currentElement); //array
            $this->view->$currentElement = $elementAttributes;
            $this->log->debug("Third: set view var " . $currentElement);
        }
    }

}

?>