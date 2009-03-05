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
 * @package     Module_Admin
 * @author      Ralf Claussnitzer (ralf.claussnitzer@slub-dresden.de)
 * @author      Felix Ostrowski (ostrowski@hbz-nrw.de)
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Controller for administration of collection roles.
 *
 * @category    Framework
 * @package     Module_Admin
 */
class Admin_CollectionRoleController extends Controller_CRUDAction {

    /**
     * The class of the model being administrated.
     *
     * @var Opus_Model_Abstract
     */
    protected $_modelclass = 'Opus_CollectionRole';

    /**
     * Overwrite standard show Action to pass additional parameter to view.
     *
     * @return void
     */
    public function showAction() {
        $collectionRole = parent::showAction();
        $selectElement = new Zend_Form_Element_Select('collection');
        $selectElement = $this->__recurseCollection($collectionRole->toArray(), $selectElement);
        $selectElement->setAttrib('size', count($selectElement->getMultiOptions()));
        $addSubCollectionButton = new Zend_Form_Element_Submit('add_subcollection');
        $addSubCollectionButton->setLabel('Add subcollection');
        $deleteButton = new Zend_Form_Element_Submit('delete');
        $deleteButton->setLabel('Delete');
        $editButton = new Zend_Form_Element_Submit('edit');
        $editButton->setLabel('Edit');
        $form = new Zend_Form;
        $form->setAction($this->view->url(array('action' => 'manage')));
        $form->addElements(array($selectElement, $deleteButton, $editButton, $addSubCollectionButton));
        $this->view->form = $form;
    }

    /**
     * Manages a collection tree (create, edit, delete and move collections).
     *
     * @return void
     */
    public function manageAction() {
        if ($this->_request->isPost() === true) {
            $data = $this->_request->getParams();
            $role_id = (int) $data['id'];
            $collection_id = (int) $data['collection'];
            $structure = new Opus_Collection_Structure($role_id);
            if (is_null($this->_request->getPost('delete')) === false) {
                // Delete collection
                $collection = new Opus_Collection($role_id, $collection_id);
                $collection->delete();
                $this->_redirectTo('Collection deleted.', 'show', null, null, array('id' => $role_id));
            } else if (is_null($this->_request->getPost('edit')) === false) {
                // Edit Collection
                $this->_redirectTo('', 'edit', 'collection', 'admin', array('id' => $collection_id, 'role' => $role_id));
            } else if (is_null($this->_request->getPost('add_subcollection')) === false) {
                // Create SubCollection
                $this->_redirectTo('', 'new', 'collection', 'admin', array('parent' => $collection_id,
                            'left_sibling' => 0, 'role' => $role_id));
            } else {
                $this->_redirectTo('', 'show', 'collection', 'admin', array('id' => $role_id));
            }
        } else {
            $this->_redirectTo('', 'index');
        }
    }

    /**
     * Extend standard behaviour to drop tables involved with this role.
     *
     * @return void
     */
    public function deleteAction() {
        $dbadapter = Zend_Db_Table::getDefaultAdapter();
        $id = $this->_request->getPost('id');
        $collectionsContentsTable = $dbadapter->quoteIdentifier("collections_contents_$id");
        $collectionsStructureTable = $dbadapter->quoteIdentifier("collections_structure_$id");
        $collectionsReplacementTable = $dbadapter->quoteIdentifier("collections_replacement_$id");
        $collectionsLinkTable = $dbadapter->quoteIdentifier("link_documents_collections_$id");
        $dbadapter->query("DROP TABLE $collectionsStructureTable");
        $dbadapter->query("DROP TABLE $collectionsReplacementTable");
        $dbadapter->query("DROP TABLE $collectionsLinkTable");
        $dbadapter->query("DROP TABLE $collectionsContentsTable");
        parent::deleteAction();
    }

    /**
     * Recursively adds collections as options to a select list.
     *
     * @param  array                    $collection The array representation of the collection to be displayed.
     * @param  Zend_Form_Element_Select &$list      The select list.
     * @param  mixed                    $level      (Optional) Depth of the current entry, defaults to 0.
     * @return $list
     */
    private function __recurseCollection(array $collection, Zend_Form_Element_Select &$list, $level = 0) {
        foreach ($collection as $subCollection) {
            $list->addMultiOption($subCollection['Id'], str_repeat('-', $level) . $subCollection['Name']);
            if (empty($subCollection['SubCollection']) === false) {
                $this->__recurseCollection($subCollection['SubCollection'], $list, ($level + 1));
            }
        }
        return $list;
    }

}

