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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id: $
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
            if(in_array($id, $docs))
                array_push($items, $role->getId());
        }
    }

    public function listmoduleAction() {

        $id = $this->getRequest()->getParam('roleid');
        if($id == null)
            throw new Exception('Role ID missing');

        $this->view->roleId = $id;
        $role = new Opus_UserRole($id);
        $roleModules = $role->listAccessModules();
        $this->view->modules = $roleModules;
        $this->view->allModules = $this->getAllModules();
    }

    public function storeAction() {
        $save = $this->getRequest()->getParam('save_button');
        $id = $this->getRequest()->getParam('roleid');
        $accessMode = $this->getRequest()->getParam('access_mode');

        $this->storeModules($this->getRequest());
        
        $this->view->redirect = array('module'=>'admin','controller'=>'role','action'=>'show','roleid'=>$id);
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

        foreach($roleModules as $module=>$controllers) {
            foreach($controllers as $controller) {
                if($request->getParam('set_'.$module.':'.$controller, 'NULL') === 'NULL') {
                    $role->removeAccessModule($module, $controller);
                }
            }
        }

        $params = $request->getParams();
        foreach($params as $name=>$value) {
            if($this->string_begins_with($name, 'set_')) {
                $parts = explode(":", $name);
                $module = explode("_", $parts[0]);
                $module = $module[1];
                $role->appendAccessModule($module, $parts[1]);
            }
        }
        $role->store();
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

    /**
     * Iterates over Zend controller directories and extracts controller and
     * module names
     * @return array
     */
    private function getAllModules() {
        $deadPaths = Array( ".", "..", ".svn");
        $module_dir = substr(str_replace("\\","/",$this->getFrontController()->getModuleDirectory()),0,strrpos(str_replace("\\","/",$this->getFrontController()->getModuleDirectory()),'/'));
        $temp = array_diff( scandir( $module_dir), $deadPaths);
        $modules = array();
        $controller_directorys = array();
        $controllers = array();
        $structured = array();
        foreach ($temp as $module) {
            if (is_dir($module_dir . "/" . $module)) {
                $structured[$module] = array();
                $directory = str_replace("\\","/",$this->getFrontController()->getControllerDirectory($module));
                foreach (scandir($directory) as $dirstructure) {
                    if (is_file($directory  . "/" . $dirstructure)) {
                        if (strstr($dirstructure,"Controller.php") != false) {
                            $replaced = str_replace("Controller.php", "", $dirstructure);
                            array_push($structured[$module], strtolower($replaced));
                        }
                    }
                }
            }
        }
        return $structured;
    }
}
?>
