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
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id: EnrichmentkeyController.php 9368 2011-12-13 09:05:15Z gmaiwald $
 */

class Admin_EnrichmentkeyController extends Controller_Action {

    /**
     * Shows list of all enrichmentkeys.
     */
    public function indexAction() {
        $this->view->title = $this->view->translate('admin_enrichmentkey_index');
        $enrichmentkeys = Opus_EnrichmentKey::getAll();

        if (!empty($enrichmentkeys)) {
            $this->view->enrichmentkeys = array();
            foreach ($enrichmentkeys as $enrichmentkey) {
                $this->view->enrichmentkeys[$enrichmentkey->getName()] = $enrichmentkey->getDisplayName();
            }
        }
        else {
            $this->view->render('none');
        }

        $this->view->protectedKeys = Opus_EnrichmentKey::getAllReferenced();
    }

    /**
     * Show enrichmentkey information.
     */
    public function showAction() {
        $this->view->title = $this->view->translate('admin_enrichmentkey_action_show');
        $name = $this->getRequest()->getParam('name');

        if (!empty($name)) {
            $enrichmentkey = new Opus_EnrichmentKey($name);
            $this->view->enrichmentkey = $enrichmentkey;
        }
        else {
            $this->_helper->redirector('index');
        }
    }

    /**
     * Shows edit form for enrichmentkey.
     */
    public function editAction() {
        $this->view->title = $this->view->translate($this->view->title);
        $name = $this->getRequest()->getParam('name');

        if (!empty($name) && !in_array($name, Opus_EnrichmentKey::getAllReferenced())) {
            $form = new Admin_Form_Enrichmentkey($name);
            $actionUrl = $this->view->url(array('action' => 'update', 'name' => $name));
            $form->setAction($actionUrl);
            $this->view->form = $form;
        }
        else {
            $this->_helper->redirector('index');
        }
    }

    /**
     * Shows form for creating a new enrichmentkey.
     */
    public function newAction() {
        $this->view->title = $this->view->translate('admin_enrichmentkey_index');
        $form = new Admin_Form_Enrichmentkey();
        $actionUrl = $this->view->url(array('action' => 'create'));
        $form->setAction($actionUrl);
        $this->view->form = $form;
    }

    /**
     * Creates a new enrichmentkey.
     */
   public function createAction() {
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();

            if ($this->getRequest()->getPost('cancel')) {
                return $this->_redirectTo('index');
            }

            $form = new Admin_Form_Enrichmentkey();

            if ($form->isValid($postData)) {
                $name = $postData['name'];
                $this->_updateEnrichmentkey(null, $name);
            }
            else {
                $actionUrl = $this->view->url(array('action' => 'create'));
                $form->setAction($actionUrl);
                $this->view->form = $form;
                $this->view->title = 'admin_enrichmentkey_new';
                $this->_helper->viewRenderer->setRender('new');
                return $this->render('new');
            }
        }
        $this->_redirectTo('index');
    }

    /**
     * Updates an  enrichmentkey.
     */
    public function updateAction() {
        $name = $this->getRequest()->getParam('name');

        if (!empty($name)) {
            $postData = $this->getRequest()->getPost();
            $enrichmentkey = new Opus_EnrichmentKey($name);

            if (!isset($postData['name'])) {
                $postData['name'] = $enrichmentkey->getName();
            }

            $form = new Admin_Form_Enrichmentkey();

            if ($form->isValid($postData)) {
                $name = $postData['name'];
                $this->_updateEnrichmentkey($enrichmentkey, $name);
            }
            else {
                $actionUrl = $this->view->url(array('action' => 'update', 'name' => $name));
                $form->setAction($actionUrl);
                $this->view->form = $form;
                $this->view->title = 'admin_enrichmentkey_edit';
                $this->_helper->viewRenderer->setRender('edit');
                return $this->render('edit');
            }
        }

        $this->_helper->redirector('index');
    }


    /**
     * Deletes an enrichmentkey from the database.
     */
    public function deleteAction() {
        $name = $this->getRequest()->getParam('name');

        if (!empty($name)) {
            
            if (in_array($name, Opus_EnrichmentKey::getAllReferenced())) {
                // TODO deliver message to user
            }
            elseif (!in_array($name, Opus_EnrichmentKey::getAll())) {
                // TODO deliver message to user
            }
            else {
                $enrichmentkey = new Opus_EnrichmentKey($name);
                $enrichmentkey->delete();
            }
            
         }

        $this->_helper->redirector('index');
    }


    protected function _updateEnrichmentkey($id, $name) {
        if (!empty($id)) {
            $enrichmentkey = new Opus_EnrichmentKey($id);
        }
        else {
            $enrichmentkey = new Opus_EnrichmentKey();
        }

        if (!empty($name)) {
            $enrichmentkey->setName($name);
            $enrichmentkey->store();
        }
       
    }


}
