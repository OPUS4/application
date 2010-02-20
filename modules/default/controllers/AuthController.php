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
 * @package     Module_Default
 * @author      Ralf Claussnitzer (ralf.claussnitzer@slub-dresden.de)
 * @author      Pascal-Nicolas Becker <becker@zib.de>
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Provides actions for basic authenticating login and logout.
 *
 * @category    Application
 * @package     Module_Default
 */
class AuthController extends Zend_Controller_Action {

    /**
     * Default URL to goto after successful login. Maybe overwritten by findRemoteParameters().
     *
     * @var array
     */
    protected $_login_url = array('action' => 'index', 'controller' => 'index', 'module' => 'default', 'params' => array());
    
    /**
     * Default URL to goto after successful logout. Maybe overwritten by findRemoteParameters().
     *
     * @var array
     */
    protected $_logout_url = array('action' => 'index', 'controller' => 'index', 'module' => 'default', 'params' => array());

    /**
     * Index action shows login form or logout link respectivly.
     *
     * @return void
     */
    public function indexAction() {
        // check for return module, controller, action and parameteres
        $rparams = $this->findReturnParameters();

        // Retrieve the current configured identity.
        $identity = Zend_Auth::getInstance()->getIdentity();
        if (empty($identity) === true) {
            // Nobody is logged in, so present the login form.
            // Do not forget return parameters.
            $url = $this->view->url(array_merge(array('action' => 'login', 'controller' => 'auth', 'module' => 'default'), $rparams));
            $this->view->form = $this->getLoginForm();
            $this->view->form->setAction($url);
        } else {
            // Somebody is logged in, so present the logout link.
            // Do not forget return parameters.
            $url = $this->view->url(array_merge(array('action' => 'logout', 'controller' => 'auth', 'module' => 'default'), $rparams));
            $text = 'Logout ' . $identity . '.';
            $this->view->form = '<a href="' . $url . '">' . $text . '</a>';
        }
    }

    /**
     * Login action performs login attempt with login form data. After a successful login
     * it redirects to the page configured by $_login_url.
     *
     * @return void
     */
    public function loginAction() {
        // check for return module, controller, action and parameteres, overwrite $_login_url.
        $rparams = $this->findReturnParameters();

        if ($this->getRequest()->isPost() === true) {
            // Credentials coming in via POST operation.
            
            // Get a login form instance for validation.
            $form = $this->getLoginForm();
            
            // Get request data.
            $data = $this->_request->getPost();
            
            if ($form->isValid($data) === true) {
                // Form data is valid (including the hash field)
                
                // Perfom authentication attempt
                $auth = new Opus_Security_AuthAdapter();
                $auth->setCredentials($data['login'], $data['password']);
                $auth_result = $auth->authenticate();

                if ($auth_result->isValid() === true) {
                    // Persistent the successful authenticated identity.
                    Zend_Auth::getInstance()->getStorage()->write($data['login']);
                    
                    // Redirect to post login url.
                    $action = $this->_login_url['action'];
                    $controller = $this->_login_url['controller'];
                    $module = $this->_login_url['module'];
                    $params = $this->_login_url['params'];
                    $this->_helper->_redirector($action, $controller, $module, $params);
                } else {
                    // Put authentication failure message to the view.
                    $this->view->auth_failed_msg = $auth_result->getMessages();
                    $this->view->auth_failed_msg = $this->view->auth_failed_msg[0];
                    // Populate the form again to trigger validator decorators.
                    $form->populate($data);
                    $this->view->form = $form;
                }
            } else {
                // Put authentication failure message to the view.
                $this->view->auth_failed_msg = 'Invalid credentials';
                // Populate the form again to trigger validator decorators.
                $form->populate($data);
                $this->view->form = $form;
            }
            // Render index script to show the login form again.
            $this->render('index');
        } else {
            // Redirect to index on GET operation.
            $this->_helper->_redirector('index', 'auth', 'default', $rparams);
        }
    }

    /**
     * Logout action performs logout and redirects to the page configured by $_logout_url.
     *
     * @return void
     */
    public function logoutAction() {
        Zend_Auth::getInstance()->clearIdentity();
        // check for return module, controller, action and parameteres. Overwrite $_logout_url.
        $rparams = $this->findReturnParameters();
        $action = $this->_logout_url['action'];
        $controller = $this->_logout_url['controller'];
        $module = $this->_logout_url['module'];
        $params = $this->_login_url['params'];
        $this->_helper->_redirector($action, $controller, $module, $params);
    }


    /**
     * Assembles and returns a login form.
     *
     * @return unknown
     */
    protected function getLoginForm() {
        $form = new Zend_Form();

        // Add hash element to detect counterfeit formular data via validation.
        $hash = new Zend_Form_Element_Hash('hash');

        // Login name element.
        $login = new Zend_Form_Element_Text('login');
        $login->addValidator(new Zend_Validate_Alnum())
            ->setRequired()
            ->setLabel('Login');

        // Password element.
        $password = new Zend_Form_Element_Password('password');
        $password->addValidator(new Zend_Validate_Alnum())
            ->setRequired()
            ->setLabel('Password');

        // Submit button.
        $submit = new Zend_Form_Element_Submit('SubmitCredentials');
        $submit->setLabel('Login');

        $form->setMethod('POST');
        $form->addElements(array($hash, $login, $password, $submit));

        return $form;
    }

    /**
     * Look for parameters rmodule, rcontroller, raction and all other parameters.
     * Sets this->_login_url and this->logout_url.
     * Ignores following parameters: module, controller, action, hash, login, password and SubmitCredentials.
     *
     * returns mixed Associative array containing parameters that should be added to urls referencing this controller.
     */
    protected function findReturnParameters() {
        $params = $this->getRequest()->getParams();
        $rparams = array();
        foreach($params as $key=>$value) {
            switch ($key) {
            // ignore default parameters
            case 'module' :
                break;
            case 'controller' :
                break;
            case 'action' :
                break;
            // do not forward login credentials
            case 'hash' :
                break;
            case 'login' :
                break;
            case 'password' :
                break;
            case 'SubmitCredentials' :
                break;
            // find return module, controller, action and parameters
            case 'rmodule' :
                $rmodule = $value;
                break;
            case 'rcontroller' :
                $rcontroller = $value;
                break;
            case 'raction' :
                $raction = $value;
                break;
            default :
                // parameter of old url
                $rparams[$key] = $value;
                break;
            }
        }

        // store return address and parameters
        if (true === isset($rmodule) && false === empty($rmodule)
                && true === isset($rcontroller) && false === empty($rcontroller)
                && true === isset($raction) && false === empty($raction)) {
            if (false === isset($rparams) || false === is_array($rparams)) {
                $rparams = array();
            }
            $this->_login_url = array(
                'action' => $raction,
                'controller' => $rcontroller,
                'module' => $rmodule,
                'params' => $rparams,
            );
            $this->_logout_url = array(
                'action' => $raction,
                'controller' => $rcontroller,
                'module' => $rmodule,
                'params' => $rparams,
            );
        return array_merge(array('rmodule' => $rmodule, 'rcontroller' => $rcontroller, 'raction' => $raction), $rparams);
        }
        return array();

    }

}
