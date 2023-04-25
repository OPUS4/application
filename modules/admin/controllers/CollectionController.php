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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Collection;
use Opus\Common\CollectionInterface;
use Opus\Common\CollectionRole;
use Opus\Common\CollectionRoleInterface;
use Opus\Common\Model\NotFoundException;

/**
 * Controller for administration of collections.
 *
 * TODO $this->_redirectToAndExit does not have return value, but is used with return here
 * TODO refactor, move into model classes, etc.
 */
class Admin_CollectionController extends Application_Controller_Action
{
    /**
     * Setup theme path
     */
    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {
        $this->_helper->Redirector->redirectToAndExit('index', '', 'collectionroles');
    }

    /**
     * Create a new collection instance
     */
    public function newAction()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id === null) {
            $this->_helper->Redirector->redirectToAndExit(
                'index',
                ['failure' => 'id parameter is missing'],
                'collectionroles'
            );
            return;
        }
        $type = $this->getRequest()->getParam('type');
        if ($type === null) {
            $this->_helper->Redirector->redirectToAndExit(
                'index',
                ['failure' => 'type parameter is missing'],
                'collectionroles'
            );
            return;
        }

        $siblingCollectionModel = new Admin_Model_Collection($id);
        $collectionModel        = new Admin_Model_Collection();
        $this->view->form       = $this->getForm($collectionModel->getObject(), $id, $type);
        $this->setCollectionBreadcrumb($siblingCollectionModel->getObject()->getRole());
    }

    /**
     * Edit a collection instance
     */
    public function editAction()
    {
        $collectionModel  = new Admin_Model_Collection($this->getRequest()->getParam('id', ''));
        $this->view->form = $this->getForm($collectionModel->getObject());
        $this->setCollectionBreadcrumb($collectionModel->getObject()->getRole());
    }

    /**
     * Moves a collection within the same hierarchy level.
     */
    public function moveAction()
    {
        try {
            $collectionModel = new Admin_Model_Collection($this->getRequest()->getParam('id', ''));
            $parentId        = $collectionModel->move($this->getRequest()->getParam('pos'));
            $this->_helper->Redirector->redirectTo(
                'show',
                $this->view->translate('admin_collections_move', [$collectionModel->getName()]),
                'collection',
                'admin',
                ['id' => $parentId]
            );
        } catch (Admin_Model_Exception $e) {
            $this->_helper->Redirector->redirectToAndExit(
                'index',
                ['failure' => $e->getMessage()],
                'collectionroles'
            );
        }
    }

    /**
     * @param bool|int $visibility
     */
    private function changeCollectionVisibility($visibility)
    {
        try {
            $collectionModel = new Admin_Model_Collection($this->getRequest()->getParam('id', ''));
            $id              = $collectionModel->setVisiblity($visibility);
            $this->_helper->Redirector->redirectTo(
                'show',
                $this->view->translate(
                    'admin_collections_changevisibility',
                    [$collectionModel->getName()]
                ),
                'collection',
                'admin',
                ['id' => $id]
            );
        } catch (Application_Exception $e) {
            $this->_helper->Redirector->redirectToAndExit(
                'index',
                ['failure' => $e->getMessage()],
                'collectionroles'
            );
        }
    }

    public function hideAction()
    {
        $this->changeCollectionVisibility(false);
    }

    public function unhideAction()
    {
        $this->changeCollectionVisibility(true);
    }

    public function deleteAction()
    {
        try {
            $collectionModel = new Admin_Model_Collection($this->getRequest()->getParam('id', ''));
            $name            = $collectionModel->getName();
            $returnId        = $collectionModel->delete();
            $message         = $this->view->translate('admin_collections_delete', [$name]);
            $this->_helper->Redirector->redirectTo('show', $message, 'collection', 'admin', ['id' => $returnId]);
        } catch (Application_Exception $e) {
            $this->_helper->Redirector->redirectToAndExit(
                'index',
                ['failure' => $e->getMessage()],
                'collectionroles'
            );
        }
    }

    public function showAction()
    {
        $roleId = $this->getRequest()->getParam('role');
        $id     = null;
        if ($roleId !== null) {
            $collectionRole = CollectionRole::get($roleId);
            $rootCollection = $collectionRole->getRootCollection();
            if ($rootCollection === null) {
                // collection role without root collection: create a new root collection
                $rootCollection = $collectionRole->addRootCollection();
                $collectionRole->store();
            }
            $id = $rootCollection->getId();
        } else {
            $id = $this->getRequest()->getParam('id', '');
        }

        $collectionModel = null;
        try {
            $collectionModel = new Admin_Model_Collection($id);
        } catch (Application_Exception $e) {
            $this->_helper->Redirector->redirectToAndExit(
                'index',
                ['failure' => $e->getMessage()],
                'collectionroles'
            );
            return;
        }

        $collection                = $collectionModel->getObject();
        $this->view->breadcrumb    = array_reverse($collection->getParents());
        $this->view->collections   = $collection->getChildren();
        $this->view->collection_id = $collection->getId();

        $role                  = $collection->getRole();
        $this->view->role_id   = $role->getId();
        $this->view->role_name = $role->getDisplayName();

        $this->setCollectionBreadcrumb($role);
    }

    /**
     * @param CollectionRoleInterface $role
     */
    public function setCollectionBreadcrumb($role)
    {
        $page = $this->view->navigation()->findOneBy('label', 'admin_collection_index');
        if ($page !== null && $role !== null) {
            $page->setLabel('default_collection_role_' . $role->getName());
            $page->setParam('id', $role->getRootCollection()->getId());
        }
    }

    /**
     * Creates new collection.
     *
     * @throws Exception
     * @throws Zend_Form_Exception
     * TODO logic should be moved to model
     */
    public function createAction()
    {
        if (! $this->getRequest()->isPost()) {
            $this->_helper->Redirector->redirectToAndExit('index', '', 'collectionroles');
            return;
        }

        $data            = $this->getRequest()->getPost();
        $collectionModel = new Admin_Model_Collection($this->getRequest()->getParam('oid'));
        $collection      = $collectionModel->getObject();

        $form = new Admin_Form_Collection();

        if (! $form->isValid($data)) {
            if ($collection->isNewRecord()) {
                $form->setAction(
                    $this->view->url(
                        [
                            'action' => 'create',
                            'id'     => $this->getRequest()->getParam('id'),
                            'type'   => $this->getRequest()->getParam('type'),
                        ]
                    )
                );
                $this->view->title = 'admin_collections_collection_new';
            } else {
                $form->setAction(
                    $this->view->url(
                        [
                            'action' => 'create',
                            'oid'    => $collection->getId(),
                            'id'     => $this->getRequest()->getParam('id'),
                            'type'   => $this->getRequest()->getParam('type'),
                        ]
                    )
                );
                $this->view->title = 'admin_collections_collection_edit';
            }
            $form->populate($data);
            $this->view->form = $form;
            return;
        }

        $form->updateModel($collection);

        if (true === $collection->isNewRecord()) {
            $id = $this->getRequest()->getParam('id');
            if ($id === null) {
                $this->_helper->Redirector->redirectToAndExit(
                    'index',
                    ['failure' => 'id parameter is missing'],
                    'collectionroles'
                );
                return;
            }

            $type = $this->getRequest()->getParam('type');
            if ($type === null) {
                $this->_helper->Redirector->redirectToAndExit(
                    'index',
                    ['failure' => 'type parameter is missing'],
                    'collectionroles'
                );
                return;
            }

            switch ($type) {
                case 'child':
                    $refCollection = Collection::get($id);
                    $refCollection->addFirstChild($collection);
                    $refCollection->store();
                    $message = $this->view->translate('admin_collections_add', [$collectionModel->getName()]);
                    break;

                case 'sibling':
                    $refCollection = Collection::get($id);
                    $refCollection->addNextSibling($collection);
                    $refCollection->store();
                    $message = $this->view->translate('admin_collections_add', [$collectionModel->getName()]);
                    break;

                default:
                    $this->_helper->Redirector->redirectToAndExit(
                        'index',
                        ['failure' => 'type paramter invalid'],
                        'collectionroles'
                    );
                    return;
            }

            $this->_helper->Redirector->redirectTo(
                'show',
                $message,
                'collection',
                'admin',
                [
                    'id'     => $collection->getParentNodeId(),
                    'anchor' => 'col' . $collection->getId(),
                ]
            );

            return;
        }

        // nur Änderungen
        $collection->store();
        $message = $this->view->translate('admin_collections_edit', [$collectionModel->getName()]);
        $parents = $collection->getParents();
        if (count($parents) === 1) {
            // TODO when is this executed
            $this->_helper->Redirector->redirectTo(
                'show',
                $message,
                'collection',
                'admin',
                ['id' => $collection->getRoleId()]
            );
            return;
        }

        $this->_helper->Redirector->redirectTo(
            'show',
            $message,
            'collection',
            'admin',
            [
                'id'     => $parents[1]->getId(),
                'anchor' => 'col' . $collection->getId(),
            ]
        );
    }

    /**
     * This action sorts the children of a collection by name or number.
     *
     * Parameters:
     * id: ID of collection
     * sortby: 'name' or 'number'
     * order: 'asc' or 'desc'
     *
     * TODO translate error messages
     */
    public function sortAction()
    {
        $request = $this->getRequest();

        $collectionId = $request->getParam('id');
        $sortBy       = $request->getParam('sortby');
        $order        = $request->getParam('order');

        if ($collectionId === null) {
            $this->_helper->Redirector->redirectToAndExit(
                'index',
                ['failure' => 'id parameter is missing'],
                'collectionroles'
            );
            return;
        }

        if (! is_numeric($collectionId)) {
            $this->_helper->Redirector->redirectToAndExit(
                'index',
                ['failure' => 'id parameter must be an integer'],
                'collectionroles'
            );
            return;
        }

        try {
            $collection = Collection::get($collectionId);

            if ($sortBy === null) {
                $this->_helper->Redirector->redirectToAndExit(
                    'show',
                    null,
                    'collection',
                    'admin',
                    ['id' => $collectionId]
                );
                return;
            }

            $reverse = strtolower($order) === 'desc';

            switch ($sortBy) {
                case 'name':
                    $collection->sortChildrenByName($reverse);
                    break;
                case 'number':
                    $collection->sortChildrenByNumber($reverse);
                    break;
                default:
                    $this->_helper->Redirector->redirectToAndExit(
                        'show',
                        ['failure' => 'parameter sortby must have value name or number'],
                        'collection',
                        'admin',
                        ['id' => $collectionId]
                    );
                    return;
            }
        } catch (NotFoundException $omnfe) {
            $this->_helper->Redirector->redirectToAndExit(
                'index',
                ['failure' => "collection with id $collectionId not found"],
                'collectionroles'
            );
            return;
        }

        $this->_helper->Redirector->redirectToAndExit(
            'show',
            null,
            'collection',
            'admin',
            ['id' => $collectionId]
        );
    }

    /**
     * Assign a document to a collection (used in document administration)
     */
    public function assignAction()
    {
        $documentId = $this->getRequest()->getParam('document');
        if ($documentId === null) {
            $this->_helper->Redirector->redirectToAndExit(
                'index',
                ['failure' => 'document parameter missing'],
                'collectionroles'
            );
            return;
        }

        if ($this->getRequest()->isPost()) {
            // Zuordnung des Dokuments zur Collection ist erfolgt
            $storeNow = $this->getRequest()->getParam('oldform', false);

            $colId = $this->getRequest()->getParam('id', '');

            if ($storeNow) {
                // Speichere Collection sofort
                $collectionModel = new Admin_Model_Collection($colId);
                $collectionModel->addDocument($documentId);

                $this->_helper->Redirector->redirectToAndExit(
                    'edit',
                    $this->view->translate('admin_document_add_collection_success', [$collectionModel->getName()]),
                    'document',
                    'admin',
                    ['id' => $documentId, 'section' => 'collections']
                );
                return;
            } else {
                $this->_helper->Redirector->redirectToAndExit(
                    'edit',
                    null,
                    'document',
                    'admin',
                    [
                        'id'       => $documentId,
                        'hash'     => '123',
                        'continue' => 'addcol',
                        'colId'    => $colId,
                    ]
                );
                return;
            }
        }

        $collectionId = $this->getRequest()->getParam('id');
        if ($collectionId === null) {
            // Einsprungseite anzeigen
            $this->prepareAssignStartPage($documentId);
        } else {
            // Collection ausgewählt: Subcollections anzeigen
            $this->prepareAssignSubPage($documentId, $collectionId);
        }

        $this->view->documentAdapter = new Application_Util_DocumentAdapter($this->view, $documentId);
    }

    /**
     * Bereitet die Einstiegseite für das Zuweisen von einer Collection zu einem Dokument vor.
     *
     * Auf der Einstiegsseite werden die CollectionRoles mit Root-Collections aufgelistet. Da ein Dokument nur einer
     * Collection zugewiesen werden kann wird die Sichtbarkeit der Root-Collection als Kriterium für die Markierung
     * als sichtbare oder unsichtbare Collection verwendet.
     *
     * @param int $documentId
     */
    private function prepareAssignStartPage($documentId)
    {
        $helper = new Admin_Model_Collections($this->view);
        $helper->setView($this->view);
        $this->view->collections = $helper->getCollectionRolesInfo($documentId);
        $this->view->documentId  = $documentId;
    }

    /**
     * @param int $documentId
     * @param int $collectionId
     * @throws NotFoundException
     */
    private function prepareAssignSubPage($documentId, $collectionId)
    {
        $collection = Collection::get($collectionId);
        $children   = $collection->getChildren();
        if (count($children) === 0) {
            // zurück zur Ausgangsansicht
            $this->_helper->Redirector->redirectToAndExit(
                'assign',
                ['failure' => 'specified collection does not have any subcollections'],
                'collection',
                'admin',
                ['document' => $documentId]
            );
            return;
        }
        $this->view->collections = [];

        $role = $collection->getRole();

        foreach ($children as $child) {
            array_push(
                $this->view->collections,
                [
                    'id'          => $child->getId(),
                    'name'        => $child->getNumberAndName(),
                    'hasChildren' => $child->hasChildren(),
                    'visible'     => $child->getVisible(),
                    'isLeaf'      => ! $child->hasChildren(),
                    'role'        => $role,
                    'collection'  => $child,
                    'assigned'    => $child->holdsDocumentById($documentId),
                ]
            );
        }

        $this->view->documentId = $documentId;
        $this->view->breadcrumb = array_reverse($collection->getParents());
        $this->view->role_name  = $role->getDisplayName();
    }

    /**
     * @param CollectionInterface $collection
     * @param int|null            $id TODO BUG
     * @param string|null         $type
     * @return Admin_Form_Collection
     */
    private function getForm($collection, $id = null, $type = null)
    {
        $form = new Admin_Form_Collection();
        $form->populateFromModel($collection);

        if ($collection->isNewRecord()) {
            $form->setAction($this->view->url(['action' => 'create', 'id' => $id, 'type' => $type]));
        } else {
            $form->setAction(
                $this->view->url(
                    [
                        'action' => 'create',
                        'oid'    => $collection->getId(),
                        'id'     => $id,
                        'type'   => $type,
                    ]
                )
            );
        }
        return $form;
    }
}
