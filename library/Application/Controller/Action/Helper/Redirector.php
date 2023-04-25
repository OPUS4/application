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
 * @copyright   Copyright (c) 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\LoggingTrait;

class Application_Controller_Action_Helper_Redirector extends Zend_Controller_Action_Helper_Redirector
{
    use LoggingTrait;

    /** @var Zend_Controller_Action_Helper_FlashMessenger */
    private $flashMessenger;

    public function init()
    {
        parent::init();

        $this->flashMessenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
    }

    /**
     * Redirects to an action / controller / module, sets a message for the redirect target view.
     *
     * @param  string      $action     The redirect target action
     * @param  null|string $message The message to be displayed
     * @param  null|string $controller The redirect target controller
     * @param  null|string $module The redirect target model
     * @param  array       $params     Parameters for the redirect target action
     */
    public function redirectTo(
        $action,
        $message = null,
        $controller = null,
        $module = null,
        $params = []
    ) {
        $this->performRedirect($action, $message, $controller, $module, $params);
    }

    /**
     * Performs a permanent (301) redirect.
     *
     * @param string      $action        The target action.
     * @param null|string $message The message to be displayed.
     * @param null|string $controller The target controller.
     * @param null|string $module The target module.
     * @param array       $params        Optional request parameters.
     */
    public function redirectToPermanent(
        $action,
        $message = null,
        $controller = null,
        $module = null,
        $params = []
    ) {
        $this->setCode(301);
        $this->performRedirect($action, $message, $controller, $module, $params);
    }

    /**
     * @param string      $action
     * @param null|string $message
     * @param null|string $controller
     * @param null|string $module
     * @param array       $params
     * @throws Application_Exception
     */
    public function redirectToPermanentAndExit(
        $action,
        $message = null,
        $controller = null,
        $module = null,
        $params = []
    ) {
        $this->setCode(301);
        $this->performRedirect($action, $message, $controller, $module, $params, true);
    }

    /**
     * @param string      $action
     * @param null|string $message
     * @param null|string $controller
     * @param null|string $module
     * @param array       $params
     * @throws Application_Exception
     */
    public function redirectToAndExit(
        $action,
        $message = null,
        $controller = null,
        $module = null,
        $params = []
    ) {
        $this->performRedirect($action, $message, $controller, $module, $params, true);
    }

    /**
     * Performs a redirect.
     *
     * There is a problem with the 'AndExit' functionality. A hard exit kills the unit testing process.
     * The testing framework calls setExit(false) in order to prevent that. However if the OPUS code
     * changes this again, the unit test process will be killed. The same is true if any 'andExit'
     * function is called explicitly because then getExit will not be called first. This information is
     * base on ZF 1.12.x sources, at least until 1.12.20.
     *
     * TODO because of problem described above 'AndExit' should never be used
     *
     * @param string      $action
     * @param string|null $message
     * @param string|null $controller
     * @param string|null $module
     * @param array       $params
     * @param bool        $exit
     * @throws Application_Exception
     */
    public function performRedirect(
        $action,
        $message = null,
        $controller = null,
        $module = null,
        $params = [],
        $exit = false
    ) {
        if ($message !== null) {
            if (is_array($message) && count($message) !== 0) {
                $keys = array_keys($message);
                $key  = $keys[0];
                if ($key === 'failure' || $key === 'notice') {
                    $this->flashMessenger->addMessage(['level' => $key, 'message' => $message[$key]]);
                } else {
                    $this->flashMessenger->addMessage(['level' => 'notice', 'message' => $message[$key]]);
                }
            } elseif (is_string($message) && $message !== '') {
                $this->flashMessenger->addMessage(['level' => 'notice', 'message' => $message]);
            }
        }
        $this->getLogger()->debug("redirect to module: $module controller: $controller action: $action");

        if (array_key_exists('anchor', $params)) {
            $anchor = '#' . $params['anchor'];
            unset($params['anchor']);

            $urlHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('url');

            $gotoUrl = $urlHelper->url(array_merge([
                'action'     => $action,
                'controller' => $controller,
                'module'     => $module,
            ], $params));

            $this->gotoUrl($gotoUrl . $anchor, ['prependBase' => false]);
        } else {
            $this->gotoSimple($action, $controller, $module, $params);
            $this->setExit($exit); // TODO does not do anything at this point
        }
    }
}
