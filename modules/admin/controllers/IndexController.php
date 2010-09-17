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
 * @author      Oliver Marahrens (o.marahrens@tu-harburg.de)
 * @author      Jens Schwidder (schwidder@zib.de)
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Main entry point for this module.
 *
 * @category    Application
 * @package     Module_Admin
 *
 * TODO could the array be generated once for each language?
 * TODO could this be made more readable (dynamic list, methods, XML)
 */
class Admin_IndexController extends Zend_Controller_Action {

    /**
     * Creates array with items for administration menu.
     *
     * @return void
     */
    public function indexAction() {
        $this->view->title = $this->view->translate('admin_index_title');

        $config = Zend_Registry::get('Zend_Config');

        $personDisabled = false;

        if (isset($config->admin->persons->disabled)) {
            $personDisabled = $config->admin->persons->disabled;
        }

        // Create an array with all possible tasks for an admin.
        // Sort it depending on the language.
        $adminTasks = array();

        $adminTasks[$this->view->translate('admin_title_licence')] = $this->view->url(array('controller'=>'licence', 'action'=>'index'), null, false);
        $adminTasks[$this->view->translate('admin_title_organizational_units_show')] = $this->view->url(array('controller'=>'collection', 'action' => 'show', 'role' => 1), null, false);
        $adminTasks[$this->view->translate('admin_title_organizational_units_edit')] = $this->view->url(array('controller'=>'collection', 'action' => 'edit', 'role' => 1), null, false);
        $adminTasks[$this->view->translate('admin_title_collections')] = $this->view->url(array('controller'=>'collection', 'action'=>'index'), null, false);
        
        if (!$personDisabled) {
            $adminTasks[$this->view->translate('admin_title_person')] = $this->view->url(array('controller'=>'person', 'action'=>'index'), null, false);
        }
        
        $adminTasks[$this->view->translate('admin_title_documents')] = $this->view->url(array('module' => 'admin', 'controller' => 'documents', 'action' => 'index'), 'default', true);
        $adminTasks[$this->view->translate('admin_title_languages')] = $this->view->url(array('module' => 'admin', 'controller' => 'language', 'action' => 'index'), 'default', true);
        $adminTasks[$this->view->translate('admin_title_statistic')] = $this->view->url(array('module' => 'admin', 'controller' => 'statistic', 'action' => 'index'), 'default', true);
        $adminTasks[$this->view->translate('pkm_list_keys')] = $this->view->url(array('module'=>'pkm', "controller"=>"index", "action"=>"listkeys"), null, false);
        $adminTasks[$this->view->translate('admin_title_oailink')] = $this->view->url(array('module' => 'admin', 'controller' => 'oailink', 'action' => 'index'), 'default', true);
        $adminTasks[$this->view->translate('admin_title_security')] = $this->view->url(array('module' => 'admin', 'controller' => 'security', 'action' => 'index'), 'default', true);

        ksort($adminTasks);

        $this->view->adminTasks = $adminTasks;
    }

}