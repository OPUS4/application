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

use Opus\Common\Document;
use Opus\Common\DocumentInterface;
use Opus\Common\Enrichment;
use Opus\Common\Model\ModelException;
use Opus\Common\Security\Realm;

class Publish_DepositController extends Application_Controller_Action
{
    /** @var array */
    public $depositData = [];

    /** @var Zend_Log */
    public $log;

    /** @var Zend_Session_Namespace */
    public $session;

    /**
     * @throws Zend_Exception
     */
    public function __construct(
        Zend_Controller_Request_Abstract $request,
        Zend_Controller_Response_Abstract $response,
        array $invokeArgs = []
    ) {
        $this->log     = $this->getLogger();
        $this->session = new Zend_Session_Namespace('Publish');

        parent::__construct($request, $response, $invokeArgs);
    }

    /**
     * stores a delivered form as document in the database
     * uses check_array
     */
    public function depositAction()
    {
        if ($this->getRequest()->isPost() !== true) {
            $this->_helper->Redirector->redirectTo('index', '', 'index');
            return;
        }

        //post content is just checked for buttons
        $post = $this->getRequest()->getPost();
        if (array_key_exists('back', $post)) {
            $this->_forward('check', 'form');
            return;
        }
        if (array_key_exists('abort', $post)) {
            if (isset($this->session->documentId)) {
                try {
                    $document = Document::get($this->session->documentId);
                    $document->delete();
                } catch (ModelException $e) {
                    $this->getLogger()->err(
                        "deletion of document # " . $this->session->documentId . " was not successful",
                        $e
                    );
                }
            }
            $this->_helper->Redirector->redirectTo('index', '', 'index');
            return;
        }

        $this->view->title    = 'publish_controller_index';
        $this->view->subtitle = $this->view->translate('publish_controller_deposit_successful');

        //deposit data is coming from the session
        if (isset($this->session->elements)) {
            foreach ($this->session->elements as $element) {
                $this->depositData[$element['name']] = [
                    'value'    => $element['value'],
                    'datatype' => $element['datatype'],
                    'subfield' => $element['subfield'],
                ];

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
            $selectedType = null;

            if (isset($this->session->selectedType)) {
                $selectedType = $this->session->selectedType;
            }

            $depositData = new Publish_Model_Deposit();
            $depositData->storeDocument($this->session->documentId, $this->log, $this->depositData, $selectedType);
        } catch (Publish_Model_Exception $e) {
            throw new Application_Exception('publish_error_unexpected');
        }

        $document = $depositData->getDocument();
        $document->setServerState('unpublished');

        $enrichments = $document->getEnrichment();
        if ($this->checkOpusSourceIsDoi($enrichments)) {
            $this->addSourceDoi($document);
        } else {
            $this->addSourceEnrichment($document);
        }

        try {
            $docId = $document->store();
        } catch (Exception $e) {
            // TODO wie sollte die Exception sinnvoll behandelt werden?
            $this->log->err("Document could not be stored successfully: " . $e->getMessage());
            throw new Application_Exception('publish_error_unexpected');
        }

        $this->log->info("Document $docId was successfully stored!");
        $this->session->documentId = $docId;

        // Prepare redirect to confirmation action.
        $this->session->depositConfirmDocumentId = $docId;

        $targetAction     = 'confirm';
        $targetController = 'deposit';
        $targetModule     = 'publish';

        $config = $this->getConfig();
        if (isset($config) && isset($config->publish->depositComplete)) {
            $targetAction     = $config->publish->depositComplete->action;
            $targetController = $config->publish->depositComplete->controller;
            $targetModule     = $config->publish->depositComplete->module;
        }

        $notification = new Application_Util_Notification($this->log, $config);
        $url          = $this->view->url(
            [
                "module"     => "admin",
                "controller" => "document",
                "action"     => "index",
                "id"         => $document->getId(),
            ],
            null,
            true
        );
        $notification->prepareMail(
            $document,
            $this->view->serverUrl() . $url
        );

        $this->_helper->Redirector->redirectToAndExit($targetAction, null, $targetController, $targetModule);
    }

    /**
     * Shows a confirmation for the user, when the publication process is
     * finished.
     */
    public function confirmAction()
    {
        // redirecting if action is called directly
        if ($this->session->depositConfirmDocumentId === null) {
            $this->_helper->Redirector->redirectToAndExit('index', null, 'index');
            return;
        }
        $this->view->docId = $this->session->depositConfirmDocumentId;

        $accessControl = Zend_Controller_Action_HelperBroker::getStaticHelper('accessControl');

        if (
            true === Realm::getInstance()->check('clearance')
                || true === $accessControl->accessAllowed('documents')
        ) {
            $this->view->showFrontdoor = true;
        }
        //unset all possible session content
        $this->session->unsetAll();
    }

    /**
     * Fügt das interne Enrichment opus.source mit dem Wert 'publish' zum Dokument hinzu.
     *
     * @param DocumentInterface $document
     * @throws ModelException
     */
    private function addSourceEnrichment($document)
    {
        $enrichment = Enrichment::new();
        $enrichment->setKeyName('opus.source');
        $enrichment->setValue('publish');
        $document->addEnrichment($enrichment);
    }

    /**
     * Fügt das interne Enrichment opus.source mit dem Wert 'doi-import' zum Dokument hinzu.
     *
     * @param Document $document
     * @throws ModelException
     */
    private function addSourceDoi($document)
    {
        $enrichment = Enrichment::new();
        $enrichment->setKeyName('opus.source');
        $enrichment->setValue('doi-import');
        $document->addEnrichment($enrichment);
    }

    /**
     * @param array $enrichments
     * @return bool
     */
    private function checkOpusSourceIsDoi($enrichments)
    {
        foreach ($enrichments as $enrichment) {
                $value = $enrichment->getValue();
                //$this->getLogger()->warn("KeyName: " . $enrichment->getKeyName());
                //$this->getLogger()->warn("Value: " . $enrichment->getValue());
            if ($value === 'crossref') {
                return true;
            }
        }
        return false;
    }
}
