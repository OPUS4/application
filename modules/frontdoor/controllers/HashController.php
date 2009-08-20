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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id:
 *
 *
 *
 */
class Frontdoor_HashController extends Zend_Controller_Action
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
       $doc_data = $document->toArray();
       $this->view->document = $document;
       $this->view->doc_data = $doc_data;

       //proof if TitleMain exists and if item is multiple (only one title is commited to view)
       if (array_key_exists('TitleMain', $doc_data))
       {
          $titlemain = $document->getTitleMain();
          if (array_key_exists('0', $titlemain) === true)
          {
              $title = $document->getTitleMain('0')->getValue();
              $this->view->title = $title;
          }
          else
          {
              if (array_key_exists ('Value', $titlemain) === true)
              {
                 $title = $document->getTitleMain()->getValue;
                 $this->view->title = $title;
              }
              else
              {
                 $this->title = $title = null;
                 $this->view->title = $title;
              }
           }
       }
       else
       {
          $this->title = $title = null;
          $this->view->title = $title;
       }

      //proof if PersonAuthor exists and if item is multiple (only first author is commited to hash-view)
      if (array_key_exists('PersonAuthor', $doc_data) === true)
      {
         $personauthor = $document->getPersonAuthor();
         if (array_key_exists('0', $personauthor) === true)
         {
               $author = $document->getPersonAuthor('0')->getName();
               if ($author !== ', ')
               {
                   $this->view->author = $author;
               }
               else
               {
                   $this->author = $author = null;
                   $this->view->author = $author;
               }
          }
          else
          {
              if (array_key_exists('Name', $personauthor) === true)
              {
                  $author = $document->getPersonAuthor()->getName;
                  $this->view->author = $author;
              }
              else
              {
                  $this->author = $author = null;
                  $this->view->author = $author;
              }
          }
      }
      else
      {
            $this->author = $author = null;
            $this->view->author = $author;
      }

      //searching for files, getting filenumbers and hashes if document is available

      if (array_key_exists('File', $doc_data) === true)
      {
         $files = $document->getFile();
         $fileNumber = count($files);
         $this->view->fileNumber = $fileNumber;
         if (array_key_exists('0', $files) === true)
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
