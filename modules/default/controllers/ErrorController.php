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
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @author      Michael Lang <lang@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * This controller is called on every error or exception.
 *
 * @package     Module_Default
 */
class ErrorController extends Application_Controller_Action
{

    /**
     * Always allow access to this controller; Override check in parent method.
     */
    protected function checkAccessModulePermissions()
    {
    }

    /**
     * Print error information appropriate to environment.
     *
     * @return void
     */
    public function errorAction()
    {
        $config = $this->getConfig();
        $logger = $this->getLogger();

        // log request URI if error occurs
        $uri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
        $logger->err("Request '$uri'");

        $errors = $this->_getParam('error_handler');

        if (isset($errors)) {
            $logger->err('ErrorController: error type = ' . $errors->type);
            $logger->err('ErrorController: exception = ' . $errors->exception);
        }

        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->title = 'error_page_not_found';
                $this->view->message = $this->view->translate('error_page_not_found');
                $this->view->errorMessage = $this->view->translate('error_msg_page_not_found');
                break;
            default:
                // application error
                $this->setResponseCode(500);
                if ($errors->exception instanceof Application_Exception) {
                    $code = $errors->exception->getHttpResponsecode();
                    if ($code != null) {
                        $this->setResponseCode($code);
                    }
                }
                $this->view->title = 'error_application';
                $this->view->message = $this->view->translate('error_application');
                $this->view->errorMessage = $this->view->translate($errors->exception->getMessage());
                break;
        }

        $this->view->exception = $errors->exception;

        $errorConfig = $config->errorController;
        if (! isset($errorConfig)) {
            $logger->warn('ErrorController not configured.');
            return;
        }

        $this->view->showException = isset($errorConfig->showException)
            && filter_var($errorConfig->showException, FILTER_VALIDATE_BOOLEAN);
        if (isset($errorConfig->showRequest) && filter_var($errorConfig->showRequest, FILTER_VALIDATE_BOOLEAN)) {
            $this->view->errorRequest = $errors->request;
        }

        if (! isset($errorConfig->mailTo)) {
            $logger->info('ErrorController mail feature not configured.');
            return;
        }

        try {
            $this->_sendErrorMail(
                $config,
                $this->getResponse()->getHttpResponseCode(),
                $this->view,
                $errors->request,
                $errors->exception
            );
        } catch (Exception $e) {
            $logger->err('ErrorController: Failed sending error email: ' . $e);
        }
    }

    private function setResponseCode($code)
    {
        if ($code != null) {
            $this->getResponse()->setHttpResponseCode($code);
        } else {
            $this->getResponse()->setHttpResponseCode(500);
        }
    }

    /**
     * @param $config
     * @param $responseCode
     * @param $view
     * @param $request
     * @param $exception
     * @return bool
     * @throws Application_Exception
     * @throws Opus_Mail_Exception
     *
     * TODO Escape exception messages, other stuff? Is it possible to inject javascript in E-Mail?
     */
    public function _sendErrorMail($config, $responseCode, $view, $request, $exception)
    {
        if (! isset($config->errorController->mailTo)) {
            return false;
        }

        if (! is_object($exception) or ! ($exception instanceof Exception)) {
            throw new Application_Exception('Invalid Exception object given.');
        }

        if (! is_object($request) or ! ($request instanceof Zend_Controller_Request_Abstract)) {
            throw new Application_Exception('Invalid Zend_Controller_Request_Abstract object given.');
        }

        // Setting up mail subject.
        $instanceName = isset($config->instance_name) ? $config->instance_name : 'Opus4';

        $subject = $instanceName . " (ID " . (array_key_exists('id_string', $GLOBALS) ? $GLOBALS['id_string'] : 'undef')
            . ") ($responseCode): " . get_class($exception) . " ";
        $subject .= "/" . $request->getModuleName() . "/" . $request->getControllerName() . "/"
            . $request->getActionName();

        // Setting up mail body.
        $body = '';

        $body .= "Source:\n";
        $body .= "   module:     " . $request->getModuleName() . "\n";
        $body .= "   controller: " . $request->getControllerName() . "\n";
        $body .= "   action:     " . $request->getActionName() . "\n";
        $body .= "   file:       " . $exception->getFile() . ":" . $exception->getLine() . "\n";
        $body .= "\n";

        $body .= "View:\n";
        if (isset($view->title)) {
            $body .= "   title: " . $view->title . "\n";
        }
        if (isset($view->message)) {
            $body .= "   message: " . $view->message . "\n";
        }
        $body .= "\n";

        // Add document ID for errors occuring during publish process
        $session = new Zend_Session_Namespace('Publish');
        if (isset($session->documentId)) {
            $body .= "User Session (Namespace Publish):\n";
            $body .= "   Document ID: " . $session->documentId . "\n";
        }
        $body .= "\n";

        $body .= "Request:\n";
        $serverKeys = ['HTTP_USER_AGENT', 'SCRIPT_URI', 'HTTP_REFERER', 'REMOTE_ADDR'];
        foreach ($serverKeys as $key) {
            if (array_key_exists($key, $_SERVER)) {
                $body .= "   $key: " . $_SERVER[$key] . "\n";
            }
        }
        $body .= "\n";

        $body .= "file: " . $exception->getFile() . ":" . $exception->getLine() . "\n";
        $body .= "-- start exception message --\n";
        $body .= $exception->getMessage() . "\n";
        $body .= "-- end exception message --\n\n";

        $body .= "-- start exception trace --\n";
        $body .= $exception->getTraceAsString() . "\n";
        $body .= "-- end exception trace --\n\n";

        $body .= "Request parameters:\n";
        $body .= "-- start request params --\n";
        $body .= var_export($request->getParams(), true) . "\n";
        $body .= "-- end request params --\n\n";

        $body .= "Request:\n";
        $body .= "-- start request --\n";
        $body .= var_export($request, true) . "\n";
        $body .= "-- end request --\n\n";

        if (isset($_SERVER)) {
            $body .= "Request header:\n";
            $body .= "-- start request header --\n";
            $body .= var_export($_SERVER, true) . "\n";
            $body .= "-- end request header --\n\n";
        }

        $adminAddress = [
            'address' => $config->errorController->mailTo->address,
            'name' => $config->errorController->mailTo->name,
        ];

        $mail = new Opus_Mail_SendMail();
        $mail->sendMail(
            $config->mail->opus->address,
            $config->mail->opus->name,
            $subject,
            $body,
            [$adminAddress]
        );

        return true;
    }
}
