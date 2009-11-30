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
class FrontdoorXSLT_MailController extends Zend_Controller_Action
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
       $mailForm->setAction($this->view->url(array('module' => "frontdoorXSLT", "controller"=>'mail', "action"=>'sendmail')));
       $mailForm->setMethod('post');
       $this->view->mailForm = $mailForm;
    }

    public function sendmailAction()
    {
     $form = new MailForm();
     if (true === $this->getRequest()->isPost()) {
         $data = $this->getRequest()->getPost();
         if (true === $form->isValid($data)) {
             $from = $form->getValue('sender_mail');
             $fromName = $form->getValue('sender');
             $subject = '';
             $bodyText = $form->getValue('message');
             $recipient = array('address' => $form->getValue('recipient_mail'),'name' => $form->getValue('recipient'));
             $mailSendMail = new Opus_Mail_SendMail();
             try {
                $mailSendMail->sendMail($from,$fromName,$subject,$bodyText,$recipient);
                $this->view->form = $form;
             } catch (Exception $e) {
                 $this->view->form = $e->getMessage();
             }
         } else {
              $this->view->form = $form;
         }
     }
//     $this->view->form = $form;
    }
}
