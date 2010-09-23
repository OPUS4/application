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
 * @package     Application - Module Publish
 * @author      Susanne Gottwald <gottwald@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Publish_2_IndexController$
 */

/**
 * Main entry point for this module.
 *
 * @category    Application
 * @package     Module_Publish
 */
class Publish_DepositController extends Controller_Action {

    public $postData = array();

    /**
     * stores a delivered form as document in the database
     * uses check_array
     */
    public function depositAction() {
        $log = Zend_Registry::get('Zend_Log');
        $defaultNS = new Zend_Session_Namespace('Publish');

        $this->view->title = $this->view->translate('publish_controller_index');

        if ($this->getRequest()->isPost() === true) {

            $post = $this->getRequest()->getPost();

            // TODO: Hier oder früher überprüfen?
            if (is_null($defaultNS->elements)) {
                throw new Exception("is null");
            }
            if (!is_array($defaultNS->elements)) {
                throw new Exception("no array");
            }
            
            foreach ($defaultNS->elements AS $element) {
                $this->postData[$element['name']] = $element['value'];
            }

            $this->postData = array_merge($this->postData, $post);            

            $depositForm = new Publish_Form_PublishingSecond($defaultNS->documentType, $defaultNS->documentId, $defaultNS->fulltext, $defaultNS->additionalFields, $this->postData);
            $depositForm->populate($this->postData);

            if (array_key_exists('back', $post)) {                
                $this->view->form = $depositForm;
                return $this->renderScript('form/check.phtml');
                
            } else {
                if (isset($this->postData['send']))
                    unset($this->postData['send']);                

                $depositData = new Publish_Model_Deposit($defaultNS->documentId, $defaultNS->documentType, $this->postData);

                $document = $depositData->getDocument();

                $projects = $depositData->getDocProjects();

                $document->setServerState('unpublished');

                $docId = $document->store();

                $log->info("Document was sucessfully stored!");

                $this->_notifyReferee($projects);
            }
        } else {
            // GET Reqquest is redirected to index
            $url = $this->view->url(array('controller' => 'index', 'action' => 'index'));
            return $this->redirectTo($url);
        }
    }    

    /**
     *  Method finally sends an email to the referrers named in config.ini
     */
    private function _notifyReferee($projects = null) {
        $log = Zend_Registry::get('Zend_Log');
        $defaultNS = new Zend_Session_Namespace('Publish');
        $mail = new Mail_PublishNotification($defaultNS->documentId, $projects, $this->view);
        if ($mail->send() === false)
            $log->err("email to referee could not be sended!");
        else
            $log->info("Referee has been informed via email.");
    }

}

