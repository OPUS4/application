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
 */

/**
 * @category    Application
 * @package     Controller
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @author      Felix Ostrowski <ostrowski@hbz-nrw.de>
 * @author      Sascha Szott <szott@zib.de>
 * @author      Jens schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2009-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Application_Controller_Action extends Application_Controller_ModuleAccess {

    /**
     * Holds the Redirector Helper.
     *
     * @var Zend_Controller_Action_Helper_Redirector
     */
    private $_redirector = null;

    /**
     * Holds the FlashMessenger Helper.
     *
     * @var Zend_Controller_Action_Helper_Messenger
     */
    private $_flashMessenger = null;

    /**
     * Helper fuer Breadcrumbs.
     * @var null
     */
    protected $_breadcrumbs = null;

    /**
     * Do some initialization on startup of every action
     *
     * @return void
     */
    public function init() {
        parent::init();
        $this->view->title = $this->_request->getModuleName() . '_' . $this->_request->getParam('controller') . '_'
            . $this->_request->getParam('action');
        $this->_redirector = $this->_helper->getHelper('redirector');
        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $this->view->flashMessenger = $this->_flashMessenger;
        $this->_breadcrumbs = $this->_helper->getHelper('breadcrumbs');
    }

    /**
     * Forward request to a different action.
     *
     * Sets the 'action' parameter so title key is correct.
     * @return void
     */
    protected function _forwardToAction($action) {
        $this->_request->setParam('action', $action);
        $this->_forward($action);
    }

    /**
     * Method called when access to module has been denied.
     */
    public function moduleAccessDeniedAction() {
        // we are not allowed to access this module -- but why?
        $identity = Zend_Auth::getInstance()->getIdentity();

        $errorcode = 'no_identity_error';
        if (!empty($identity)) {
            $errorcode = 'wrong_identity_error';
        }

        // Forward to module auth
        $this->_flashMessenger->addMessage(array('level' => 'failure', 'message' => $errorcode));

        $returnParams = $this->_helper->returnParams();

        $this->_redirector->gotoSimple('index', 'auth', 'default', $returnParams);
    }

    /**
     * Gibt das Formular aus wenn kein ViewScript vorhanden ist.
     *
     * Durch diese Funktion können die ganzen View Scripte, die nur ein Formular ausgeben eingespart werden. Der
     * Controller ruft einfach diese Funktion auf, wenn ein Formular ausgegeben werde sollte. Wenn doch ein View
     * Skript für die Action existiert, dann wird das Formular in der View Variable 'form' gespeichert und kann
     * im View Script verwendet werden.
     *
     * @param $form
     */
    protected function renderForm($form) {
        if ($this->isViewScriptPresent() === false) {
            $this->_helper->viewRenderer->setNoRender(true);
            echo $form;
        }
        else {
            $this->view->form = $form;
        }
    }

    /**
     * Prueft, ob fuer die Action ein View Script existiert.
     * @return bool
     */
    protected function isViewScriptPresent() {
        return (!$this->view->getScriptPath($this->_helper->viewRenderer->getViewScript())) ? false : true;
    }

}
