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
 * @author      Sascha Szott <szott@zib.de>
 * @author     	Thoralf Klein <thoralf.klein@zib.de>
 * @author      Felix Ostrowski <ostrowski@hbz-nrw.de>
 * @author      Tobias Tappe <tobias.tappe@uni-bielefeld.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Controller for administration of collections.
 *
 */
class Admin_CollectionController extends Controller_Action {

    /**
     * Setup theme path
     *
     * @return void
     */
    public function init() {
        parent::init();
        Opus_Collection::setThemesPath(APPLICATION_PATH . '/public/layouts');
    }

    /**
     * List all available collection role instances
     *
     * @return void
     */
    public function indexAction() {
        $this->view->collectionRoles = Opus_CollectionRole::fetchAll();
    }

    public function newroleAction() {
        $collectionRoleModel = new Admin_Model_CollectionRole();        
        $this->view->form = $this->getRoleForm($collectionRoleModel->getObject());
    }

    public function editroleAction() {
        $collectionRoleModel = new Admin_Model_CollectionRole($this->getRequest()->getParam('roleid', ''));
        $this->view->form = $this->getRoleForm($collectionRoleModel->getObject());
    }

    /**
     * Create a new collection instance
     *
     * @return void
     */
    public function newAction() {
        $id = $this->getRequest()->getParam('id');
        if (is_null($id)) {
            $this->_redirectToAndExit('index', 'id parameter is missing');
        }
        $type = $this->getRequest()->getParam('type');
        if (is_null($type)) {
            $this->_redirectToAndExit('index', 'type parameter is missing');
        }

        $collectionModel = new Admin_Model_Collection();
        $this->view->form = $this->getForm($collectionModel->getObject(), $id, $type);
    }

    /**
     * Edits a collection instance
     *
     * @return void
     */
    public function editAction() {
        $collectionModel = new Admin_Model_Collection($this->getRequest()->getParam('id', ''));
        $this->view->form = $this->getForm($collectionModel->getObject());
    }

    private function moveCollection($increment) {
        throw new Application_Exception('Method is not implemented!');
        $collectionModel = new Admin_Model_Collection($this->getRequest()->getParam('id', ''));

        // evaluate parameter ref
    }

    public function moveroleAction() {
        try {
            $collectionRoleModel = new Admin_Model_CollectionRole($this->getRequest()->getParam('roleid', ''));
            $collectionRoleModel->move($this->getRequest()->getParam('pos'));
            $this->_redirectTo('index', 'Operation completed successfully.');
        }
        catch (Application_Exception $e) {
            $this->_redirectToAndExit('index', $e->getMessage(), 'collection');
        }
    }

    public function moveupAction() {
        $collectionParentId = $this->moveCollection(-1);
        if (is_null($collectionParentId)) {
            $this->_redirectTo('index', 'Operation completed successfully.');
        }
        else {
            $this->_redirectTo('show', 'Operation completed successfully.', 'controller', 'admin', array('id' => $collectionParentId));
        }
    }

    public function movedownAction() {
        $collectionParentId = $this->moveCollection(+1);
        if (is_null($collectionParentId)) {
            $this->_redirectTo('index', 'Operation completed successfully.');
        }
        else {
            $this->_redirectTo('show', 'Operation completed successfully.', 'controller', 'admin', array('id' => $collectionParentId));
        }
    }

    private function changeRoleVisibility($visibility) {        
        try {
            $collectionRoleModel = new Admin_Model_CollectionRole($this->getRequest()->getParam('roleid', ''));
            $collectionRoleModel->setVisibility($visibility);
        }
        catch (Application_Exception $e) {
            $this->_redirectToAndExit('index', $e->getMessage(), 'collection');
        }
    }

    private function changeCollectionVisibility($visibility) {
        try {
            $collectionModel = new Admin_Model_Collection($this->getRequest()->getParam('id', ''));
            return $collectionModel->setVisiblity($visibility);
        }
        catch (Application_Exception $e) {
            $this->_redirectToAndExit('index', $e->getMessage(), 'collection');
        }
    }

    public function hideroleAction() {
        $this->changeRoleVisibility(false);
        $this->_redirectTo('index', 'Operation completed successfully.');
    }
    
    public function unhideroleAction() {
        $this->changeRoleVisibility(true);
        $this->_redirectTo('index', 'Operation completed successfully.');
    }

    public function hideAction() {
        $id = $this->changeCollectionVisibility(false);
        $this->_redirectTo('show', 'Operation completed successfully.', 'collection', 'admin', array('id' => $id));
    }

    public function unhideAction() {
        $id = $this->changeCollectionVisibility(true);
        $this->_redirectTo('show', 'Operation completed successfully.', 'collection', 'admin', array('id' => $id));
    }

    public function deleteroleAction() {
        try {
            $collectionRoleModel = new Admin_Model_CollectionRole($this->getRequest()->getParam('roleid', ''));
            $collectionRoleModel->delete();
        }
        catch (Application_Exception $e) {
            $this->_redirectToAndExit('index', $e->getMessage(), 'collection');
            return;
        }
        $this->_redirectTo('index', 'Operation completed successfully.');
    }

    public function deleteAction() {
        try {
            $collectionModel = new Admin_Model_Collection($this->getRequest()->getParam('id', ''));
            $returnId = $collectionModel->delete();
            $this->_redirectTo('show', 'Operation completed successfully.', 'collection', 'admin', array ('id' => $returnId));
        }
        catch (Application_Exception $e) {
            $this->_redirectToAndExit('index', $e->getMessage(), 'collection');            
        }        
    }

    public function showAction() {
        $roleId = $this->getRequest()->getParam('role');
        $id = null;
        if (!is_null($roleId)) {
            // collection role without root collection: create a root collection
            $rootCollection = new Opus_Collection();
            $collectionRole = new Opus_CollectionRole($roleId);
            $collectionRole->addRootCollection($rootCollection);
            $collectionRole->store();
            $id = $rootCollection->getId();
        }
        else {
            $id = $this->getRequest()->getParam('id', '');
        }

        $collectionModel = null;
        try {
            $collectionModel = new Admin_Model_Collection($id);
        }
        catch (Application_Exception $e) {
            $this->_redirectToAndExit('index', $e);
            return;
        }
        $collection = $collectionModel->getObject();
        
        $this->view->breadcrumb = array_reverse($collection->getParents());
        $this->view->collections = $collection->getChildren();
        $this->view->collection_id = $collection->getId();
        
        $role = $collection->getRole();
        $this->view->role_id    = $role->getId();
        $this->view->role_name  = $role->getDisplayName();
        
    }

    private function returnIfNotPost() {
        if (!$this->getRequest()->isPost()) {
            $this->_redirectToAndExit('index');
        }
    }

    private function initCreateRoleForm($form, $collectionRole) {
        if ($collectionRole->isNewRecord()) {
            $form->setAction($this->view->url(array('action' => 'createrole')));
        }
        else {
            $form->setAction($this->view->url(array('action' => 'createrole', 'oid' => $collectionRole->getId())));
        }
        return $form;        
    }

    public function createroleAction() {
        $this->returnIfNotPost();

        $data = $this->_request->getPost();

        $objectId = $this->getRequest()->getParam('oid');
        $collectionRole = null;
        if (is_null($objectId)) {
            $collectionRole = new Opus_CollectionRole();
        }
        else {
            $collectionRole = new Opus_CollectionRole($objectId);
        }

        $form_builder = new Form_Builder();        
        $form_builder->buildModelFromPostData($collectionRole, $data['Opus_Model_Filter']);
        $form = $form_builder->build($this->__createRoleFilter($collectionRole));
        
        if (!$form->isValid($data)) {
            $this->view->form = $this->initCreateRoleForm($form, $collectionRole);
        }
        else {
            // manuelles Überprüfen der IBs in Tabelle collections_roles
            $tmpRole = Opus_CollectionRole::fetchByName($collectionRole->getName());
            if (!is_null($tmpRole) && $tmpRole->getId() !== $collectionRole->getId()) {
                $this->view->form = $this->initCreateRoleForm($form, $collectionRole);
                $this->view->message = 'name is not unique';
                return;
            }
            
            $tmpRole = Opus_CollectionRole::fetchByOaiName($collectionRole->getOaiName());
            if (!is_null($tmpRole) && $tmpRole->getId() !== $collectionRole->getId()) {
                $this->view->form = $this->initCreateRoleForm($form, $collectionRole);
                $this->view->message = 'oainame is not unique';
                return;
            }

            if (true === $collectionRole->isNewRecord()) {
                if (true === is_null($collectionRole->getRootCollection())) {
                    $collectionRole->addRootCollection();
                    $collectionRole->getRootCollection()->setVisible('1');
                }
                $collectionRole->store();
                $this->_redirectTo('index', 'Collection role \'' . $collectionRole->getName() . '\' successfully created.');
            }
            else {
                $collectionRole->store();
                $this->_redirectTo('index', 'Collection role \'' . $collectionRole->getName() . '\' successfully edited.');
            }
        }
    }

    public function createAction() {
        $this->returnIfNotPost();
        
        $data = $this->_request->getPost();

        $objectId = $this->getRequest()->getParam('oid');
        $collection = null;
        if (is_null($objectId)) {
            $collection = new Opus_Collection();
        }
        else {
            $collection = new Opus_Collection($objectId);
        }

        $form_builder = new Form_Builder();
        $form_builder->buildModelFromPostData($collection, $data['Opus_Model_Filter']);
        $form = $form_builder->build($this->__createFilter($collection));

        if (!$form->isValid($data)) {
            if ($collection->isNewRecord()) {
                $form->setAction($this->view->url(array('action' => 'create', 'id' => $this->getRequest()->getParam('id'), 'type' => $this->getRequest()->getParam('type'))));
            }
            else {
                $form->setAction($this->view->url(array('action' => 'create', 'oid' => $objectId, 'id' => $this->getRequest()->getParam('id'), 'type' => $this->getRequest()->getParam('type'))));
            }
            $this->view->form = $form;
        }
        else {            
            if (true === $collection->isNewRecord()) {
                $id = $this->getRequest()->getParam('id');
                $type = $this->getRequest()->getParam('type');
                if (is_null($id)) {
                    $this->_redirectToAndExit('index', 'id parameter is missing');
                    return;
                }
                if (is_null($type)) {
                    $this->_redirectToAndExit('index', 'type parameter is missing');
                    return;
                }
                if ($type === 'child') {
                    $refCollection = new Opus_Collection($id);
                    $refCollection->addFirstChild($collection);
                    $refCollection->store();
                }
                else if ($type === 'sibling') {
                    $refCollection = new Opus_Collection($id);
                    $refCollection->addNextSibling($collection);
                    $refCollection->store();
                }
                else {
                    $this->_redirectToAndExit('index', 'type paramter invalid');
                    return;
                }
                $this->_redirectTo('show', 'Insert successful', 'collection', 'admin', array('id' => $collection->getId()));
            }
            else {
                // nur Änderungen
                $collection->store();
                $parents = $collection->getParents();
                if (count($parents) === 1) {
                    $this->_redirectTo('show', 'Edit successful', 'collection', 'admin', array('id' => $collection->getRoleId()));
                }
                else {
                    $this->_redirectTo('show', 'Edit successful', 'collection', 'admin', array('id' => $parents[1]->getId()));
                }
            }
        }
    }

    /**
     * Assign a document to a collection (used in document administration)
     *
     * @return void
     */
    public function assignAction() {
        $documentId = $this->getRequest()->getParam('document');
        if (is_null($documentId)) {
            $this->_redirectToAndExit('index', array('level' => 'failure', 'message' => 'document parameter missing'), 'index');
            return;
        }
        
        if ($this->getRequest()->isPost() === true) {
            // Zuordnung des Dokuments zur Collection ist erfolgt
            $collectionModel = new Admin_Model_Collection($this->getRequest()->getParam('id', ''));
            $collectionModel->addDocument($documentId);
            $this->_redirectToAndExit(
                    'edit',
                    'Document successfully assigned to collection "' . $collectionModel->getDisplayName() . '".',
                    'documents', 'admin', array('id' => $documentId));
            return;
        }
        $collectionId = $this->getRequest()->getParam('id');
        if (is_null($collectionId)) {
            // Einsprungseite anzeigen
            $this->prepareAssignStartPage($documentId);
        }
        else {
            // Collection ausgewählt: Subcollections anzeigen
            $this->prepareAssignSubPage($documentId, $collectionId);
        }
    }

    private function prepareAssignStartPage($documentId) {
        $collectionRoles = Opus_CollectionRole::fetchAll();
        $this->view->collections = array();
        foreach ($collectionRoles as $collectionRole) {
            $rootCollection = $collectionRole->getRootCollection();
            if (!is_null($rootCollection)) {
                array_push($this->view->collections,
                        array(
                            'id' => $rootCollection->getId(),
                            'name' => $collectionRole->getDisplayName(),
                            'hasChildren' => (count($rootCollection->getChildren()) > 0)));
            }
        }
        $this->view->documentId = $documentId;
        $this->view->breadcrumb = array();
        $this->view->role_name = $collectionRole->getDisplayName();
    }

    private function prepareAssignSubPage($documentId, $collectionId) {
        $collection = new Opus_Collection($collectionId);
        $children = $collection->getChildren();
        if (count($children) === 0) {
            // zurück zur Ausgangsansicht
            $this->_redirectToAndExit('assign', array('level' => 'failure', 'message' => 'specified collection does not have any subcollections'), 'collection', 'admin', array('document' => $documentId));
        }
        else {
            $this->view->collections = array();
            foreach ($children as $child) {
                array_push($this->view->collections,
                        array(
                            'id' => $child->getId(),
                            'name' => $child->getDisplayName(),
                            'hasChildren' => (count($child->getChildren()) > 0)));
            }
            $this->view->documentId = $documentId;
            $this->view->breadcrumb = array_reverse($collection->getParents());
            $this->view->role_name = $collection->getRole()->getDisplayName();
        }
    }

    private function getForm($collection, $id = null, $type = null) {
        $form_builder = new Form_Builder();
        $collectionForm = $form_builder->build($this->__createFilter($collection));
        if ($collection->isNewRecord()) {
            $collectionForm->setAction($this->view->url(array('action' => 'create', 'id' => $id, 'type' => $type)));
        }
        else {
            $collectionForm->setAction($this->view->url(array('action' => 'create', 'oid' => $collection->getId(), 'id' => $id, 'type' => $type)));
        }
        return $collectionForm;
    }

    /**
     * Returns a filtered representation of the collection.
     *
     * @param  Opus_Model_Abstract $collection The collection to be filtered.
     * @return Opus_Model_Filter The filtered collection.
     */
    private function __createFilter(Opus_Model_Abstract $collection) {
        $filter = new Opus_Model_Filter();
        $filter->setModel($collection);
        $filter->setBlacklist(array('Parents', 'Children', 'PendingNodes', 'RoleId', 'RoleName', 'RoleDisplayFrontdoor', 'RoleVisibleFrontdoor', 'PositionKey', 'PositionId', 'SortOrder'));
        $filter->setSortOrder(array('Name', 'Number', 'Visible'));
        return $filter;
    }

    private function getRoleForm(Opus_CollectionRole $collectionRole) {
        $form_builder = new Form_Builder();
        $collectionForm = $form_builder->build($this->__createRoleFilter($collectionRole));
        if ($collectionRole->isNewRecord()) {
            $collectionForm->setAction($this->view->url(array('action' => 'createrole')));
        }
        else {
            $collectionForm->setAction($this->view->url(array('action' => 'createrole', 'oid' => $collectionRole->getId())));
        }
        return $collectionForm;
    }

    private function __createRoleFilter(Opus_CollectionRole $collectionRole) {
        $filter = new Opus_Model_Filter();
        $filter->setModel($collectionRole);
        $filter->setBlacklist(array('RootCollection'));
        return $filter;
    }
}