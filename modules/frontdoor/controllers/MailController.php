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
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Mail\SendMail;

/**
 * Controller for document recommendation starting from Frontdoor
 */
class Frontdoor_MailController extends Application_Controller_Action
{
    /**
     * TODO: this action is currently untested and therefore not supported
     */
    public function indexAction()
    {
        throw new Application_Exception('currently not supported');
        /*
        $docId = $this->getRequest()->getParam('docId');
        if ($docId === null) {
            throw new Application_Exception('missing parameter docId');
        }

        $document = Document::get($docId);
        $this->view->docId = $docId;
        $this->view->type = $document->getType();

        $author_names = array();
        foreach ($document->getPersonAuthor() as $author) {
            array_push($author_names, $author->getName());
        }
        $this->view->author = $author_names;

        $title = $document->getTitleMain();
        if (count($title) > 0) {
            $this->view->title = $title[0]->getValue();
        }
        else {
            $this->view->title = 'untitled document';
        }

        // show mail form
        $mailForm = new Frontdoor_Form_MailForm();
        $mailForm->title->setValue($this->view->title);
        $mailForm->doc_id->setValue($docId);
        $mailForm->doc_type->setValue($this->view->translate($this->view->type));
        $mailForm->setAction(
            $this->view->url(array('module' => 'frontdoor', 'controller' => 'mail', 'action' => 'sendmail'))
        );
        $mailForm->setMethod('post');
        $this->view->mailForm = $mailForm;
         *
         */
    }

    /**
     * TODO: this action is currently untested and therefore not supported
     */
    public function sendmailAction()
    {
        throw new Application_Exception('currently not supported');

        /*
        $form = new Frontdoor_Form_MailForm();
        $this->view->form = $form;

        if (!$this->getRequest()->isPost()) {
            return ;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        $from = '';
        $from = $form->getValue('sender_mail');
        if ($from === '') {
            $config = Config::get();
            if (true === isset($config->mail->opus->address)) {
                $from = $config->mail->opus->address;
            }
        }
        $fromName = $form->getValue('sender');
        $title = $form->getValue('title');
        $docId = $form->getValue('doc_id');
        $docType = $form->getValue('doc_type');
        $recipientMail = $form->getValue('recipient_mail');
        $subject = $this->view->translate('frontdoor_sendmailsubject');
        $bodyText = 'Hallo,' . '\n' . $this->view->translate('frontdoor_sendmailbody1') . ':\n';
        $bodyText .= $title . ' (' . $docType . ')';
        $bodyText .= '\n' . $this->view->translate('frontdoor_sendmailbody2') . ': ';
        $bodyText .= '\n' . $this->view->translate('frontdoor_sendmailmsg') . ': ' . $form->getValue('message');
        $bodyText .= '\n' . $this->view->translate('frontdoor_sendersname') . ': ' . $fromName;
        $bodyText .= '\n' . $this->view->translate('frontdoor_sendersmail') . ': ' . $from;
        $recipient = array(1 => array('address' => $recipientMail, 'name' => $form->getValue('recipient')));
        $mailSendMail = new SendMail();
        try {
            $mailSendMail->sendMail($from, $fromName, $subject, $bodyText, $recipient);
            $this->view->ok = '1';
            $this->view->docId = $docId;
            $this->view->title = $title;
            $this->view->docType = $docType;
            $this->view->recipientMail = $recipientMail;
            $this->view->message = $form->getValue('message');
            $this->render('recfeedback');
        }
        catch (Exception $e) {
            $this->view->form = $e->getMessage();
            $this->view->text = $this->view->translate('frontdoor_mail_notok');
        }
         *
         */
    }

    /**
     * Send mail to author(s) of document.
     */
    public function toauthorAction()
    {
        $docId = $this->getRequest()->getParam('docId');
        if ($docId === null) {
            throw new Application_Exception('missing parameter docId');
        }
        if (is_array($docId)) {
            $docId = end($docId);
        }

        $authorsModel = null;
        try {
            $authorsModel = new Frontdoor_Model_Authors($docId);
        } catch (Frontdoor_Model_Exception $e) {
            throw new Application_Exception($e->getMessage());
        }

        $authors = $authorsModel->getContactableAuthors();
        if (empty($authors)) {
            throw new Application_Exception('no authors contactable via email');
        }

        $form = new Frontdoor_Form_ToauthorForm(['authors' => $authors]);
        $form->setAction(
            $this->view->url(
                [
                    'module'     => 'frontdoor',
                    'controller' => 'mail',
                    'action'     => 'toauthor',
                ]
            )
        );
        $form->setMethod('post');

        $this->view->docId = $docId;

        if (! $this->getRequest()->isPost() || ! $form->isValid($this->getRequest()->getPost())) {
            $this->view->form   = $form;
            $this->view->author = $authors;
            $this->view->type   = $authorsModel->getDocument()->getType();
            $this->view->title  = $authorsModel->getDocument()->getTitleMain(0)->getValue();
            return;
        }

        try {
            $authorsModel->sendMail(
                new SendMail(),
                $form->getValue('sender_mail'),
                $form->getValue('sender'),
                $this->view->translate('mail_toauthor_subject'),
                $form->getValue('message'),
                $form->getValue('authors')
            );
            $this->view->success = 'frontdoor_mail_ok';
        } catch (Exception $e) {
            $this->getLogger()->err($e->getMessage());
            $this->view->success = 'frontdoor_mail_notok';
        }
        $this->render('feedback');
    }
}
