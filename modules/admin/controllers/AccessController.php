<?php
/*
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
 * @author      Julian Heise <heise@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id: $
 */

/**
 * Controller for managing role access to documents and modules.
 *
 * TODO implement assigning documents to roles
 */
class Admin_AccessController extends Controller_Action {

    public function listroleAction() {
        $id = $this->getRequest()->getParam('docid');
        $roles = Opus_UserRole::getAll();
        $this->view->docId = $id;
        $this->view->roles = $roles;
        $this->view->checkedRoles = $this->getCheckedRoles($id, $roles);
    }

    private function getCheckedRoles($id, $roles) {
        $items = array();
        foreach($roles as $role) {
            $docs = $role->listAccessDocuments();

            if(in_array($id, $docs)) {
                array_push($items, $role->getId());
            }
        }
        return $items;
    }

    public function listmoduleAction() {

        $id = $this->getRequest()->getParam('roleid');
        if($id == null)
            throw new Exception('Role ID missing');

        $role = new Opus_UserRole($id);
        $roleModules = $role->listAccessModules();

        if ($role->getName() !== 'guest') {
            $guest = Opus_UserRole::fetchByName('guest');
            $guestModules = $guest->listAccessModules();
            $this->view->guestModules = $guestModules;
        }

        $moduleDirectory = dirname($this->getFrontController()->getModuleDirectory());
        $modulesModel = new Admin_Model_Modules($moduleDirectory);

        $this->view->loginNames = $role->getAllAccountNames();
        $this->view->roleId = $role->getId();
        $this->view->roleName = $role->getName();
        $this->view->modules = $roleModules;
        $this->view->allModules = $modulesModel->getAll();
    }

    public function storeAction() {
        $save = $this->getRequest()->getParam('save_button');
        $id = $this->getRequest()->getParam('roleid');
        $docId = $this->getRequest()->getParam('docid');
        if (!empty($id)) {
            $accessMode = $this->getRequest()->getParam('access_mode');

            $this->storeModules($this->getRequest());

            $this->view->redirect = array('module'=>'admin','controller'=>'role','action'=>'show','id'=>$id);
        }
        elseif (!empty($docId)) {
            $this->storeRoles($this->getRequest());

            $this->view->redirect = array('module'=>'admin','controller'=>'document','action'=>'index','id'=>$docId);
        }

        if($save != null) {
            $this->view->submit = 'access_submit_save';
            $this->view->message = 'access_save_message';
        } else {
            $this->view->submit = 'access_submit_cancel';
            $this->view->message = 'access_cancel_message';
        }
    }

    private function storeModules($request) {
        $id = $request->getParam('roleid');
        $role = new Opus_UserRole($id);
        $roleModules = $role->listAccessModules();

        foreach($roleModules as $module) {
            if($request->getParam('set_'.$module, 'NULL') === 'NULL') {
                $role->removeAccessModule($module);
            }
        }

        $params = $request->getParams();
        foreach($params as $name=>$value) {
            if($this->string_begins_with($name, 'set_')) {
                $module = explode("_", $name);
                $module = $module[1];
                $role->appendAccessModule($module);
            }
        }
        $role->store();
    }

    /**
     *
     * @param <type> $request
     *
     * TODO Is it a problem if document is append twice?
     */
    private function storeRoles($request) {
        $docId = $request->getParam('docid');

        $roles = Opus_UserRole::getAll();

        foreach ($roles as $role) {
            $roleName = $role->getName();
            $checked = $request->getParam($roleName);
            if ($checked) {
                $role->appendAccessDocument($docId);
                $role->store();
            }
            else {
                $role->removeAccessDocument($docId);
                $role->store();
            }
        }
    }

    /**
     * Checks whether a given string has the supplied prefix
     * @param $string
     * @param $prefix
     * @return boolean
     */
    private function string_begins_with($string, $prefix) {
        return (strncmp($string, $prefix, strlen($prefix)) == 0);
    }

}
