<?php
/**
 *
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
 * @author      Wolfgang Filter <wolfgang.filter@ub.un-stuttgart.de>
 * @author      Simone Finkbeiner <simone.finkbeiner@ub.un-stuttgart.de>
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id:
 *
 *
 *
 */
class FrontdoorXSLT_HashController extends Zend_Controller_Action
{
    /**
     *
     *getting hashvalues from Opus_Document to display them
     *case of multiple files and multiple hashtypes are considered
     *commits array 'fileNames' with all filenames to view
     *commits array 'hashValueType' with all hashvalues and hashtypes to view
     *
     */

    public function indexAction()
    {
       //starting document model
       $request = $this->getRequest();
       $docId = $request->getParam('docId');
       $this->view->docId = $docId;
       $document = new Opus_Document($docId);
       // get authors
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

       $this->view->document = $document;

      //searching for files, getting filenumbers and hashes if document is available
       $fileNumber = 0;
       $first_hash = null;
       $files = $document->getFile();
       if (true === is_array($files)) {
           $fileNumber = count($files);
           $this->view->fileNumber = $fileNumber;
           $hash_exists = $document->getFile('0')->getHashValue();
           if (is_array($hash_exists)) {
               $first_hash = $document->getFile('0')->getHashValue('0')->getValue();
               $this->view->first_hash = $first_hash;
            }
 //           else {
 //              $this->first_hash = $first_hash = null;
 //              $this->view_first_hash = $first_hash;
 //           }
       }
 //     else {
 //           $this->first_hash = $first_hash = null;
 //           $this->view_first_hash = $first_hash;
 //      }
 /*        if (array_key_exists('0', $files) === true)
         {
            $hash_exists = $document->getFile('0')->getHashValue();
            if (array_key_exists('0', $hash_exists))
            {
               $first_hash = $document->getFile('0')->getHashValue('0')->getValue();
               $this->view->first_hash = $first_hash;
            }
            else
            {
               $this->first_hash = $first_hash = null;
               $this->view_first_hash = $first_hash;
            }
         }
         else
         {
            $this->first_hash = $first_hash = null;
            $this->view_first_hash = $first_hash;
         }
      }
      else
      {
          $this->first_hash = $first_hash = null;
          $this->view_first_hash = $first_hash;
      }
*/

       // Iteration over all files, hashtypes and -values; filling up the arrays $fileNames() and $hashValueType()
       $gpg = new Opus_GPG();
       $this->view->verifyResult = array();
       if ($first_hash !== NULL)
       {
           for ($i = 0; $i < $fileNumber; $i++)
           {
                 if (is_array ($hashes = $document->getFile($i)->getHashValue()) === true)
                 {
                     $hashNumber = count($hashes);
                     $names = $document->getFile($i)->getPathName();
                     $fileNames[] = $names;
                     for ($j = 0; $j < $hashNumber; $j++)
                     {
                         $hashValue = $document->getFile($i)->getHashValue($j)->getValue();
                         $hashType = $document->getFile($i)->getHashValue($j)->getType();
                         $hashValueType['hashValue_' .$i. '_' .$j] = $hashValue;
                         $hashValueType['hashType_' .$i. '_' .$j] = $hashType;
                         if (substr($hashType, 0, 3) === 'gpg') {
                             try
                             {
                                 $this->view->verifyResult[$document->getFile($i)->getPathName()] = $gpg->verifyPublicationFile($document->getFile($i));
                             }
                             catch (Exception $e)
                             {
                                 $this->view->verifyResult[$document->getFile($i)->getPathName()] = array('result' => array($e->getMessage()), 'signature' => $hashValue);
                             }
                         }
                     }
                  }
             }
             $this->view->hashValueType = $hashValueType;
             $this->view->fileNames = $fileNames;
        }
        else
        {
            $this->fileNumber = $fileNumber = 0;
            $this->view->fileNumber = $fileNumber;
        }
    }
}
