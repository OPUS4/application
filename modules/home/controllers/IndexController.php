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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Main entry point for this module.
 *
 * @category    Application
 * @package     Module_Home
 */
class Home_IndexController extends Zend_Controller_Action {

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
    }

    /**
     * Switches the language for Zend_Translate and redirects back.
     *
     * @return void
     */
    public function languageAction() {
        $origin = $_SERVER['HTTP_REFERER'];
        $language = $this->_request->getParam('language');
        if (is_string('language') === false or Zend_Registry::get('Zend_Translate')->isAvailable($language) === false) {
            $this->_redirector->gotoUrl($origin);
        } else {
            $sessiondata = new Zend_Session_Namespace();
            $sessiondata->language = $language;
            $this->_redirector->gotoUrl($origin);
        }
    }

    /**
     * Show fulltext search form
     *
     * @return void
     */
    public function indexAction()
    {
        $searchForm = new Zend_Form;
        $searchForm->setAttrib('class', 'crud');
        $query = new Zend_Form_Element_Text('query');
        $query->addValidator('stringLength', false, array(3, 100))
            ->setRequired(true);

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('search_searchaction');

        // Add elements to form:
        $searchForm->addElements(array($query, $submit));

        $searchForm->setAction($this->view->url(array(
            'module' => 'search',
            'controller' => 'search',
            'action' => 'search')));
        $searchForm->setMethod('post');
        $this->view->searchForm = $searchForm;
    }

    public function aboutAction() {
         $config = Zend_Registry::get('Zend_Config');

		$module = $config->startmodule;
		if (empty($module) === true) {
			$module = 'home';
		}
		
		$this->view->startmodule = $module;
 
        if (array_key_exists('content', $this->_request->getParams()) === true) {
            $this->view->content = $this->_request->getParam("content");
        }
    }

    public function helpAction() {
        $config = Zend_Registry::get('Zend_Config');

		$module = $config->startmodule;
		if (empty($module) === true) {
			$module = 'home';
		}
		
		$this->view->startmodule = $module;
		
        if (array_key_exists('content', $this->_request->getParams()) === true) {
            $this->view->content = $this->_request->getParam("content");
        }
    }
}
