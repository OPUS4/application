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
 * @package     Module_Publish
 * @author      Susanne Gottwald <gottwald@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Publish_DepositController extends Application_Controller_Action {

    public $depositData = array();
    public $log;
    public $session;
    public $document;

    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response,
                                array $invokeArgs = array()) {
        $this->log = $this->getLogger();
        $this->session = new Zend_Session_Namespace('Publish');

        parent::__construct($request, $response, $invokeArgs);
    }

    /**
     * stores a delivered form as document in the database
     * uses check_array
     */
    public function depositAction() {

        if ($this->getRequest()->isPost() !== true) {
            return $this->_helper->Redirector->redirectTo('index', '', 'index');
        }

        //post content is just checked for buttons
        $post = $this->getRequest()->getPost();
        if (array_key_exists('back', $post)) {
            return $this->_forward('check', 'form');
        }
        if (array_key_exists('abort', $post)) {
            if (isset($this->session->documentId)) {
                try {
                    $document = new Opus_Document($this->session->documentId);
                    $document->deletePermanent();
                }
                catch (Opus_Model_Exception $e) {
                    $this->getLogger()->err(
                        "deletion of document # " . $this->session->documentId . " was not successful", $e
                    );
                }
            }
            return $this->_helper->Redirector->redirectTo('index', '', 'index');
        }

        $this->view->title = 'publish_controller_index';
        $this->view->subtitle = $this->view->translate('publish_controller_deposit_successful');

        //deposit data is coming from the session
        if (isset($this->session->elements)) {
            foreach ($this->session->elements AS $element) {
                $this->depositData[$element['name']] = array(
                    'value' => $element['value'],
                    'datatype' => $element['datatype'],
                    'subfield' => $element['subfield']);

                $this->log->debug(
                    "STORE DATA: " . $element['name'] . ": " . $element['value'] . ", Typ:" . $element['datatype']
                    . ", Sub:" . $element['subfield']
                );
            }
        }

        if (isset($this->depositData['send'])) {
            unset($this->depositData['send']);
        }

        try {
            $depositData = new Publish_Model_Deposit();
            $depositData->storeDocument($this->session->documentId, $this->log, $this->depositData);
        }
        catch (Publish_Model_Exception $e) {
            throw new Application_Exception('publish_error_unexpected');
        }

        $this->document = $depositData->getDocument();
        $this->document->setServerState('unpublished');

        try {
            $docId = $this->document->store();
        }
        catch (Exception $e) {
            // TODO wie sollte die Exception sinnvoll behandelt werden?
            $this->log->err("Document could not be stored successfully: " . $e->getMessage());
            throw new Application_Exception('publish_error_unexpected');
        }

        $this->log->info("Document $docId was successfully stored!");
        $this->session->documentId = $docId;

        // Prepare redirect to confirmation action.
        $this->session->depositConfirmDocumentId = $docId;

        $targetAction = 'confirm';
        $targetController = 'deposit';
        $targetModule = 'publish';

        $config = $this->getConfig();
        if (isset($config) and isset($config->publish->depositComplete)) {
            $targetAction = $config->publish->depositComplete->action;
            $targetController = $config->publish->depositComplete->controller;
            $targetModule = $config->publish->depositComplete->module;
        }

        $notification = new Application_Util_Notification($this->log, $config);
        $url = $this->view->url(
            array(
                "module" => "admin",
                "controller" => "document",
                "action" => "index",
                "id" => $this->document->getId()
            ),
            null,
            true
        );
        $notification->prepareMail(
            $this->document,  $this->view->serverUrl() . $url
        );

        return $this->_helper->Redirector->redirectToAndExit($targetAction, null, $targetController, $targetModule);
    }

    /**
     * Shows a confirmation for the user, when the publication process is
     * finished.
     */
    public function confirmAction() {
        // redirecting if action is called directly
        if (is_null($this->session->depositConfirmDocumentId)) {
            return $this->_helper->Redirector->redirectToAndExit('index', null, 'index');
        }
        $this->view->docId = $this->session->depositConfirmDocumentId;

        $accessControl = Zend_Controller_Action_HelperBroker::getStaticHelper('accessControl');

        if (true === Opus_Security_Realm::getInstance()->check('clearance')
                || true === $accessControl->accessAllowed('documents')) {
            $this->view->showFrontdoor = true;
        }
        //unset all possible session content
        $this->session->unsetAll();
    }

}

