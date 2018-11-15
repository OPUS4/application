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
 * @author      Julian Heise <heise@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Controller for managing permissions for roles including module access.
 *
 *
 */
class Admin_AccessController extends Application_Controller_Action
{

    /**
     *
     */
    public function listroleAction()
    {
        $id = $this->getRequest()->getParam('docid');
        $roles = Opus_UserRole::getAll();
        $this->view->docId = $id;
        $this->view->roles = $roles;
        $this->view->checkedRoles = $this->getCheckedRoles($id, $roles);
    }

    /**
     * Returns list of selected roles.
     *
     * @param type $id
     * @param type $roles
     * @return array
     */
    private function getCheckedRoles($id, $roles)
    {
        $items = [];
        foreach ($roles as $role) {
            $docs = $role->listAccessDocuments();

            if (in_array($id, $docs)) {
                array_push($items, $role->getId());
            }
        }
        return $items;
    }

    /**
     * Action for showing list of modules and permissions.
     *
     * @throws Exception
     */
    public function listmoduleAction()
    {
        $security = new Admin_Model_AccessManager();

        $id = $this->getRequest()->getParam('roleid');

        if ($id == null) {
            throw new Exception('Role ID missing');
        }

        $role = new Opus_UserRole($id);
        $roleModules = $role->listAccessModules();

        if ($role->getName() !== 'guest') {
            $guest = Opus_UserRole::fetchByName('guest');
            $guestModules = $guest->listAccessModules();
            // Role 'guest' has always access to 'default' module
            if (!in_array('default', $guestModules)) {
                $guestModules[] = 'default';
            }
            $this->view->guestModules = $guestModules;
        }
        else {
            // Role 'guest' has alreays access to 'default' module
            if (!in_array('default', $roleModules)) {
                $roleModules[] = 'default';
            }
        }

        $transitions = $security->getWorkflowResources();

        $this->view->loginNames = $role->getAllAccountNames();

        $this->view->roleId = $role->getId();
        $this->view->roleName = $role->getName();
        $this->view->modules = $roleModules;

        $this->view->allModules = array_keys(Application_Modules::getInstance()->getModules());
        $this->view->allResources = $security->getAllResources();
        $this->view->allWorkflow = $transitions;
    }

    /**
     * Action for saving selected permissions for role.
     */
    public function storeAction()
    {
        $save = $this->getRequest()->getParam('save_button');
        $id = $this->getRequest()->getParam('roleid');
        $docId = $this->getRequest()->getParam('docid');

        if (!empty($id)) {
            $accessMode = $this->getRequest()->getParam('access_mode');

            $this->storeModules($this->getRequest());

            $this->view->redirect = ['module'=>'admin','controller'=>'role','action'=>'show','id'=>$id];
        } elseif (!empty($docId)) {
            $this->storeRoles($this->getRequest());

            $this->view->redirect = ['module'=>'admin','controller'=>'document','action'=>'index','id'=>$docId];
        }

        if ($save != null) {
            $this->view->submit = 'access_submit_save';
            $this->view->message = 'access_save_message';
        } else {
            $this->view->submit = 'access_submit_cancel';
            $this->view->message = 'access_cancel_message';
        }
    }

    /**
     * Stores selected permissions in database.
     *
     * @param type $request
     *
     * TODO secure against missing parameters
     */
    private function storeModules($request)
    {
        $id = $request->getParam('roleid');

        $role = new Opus_UserRole($id);
        $roleModules = $role->listAccessModules();

        foreach ($roleModules as $module) {
            if ($request->getParam('set_'.$module, 'NULL') === 'NULL') {
                $role->removeAccessModule($module);
            }
        }

        $params = $request->getParams();

        foreach ($params as $name=>$value) {
            $startsWith = 'set_';
            if (substr($name, 0, strlen($startsWith)) === $startsWith) {
                $module = explode("_", $name, 2);
                $module = $module[1];
                $role->appendAccessModule($module);
            }
        }

        $role->store();
    }

    /**
     * Stores roles for document.
     *
     * @param <type> $request
     *
     * TODO Is it a problem if document is append twice?
     */
    private function storeRoles($request)
    {
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
}

