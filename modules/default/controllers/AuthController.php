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
 */
class AuthController extends Controller_Action {

    /**
     * Default URL to goto after successful login. Maybe overwritten by findRemoteParameters().
     *
     * @var array
     */
    protected $_login_url = array('action' => 'index', 'controller' => 'index', 'module' => 'admin', 'params' => array());
    /**
     * Default URL to goto after successful logout. Maybe overwritten by findRemoteParameters().
     *
     * @var array
     */
    protected $_logout_url = array('action' => 'index', 'controller' => 'index', 'module' => 'default', 'params' => array());

    /**
     * Index action shows login form or logout link respectively.
     *
     * @return void
     */
    public function indexAction() {
        $identity = Zend_Auth::getInstance()->getIdentity();
        if (empty($identity) === true) {
            return $this->loginAction();
        }

        $this->view->logout_url = $this->view->url(array('action' => 'logout'));
        $this->view->identity = htmlspecialchars($identity);
    }

    /**
     * Login action performs login attempt with login form data. After a successful login
     * it redirects to the page configured by $_login_url.
     *
     * @return void
     */
    public function loginAction() {

        // Initialize form.
        $form = $this->getLoginForm();

        // check for return module, controller, action and parameteres, overwrite $_login_url.
        $rparams = $this->findReturnParameters();

        if ($this->getRequest()->isPost() !== true) {
            // Do not forget return parameters.
            $url = $this->view->url(array_merge(array('action' => 'login', 'controller' => 'auth', 'module' => 'default'), $rparams));
            $form->setAction($url);

            $this->view->form = $form;
            return $this->render('login');
        }

        // Credentials coming in via POST operation.
        // Get a login form instance for validation.
        // Get request data.
        $data = $this->_request->getPost();

        if ($form->isValid($data) !== true) {
            // Put authentication failure message to the view.
            $this->view->auth_failed_msg = 'Invalid credentials';

            // Populate the form again to trigger validator decorators.
            $form->populate($data);

            $this->view->form = $form;
            return $this->render('login');
        }

        // Form data is valid (including the hash field)
        $auth = new Opus_Security_AuthAdapter();

        // Overwrite auth adapter if config-key is set.
        $config = Zend_Registry::get('Zend_Config');
        if (isset($config, $config->authenticationModule) and ($config->authenticationModule === 'Ldap')) {
            $auth = new Opus_Security_AuthAdapter_Ldap();
        }

        // Perfom authentication attempt
        $auth->setCredentials($data['login'], $data['password']);
        $auth_result = $auth->authenticate();

        if ($auth_result->isValid() !== true) {
            // Put authentication failure message to the view.
            $message = $auth_result->getMessages();
            $this->view->auth_failed_msg = $message[0];

            // Populate the form again to trigger validator decorators.
            $form->populate($data);

            $this->view->form = $form;
            return $this->render('login');
        }

        // Persistent the successful authenticated identity.
        Zend_Auth::getInstance()->getStorage()->write($data['login']);

        // Redirect to post login url.
        $action = $this->_login_url['action'];
        $controller = $this->_login_url['controller'];
        $module = $this->_login_url['module'];
        $params = $this->_login_url['params'];
        $this->_helper->_redirector($action, $controller, $module, $params);

    }

    /**
     * Logout action performs logout and redirects to the page configured by $_logout_url.
     *
     * @return void
     */
    public function logoutAction() {
        Zend_Auth::getInstance()->clearIdentity();
        // check for return module, controller, action and parameters. Overwrite $_logout_url.
        $this->findReturnParameters();
        $action = $this->_logout_url['action'];
        $controller = $this->_logout_url['controller'];
        $module = $this->_logout_url['module'];
        $params = $this->_logout_url['params'];
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
        $login->addValidator(new Zend_Validate_Regex('/^[A-Za-z0-9@._-]+$/'))
                ->setRequired()
                ->setLabel('auth_field_login');

        // Password element.
        $password = new Zend_Form_Element_Password('password');
        $password->addValidator(new Zend_Validate_Alnum())
                ->setRequired()
                ->setLabel('auth_field_password');

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
        $params = $this->getRequest()->getUserParams();
        $rparams = array();
        $rmodule = null;
        $rcontroller = null;
        $raction = null;
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

        if (is_null($rmodule) || is_null($rcontroller) || is_null($raction)) {
            return array();
        }

        // store return address and parameters
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
}
