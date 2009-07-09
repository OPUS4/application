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
 * @author      Wolfgang Filter <wolfgang.filter@ub.uni-stuttgart.de>
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
     * Create the mail form, getting author and main title for view
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
       $doc_data = $document->toArray();

       // Proof if 'PersonAuthor' is a multiple item or not and if 'Name' is an empty item or not
       if (empty ($doc_data['PersonAuthor']) === false)
       {
           if (empty ($doc_data['PersonAuthor']['0']['Name']) === false)
           {
              $author = $document->getPersonAuthor('0')->getName();
              if ($author == ', ')
              {
                 $this->author = $author = null;
                 $this->view->author = $author;
              }
              else
              {
                 $this->view->author = $author;
              }
           }
           else
           {
              $author = $document->getPersonAuthor()->getName();
              if ($author == ', ')
              {
                 $this->author = $author = null;
                 $this->view->author = $author;
              }
              else
              {
                 $this->view->author = $author;
              }
           }
        }
       else
       {
           $this->author = $author = null;
           $this->view->author = $author;
       }

       // Proof if 'TitleMain' is multiple or not
       if (empty($doc_data['TitleMain']) === false)
       {
           if (empty ($doc_data['TitleMain']['0']) === false)
           {
              $title = $document->getTitleMain('0')->getValue();
              $this->view->title = $title;
           }
           else
           {
              $title = $document->getTitleMain()->getValue();
              $this->view->title = $title;
           }
       }
       else
       {
           $this->title = $title = null;
           $this->view->title = $title;
       }

       // show mail form
       $mailForm = new MailForm();
       $mailForm->setAction($this->view->url(array('module' => "frontdoor", "controller"=>'mail', "action"=>"recommendate")));
       $mailForm->setMethod('post');
       $this->view->mailForm = $mailForm;
    }

    public function recommendateAction()
    {
     //action for recommendate document via mail posting (to be done)
    }
}
