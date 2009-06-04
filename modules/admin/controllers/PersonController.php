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
 * @author      Henning Gerhardt (henning.gerhardt@slub-dresden.de)
 * @author      Birgit Dressler (b.dressler@sulb.uni-saarland.de)
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Main entry point for this module.
 *
 * @category    Application
 * @package     Module_Admin
 */

class Admin_PersonController extends Zend_Controller_Action {

    /**
     * TODO
     *
     * @return void
     */
    public function indexAction() {
        $this->view->title = $this->view->translate('Person_Controller');


        $person_input = new Zend_Form_Element_Text('person');
        $person_input->setRequired(true)
            ->setLabel('Person_Id');

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('transmit_index');

        $form = new Zend_Form();
        //$action_url = $this->view->url(array("module" => "publish", "controller" => "index", "action" => "index"));
        $action_url = $this->view->url(array("controller" => "person", "action" => "edit"));
        $form->setAction($action_url);
        $form->addElements(array($person_input, $submit));


        $this->view->form = $form;
    }

    /**
     * TODO
     *
     * @return void
     */
    public function editAction() {

        $this->view->title = $this->view->translate('Person_Controller');

        if (true === $this->_request->isPost()) {
            // post values
            $this->view->birgit = 'Post values';
            $postvalues = $this->_request->getPost();
            //var_dump($postvalues);
            $person_id = $postvalues['person'];
            $person = new Opus_Person($person_id);
            $form_builder = new Form_Builder();
            $form = $form_builder->build($person);

            $action_url = $this->view->url(array("controller" => "person", "action" => "save"));
            $form->setAction($action_url);
            $this->view->form = $form;
        } else {
            // non post values
            $this->view->birgit = 'data_error';
        }
    }

    /**
     * TODO
     *
     * @return void
     */
    public function saveAction() {
        $this->view->title = $this->view->translate('Person_Controller');

        if (true === $this->_request->isPost()) {
            // post values
            $postvalues = $this->_request->getPost();
            $form_builder = new Form_Builder;
            $form = $form_builder->buildFromPost($postvalues);
            $model = $form_builder->getModelFromForm($form);
            // store changed values
            $model->store();
            $form = $form_builder->build($model);
            $submit = new Zend_Form_Element_Submit('submit');
            $submit->setLabel('transmit_index');
            $action_url = $this->view->url(array("controller" => "person", "action" => "index"));
            $form->setAction($action_url);
            $this->view->form = $form;
            $this->view->store = $this->view->translate('stored');
        } else {
            // non post values
            $this->view->store = $this->view->translate('notstored');
        }
    }

}
