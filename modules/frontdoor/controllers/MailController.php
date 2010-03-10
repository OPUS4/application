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
 * @package     Module_Frontdoor
 * @author      Simone Finkbeiner-Franke <simone.finkbeiner@ub.uni-stuttgart.de>
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id:
 */

/**
 * Controller for document recommendation starting from Frontdoor
 *
 */
class Frontdoor_MailController extends Zend_Controller_Action
{
    /**
     *
     * @return void
     *
     */
    public function indexAction()
    {
       $request = $this->getRequest();
       $docId = $request->getParam('docId');
       $this->view->docId = $docId;
       $document = new Opus_Document($docId);
       // get author
       $author_names = array();
       $authors = $document->getPersonAuthor();
       print_r($authors);
       if (true === is_array($authors)) {
           $ni = 0;
           foreach ($authors as $author) {
               $author_names[$ni] = $author->getName();
               $ni = $ni + 1;
           }
       }
       else {
           $author_names[0] = $document->getPersonAuthor()->getName();
       }
       $this->view->author = $author_names;

       // get title
       $title = $document->getTitleMain();
       if (true === is_array($title)) {
           $title_value = $title[0]->getValue();
       }
       else {
           $title_value = $title->getValue();
       }
       $this->view->title = $title_value;
       // get type
       $type = $document->getType();
       $this->view->type = $type;
       // show mail form
       $mailForm = new MailForm();
       $mailForm->title->setValue($title_value);
       $mailForm->doc_id->setValue($docId);
       $mailForm->setAction($this->view->url(array('module' => "frontdoor", "controller"=>'mail', "action"=>'sendmail')));
       $mailForm->setMethod('post');
       $this->view->mailForm = $mailForm;
    }

    public function sendmailAction()
    {
     $form = new MailForm();
     if (true === $this->getRequest()->isPost()) {
         $data = $this->getRequest()->getPost();
         if (true === $form->isValid($data)) {
             $from = '';
             $from = $form->getValue('sender_mail');
             if ('' == $from) {
                $registry = Zend_Registry::getInstance();
                $config = $registry->get('Zend_Config');
                if (true === isset($config->mail->opus->address)) {
                    $from = $config->mail->opus->address ;
                    }
             }
             $fromName = $form->getValue('sender');
             $title = $form->getValue('title');
             $docId = $form->getValue('doc_id');
             $recipientMail = $form->getValue('recipient_mail');
             $subject = $this->view->translate('frontdoor_sendmailsubject');
             $bodyText = 'Hallo,' . '\n' . $this->view->translate('frontdoor_sendmailbody1') . ':\n';
             $bodyText .= $title;
             $bodyText .= '\n' . $this->view->translate('frontdoor_sendmailbody2') . ': ';
             $bodyText .= '\n' . $this->view->translate('frontdoor_sendmailmsg') . ': ' . $form->getValue('message');
             $bodyText .= '\n' . $this->view->translate('frontdoor_sendersname') .': ' . $fromName;
             $bodyText .= '\n' . $this->view->translate('frontdoor_sendersmail') .': ' . $from;
             $recipient = array(1 => array('address' => $recipientMail,'name' => $form->getValue('recipient')));
             $mailSendMail = new Opus_Mail_SendMail();
             try {
                $mailSendMail->sendMail($from,$fromName,$subject,$bodyText,$recipient);
                $this->view->ok = '1';
                $this->view->docId = $docId;
                $this->view->title = $title;
                $this->view->recipientMail = $recipientMail;
                $this->view->message = $form->getValue('message');
                $this->render('recfeedback');
             } catch (Exception $e) {
                 $this->view->form = $e->getMessage();
                 $this->view->text = $this->view->translate('frontdoor_mail_notok');
             }
         } else {
              $this->view->form = $form;
         }
     }
    $this->view->form = $form;
    }

    public function toauthorAction()
    {
       $request = $this->getRequest();
       $docId = $request->getParam('docId');
       $this->view->docId = $docId;
       $document = new Opus_Document($docId);
       $author = $document->getPersonAuthor();
       if (true === is_array($author)) {
           foreach ($author as $au) {

                 $authors[] = array('name' => $au->getName(), 'mail' => $au->getEmail(), 'allowMail' => $au->getAllowEmailContact());

           }
       }
       else {
           $authors[] = array('name' => $author->getName(), 'mail' => $author->getEmail());
       }
       $this->view->author = $authors;
       // get title
       $title = $document->getTitleMain();
       if (true === is_array($title)) {
           $title_value = $title[0]->getValue();
       }
       else {
           $title_value = $title->getValue();
       }
       $this->view->title = $title_value;
       // get type
       $type = $document->getType();
       $this->view->type = $type;
       // show mail form


       //$this->view->mailForm = $form;




        $form = new ToauthorForm(array('authors' => $authors));
        $form->setAction($this->view->url(array('module' => "frontdoor", "controller"=>'mail', "action"=>'toauthor')));
        $form->setMethod('post');
        if (true === $this->getRequest()->isPost()) {
         $data = $this->getRequest()->getPost();

         if (true === $form->isValid($data)) {
             $from = $form->getValue('sender_mail');
             $fromName = $form->getValue('sender');
             $subject = $this->view->translate('frontdoor_sendmailsubject');
             $bodyText = $form->getValue('message');
             $recipient = array(1 => array('address' => 'author@mail.com' ,'name' => 'author_name'));
             $mailSendMail = new Opus_Mail_SendMail();
             try {
                $mailSendMail->sendMail($from,$fromName,$subject,$bodyText,$recipient);
                $this->view->ok = true;

                $this->view->success = 'frontdoor_mail_ok';
                $this->render('feedback');
             } catch (Exception $e) {
                 $this->view->ok = false;
                 $this->view->form = $e->getMessage();
                 $this->view->success = 'frontdoor_mail_notok';
                 $this->render('feedback');
             }
         } else {
              $this->view->form = $form;
         }
     }
    $this->view->form = $form;

    }
}
