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
 * @package     Controller
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @copyright   Copyright (c) 2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Module-access-checking controller for Opus Applications.
 *
 * @category    Application
 * @package     Controller
 */
class Controller_ModuleAccess extends Zend_Controller_Action {

    /**
     * Use pre-dispatch to check user access rights *before* action is called.
     */
    public function preDispatch() {
        parent::preDispatch();
        $this->checkAccessModulePermissions();
    }

    /**
     * Checks if the user is allowed to access the given module.
     *
     * @return void
     */
    protected function checkAccessModulePermissions() {
        $logger = Zend_Registry::get('Zend_Log');
        $module = $this->_request->getModuleName();

        $logger->debug("starting authorization check for module '$module'");

        // Check, controller-specific constraints...
        if (true !== $this->customAccessCheck()) {
            $logger->debug("FAILED custom authorization check for module '$module'");
            return $this->rejectRequest();
        }

        // Check, if the user has the right privileges...
        if (true !== Opus_Security_Realm::getInstance()->checkModule($module)) {
            $logger->debug("FAILED authorization check for module '$module'");
            return $this->rejectRequest();
        }

        $logger->debug("authorization check for module '$module' successful");
        return;
    }

    /**
     * Method stub to be overridden by controllers.  Enables checks for custom
     * properties.
     *
     * @return boolean
     */
    protected function customAccessCheck() {
        return true;
    }

    /**
     * Method called when access to module has been denied.
     */
    protected function rejectRequest() {
        throw new Application_Exception();
    }

}
