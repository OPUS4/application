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
 * @package     Module_Home
 * @author      Ralf Claussnitzer (ralf.claussnitzer@slub-dresden.de)
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Home_IndexController extends Controller_Action {

    /**
     * Redirector - defined for code completion
     *
     * @var Zend_Controller_Action_Helper_Redirector
     */
    protected $_redirector = null;

    /**
     * Do some initialization on startup of every action.
     *
     * @return void
     */
    public function init() {
        parent::init();
        $this->_redirector = $this->_helper->getHelper('Redirector');
    }

    /**
     * The home module is the place for all custom static pages.  This function
     * catches all action calls, thus making a new page available via
     * http://.../home/index/page by simply placing it in
     * modules/home/views/scripts/index/page.phtml
     *
     * @param  string $action     The name of the action that was called.
     * @param  array  $parameters The parameters passed to the action.
     * @return void
     */
    public function __call($action, $parameters) {
        $logger = Zend_Registry::get('Zend_Log');
        if (!'Action' == substr($action, -6)) {
            $logger->info(__METHOD__ . ' undefined method: ' . $action);
            parent::__call($action, $parameters);
        }
        // it should be checked if the requested static page exists at all, as
        // otherwise this controller will not throw exceptions of type NO_ACTION
        $actionName = $this->getRequest()->getActionName();
        $sanitizedActionName = str_replace(array('\\', '/'), '', $actionName);
        $logger->debug(__METHOD__ . ' requested action: ' . $actionName);
        $logger->debug(__METHOD__ . ' sanitized action: ' . $sanitizedActionName);
        $scriptPaths = $this->view->getScriptPaths();
        $staticFile = $scriptPaths[0] . '/index/' .$sanitizedActionName . '.phtml';
        if (!is_readable($staticFile)) {
            $logger->info(__METHOD__ . ' requested file ' . $actionName . '.phtml is not readable');
            parent::__call($action, $parameters);
        }
    }

    /**
     * Switches the language for Zend_Translate and redirects back.
     *
     * @return void
     */
    public function languageAction() {
        $module = null;
        $controller = null;
        $action = null;
        $language = null;
        $params = array();

        foreach ($this->getRequest()->getParams() as $param => $value) {
            switch ($param) {
                case 'rmodule':
                    $module = $value;
                    break;
                case 'rcontroller':
                    $controller = $value;
                    break;
                case 'raction':
                    $action = $value;
                    break;
                case 'rrmodule':
                    $params['rmodule'] = $value;
                    break;
                case 'rrcontroller':
                    $params['rcontroller'] = $value;
                    break;
                case 'rraction':
                    $params['raction'] = $value;
                    break;
                case 'language':
                    $language = $value;
                    break;
                default:
                    $params[$param] = $value;
            }
        }

        if (!is_null($language) && Zend_Registry::get('Zend_Translate')->isAvailable($language)) {
            $sessiondata = new Zend_Session_Namespace();
            $sessiondata->language = $language;
        }
        $this->_redirectTo($action, '', $controller, $module, $params);
    }

    public function indexAction() {
        $this->_helper->mainMenu('home');
    }


    public function helpAction() {
        $config = Zend_Registry::get('Zend_Config');
        if (isset($config->help->separate)) {
            $this->view->separate = (boolean) $config->help->separate;
        }
        else {
            $this->view->separate = false;
        }

        if ($this->view->separate) {
            $content = $this->getRequest()->getParam('content');
            if (!is_null($content)) {
                if ($content === 'contact') {
                    $this->_redirectToAndExit('contact');
                }
                if ($content === 'imprint') {
                    $this->_redirectToAndExit('imprint');
                }

                $translation = $this->view->translate('help_content_' . $content);
                if (file_exists($this->view->getScriptPath('') . $translation)) {
                    $this->view->contenttitle = 'help_title_' . $content;
                    $this->view->content = file_get_contents($this->view->getScriptPath('') . $translation);
                }
                elseif ($translation !== 'help_content_' . $content) {
                    $this->view->contenttitle = 'help_title_' . $content;
                    $this->view->content = $translation;
                }
            }            
        }

        $this->_helper->mainMenu('help');
    }

    /**
     * only for testing purposes to display a warning via Zend's FlashMessenger
     */
    public function failureAction() {
        $this->_redirectTo('index', array('failure' => 'This is a warning.'));
    }

    /**
     * only for testing purposes to display a notice via Zend's FlashMessenger
     */
    public function noticeAction() {
        $this->_redirectTo('index', array('notice' => 'This is a notice.'));
    }
}