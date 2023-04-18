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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Security\AuthAdapter;
use Opus\Security\Ldap\LdapAuthAdapter;

/**
 * Provides actions for basic authenticating login and logout.
 */
class AuthController extends Application_Controller_Action
{
    public function init()
    {
        parent::init();

        $this->view->robots = 'noindex, nofollow';
    }

    /**
     * Always allow access to this controller; Override check in parent method.
     */
    protected function checkAccessModulePermissions()
    {
    }

    /**
     * Default URL to goto after successful login. Maybe overwritten by findRemoteParameters().
     *
     * @var array
     */
    protected $loginUrl = ['action' => 'index', 'controller' => 'index', 'module' => 'home', 'params' => []];
    /**
     * Default URL to goto after successful logout. Maybe overwritten by findRemoteParameters().
     *
     * @var array
     */
    protected $logoutUrl = [
        'action'     => 'index',
        'controller' => 'index',
        'module'     => 'default',
        'params'     => [],
    ];

    /**
     * Index action shows login form or logout link respectively.
     */
    public function indexAction()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
        if (empty($identity) === true) {
            $this->loginAction();
            return;
        }

        $this->view->logout_url = $this->view->url(['action' => 'logout']);
        $this->view->identity   = htmlspecialchars($identity);
    }

    /**
     * Login action performs login attempt with login form data. After a successful login
     * it redirects to the page configured by $_login_url.
     */
    public function loginAction()
    {
        // Redirect to start page if user is already logged in
        $identity = Zend_Auth::getInstance()->getIdentity();
        if (empty($identity) !== true) {
            $this->_helper->_redirector('index', 'index', 'home', []);
            return;
        }

        // Initialize form.
        $form = $this->getLoginForm();
        /** @var Zend_Log $logger */
        $logger = $this->getLogger();

        // check for return module, controller, action and parameteres, overwrite $_login_url.
        $rparams = $this->findReturnParameters();

        if ($this->getRequest()->isPost() !== true) {
            // Do not forget return parameters.
            $url = $this->view->url(
                array_merge(['action' => 'login', 'controller' => 'auth', 'module' => 'default'], $rparams)
            );
            $form->setAction($url);

            $this->view->form = $form;
            $this->render('login');
            return;
        }

        // Credentials coming in via POST operation.
        // Get a login form instance for validation.
        // Get request data.
        $data = $this->_request->getPost();

        if ($form->isValid($data) !== true) {
            // Put authentication failure message to the view.
            $this->view->auth_failed_msg = $this->view->translate('auth_error_invalid_credentials');

            // Populate the form again to trigger validator decorators.
            $form->populate($data);

            $this->view->form = $form;
            $this->render('login');
            return;
        }

        // Form data is valid (including the hash field)
        $auth = new AuthAdapter();

        // Overwrite auth adapter if config-key is set.
        $config = $this->getConfig();
        if (isset($config, $config->authenticationModule) && ($config->authenticationModule === 'Ldap')) {
            $auth = new LdapAuthAdapter();
        }

        // Perfom authentication attempt
        $login = strtolower($data['login']);

        $auth->setCredentials($login, $data['password']);
        $authResult = $auth->authenticate();

        if ($authResult->isValid() !== true) {
            // Put authentication failure message to the view.
            $message                     = $authResult->getMessages();
            $this->view->auth_failed_msg = $this->view->translate($message[0]);

            // Populate the form again to trigger validator decorators.
            $logger->notice("Failed login attempt of user '" . $login . "'.");
            $form->populate($data);

            $this->view->form = $form;
            $this->render('login');
            return;
        }

        // Persistent the successful authenticated identity.
        $logger->notice("Successful login attempt of user '" . $login . "'.");
        Zend_Auth::getInstance()->getStorage()->write(strtolower($login));

        // Redirect to post login url.
        $action     = $this->loginUrl['action'];
        $controller = $this->loginUrl['controller'];
        $module     = $this->loginUrl['module'];
        $params     = $this->loginUrl['params'];
        $this->_helper->_redirector($action, $controller, $module, $params);
    }

    /**
     * Logout action performs logout and redirects to home module.
     */
    public function logoutAction()
    {
        Zend_Auth::getInstance()->clearIdentity();
        $this->_helper->_redirector('index', 'index', 'home');
    }

    /**
     * Assembles and returns a login form.
     *
     * @return Zend_Form
     */
    protected function getLoginForm()
    {
        $form = new Zend_Form();

        // Add hash element to detect counterfeit formular data via validation.
        $hash = new Zend_Form_Element_Hash('hash');

        // Login name element.
        $login = new Zend_Form_Element_Text('login');
        $login->addValidator(new Zend_Validate_Regex('/^[A-Za-z0-9@._-]+$/'))
                ->setRequired()
                ->setLabel('auth_field_login');
        $login->addErrorMessages(
            [
                Zend_Validate_NotEmpty::IS_EMPTY => 'auth_error_no_username',
            ]
        );

        // Password element.
        $password = new Zend_Form_Element_Password('password');
        $password->setRequired()
                ->setLabel('auth_field_password');
        $password->addErrorMessages(
            [
                Zend_Validate_NotEmpty::IS_EMPTY => 'auth_error_no_password',
            ]
        );

        // Submit button.
        $submit = new Zend_Form_Element_Submit('SubmitCredentials');
        $submit->setLabel('Login');

        $form->setMethod('POST');
        $form->addElements([$hash, $login, $password, $submit]);

        return $form;
    }

    /**
     * Look for parameters rmodule, rcontroller, raction and all other parameters.
     * Sets this->_login_url and this->logout_url.
     * Ignores following parameters: module, controller, action, hash, login, password and SubmitCredentials.
     *
     * returns mixed Associative array containing parameters that should be added to urls referencing this controller.
     *
     * @return array
     */
    protected function findReturnParameters()
    {
        $params      = $this->getRequest()->getUserParams();
        $rparams     = [];
        $rmodule     = null;
        $rcontroller = null;
        $raction     = null;
        foreach ($params as $key => $value) {
            switch ($key) {
                // ignore default parameters
                case 'module':
                case 'controller':
                case 'action':
                // do not forward login credentials
                case 'hash':
                case 'login':
                case 'password':
                case 'SubmitCredentials':
                    break;
                // find return module, controller, action and parameters
                case 'rmodule':
                    $rmodule = $value;
                    break;
                case 'rcontroller':
                    $rcontroller = $value;
                    break;
                case 'raction':
                    $raction = $value;
                    break;
                default:
                    // parameter of old url
                    $rparams[$key] = $value;
                    break;
            }
        }

        if ($rmodule === null || $rcontroller === null || $raction === null) {
            return [];
        }

        // store return address and parameters
        $this->loginUrl  = [
            'action'     => $raction,
            'controller' => $rcontroller,
            'module'     => $rmodule,
            'params'     => $rparams,
        ];
        $this->logoutUrl = [
            'action'     => $raction,
            'controller' => $rcontroller,
            'module'     => $rmodule,
            'params'     => $rparams,
        ];
        return array_merge(
            ['rmodule' => $rmodule, 'rcontroller' => $rcontroller, 'raction' => $raction],
            $rparams
        );
    }
}
