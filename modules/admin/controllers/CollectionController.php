<?php
/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the North Rhine-Westphalian Library Service Center,
 * the Cooperative Library Network Berlin-Brandenburg, the Saarland University
 * and State Library, the Saxon State Library - Dresden State and University
 * Library, the Bielefeld University Library and the University Library of
 * Hamburg University of Technology with funding from the German Research
 * Foundation and the European Regional Development Fund.
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
 * @author      Felix Ostrowski <ostrowski@hbz-nrw.de>
 * @author      Tobias Tappe <tobias.tappe@uni-bielefeld.de>
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Controller for administration of collections.
 *
 * @category    Framework
 * @package     Module_Admin
 */
class Admin_CollectionController extends Opus_Controller_CRUDAction {

    /**
     * The class of the model being administrated.
     *
     * @var Opus_Model_Abstract
     */
    protected $_modelclass = 'Opus_Model_Collection';

    /**
     * Edits a collection instance
     *
     * @return void
     */
    public function editAction() {
        $collection_id = $this->getRequest()->getParam('id');
        $role_id = $this->getRequest()->getParam('role');
        $form_builder = new Opus_Form_Builder();
        $model = new Opus_Collection($role_id, $collection_id);
        $modelForm = $form_builder->build($model);
        $action_url = $this->view->url(array("action" => "create"));
        $modelForm->setAction($action_url);
        $this->view->form = $modelForm;
    }

    /**
     * Create a new collection instance
     *
     * @return void
     */
    public function newAction() {
        $role = (int) $this->getRequest()->getParam('role');
        $parent = (int) $this->getRequest()->getParam('parent');
        $left_sibling = (int) $this->getRequest()->getParam('left_sibling');
        $form_builder = new Opus_Form_Builder();
        $model = new Opus_Collection($role, null, $parent, $left_sibling);
        $modelForm = $form_builder->build($model);
        $action_url = $this->view->url(array("action" => "create"));
        $modelForm->setAction($action_url);
        $this->view->form = $modelForm;
    }

    /**
     * Redirect to index action of collection roles.
     *
     * @return void
     */
    public function indexAction() {
        $this->_redirectTo('', 'index', 'collection-role');
    }

    /**
     * Deletes a collection instance
     *
     * @return void
     */
    public function deleteAction() {
        if ($this->_request->isPost() === true) {
            $role = (int) $this->getRequest()->getParam('role');
            $id = (int) $this->getRequest()->getPost('id');
            $model = new Opus_Collection($role, $id);
            $model->delete();
            $this->_redirectTo('Model successfully deleted.', 'index');
        } else {
            $this->_redirectTo('', 'index');
        }
    }
}

