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
       if (true === is_array($files) && count($files) > 0) {
           $fileNumber = count($files);
           $this->view->fileNumber = $fileNumber;
           $hash_exists = $document->getFile('0')->getHashValue();
           if (true === is_array($hash_exists)) {
               $first_hash = $document->getFile('0')->getHashValue('0')->getValue();
               $this->view->first_hash = $first_hash;
            }
       }
       // Iteration over all files, hashtypes and -values
       $gpg = new Opus_GPG();
       $this->view->verifyResult = array();
       $fileNames = array();
       $hashType = array();
       $hashSoll = array();
       $hashIst = array();
       $hashNumber = array();
       if ($first_hash !== NULL) {
           for ($i = 0; $i < $fileNumber; $i++) {
               $fileNames[$i] = $document->getFile($i)->getPathName();
               if (true === is_array ($hashes = $document->getFile($i)->getHashValue())) {
                     $countHash = count($hashes);
                     for ($j = 0; $j < $countHash; $j++) {
                         $hashNumber[$i] = $countHash;
                         $hashSoll[$i][$j] = $document->getFile($i)->getHashValue($j)->getValue();
                         $hashType[$i][$j] = $document->getFile($i)->getHashValue($j)->getType();
                         if (substr($hashType[$i][$j], 0, 3) === 'gpg') {
                             try
                             {
                                 $this->view->verifyResult[$fileNames[$i]] = $gpg->verifyPublicationFile($document->getFile($i));
                             }
                             catch (Exception $e)
                             {
                                 $this->view->verifyResult[$fileNames[$i]] = array('result' => array($e->getMessage()), 'signature' => $hashSoll[$i][$j]);
                             }
                         }
                       else {
                           $hashIst[$i][$j] = 0;
                           if (true === $document->getFile($i)->canVerify()) {
                               $hashIst[$i][$j] = $document->getFile($i)->getRealHash($hashType[$i][$j]);
                           }
                       }
                     }
                  }
             }
       }
       $this->view->hashType = $hashType;
       $this->view->hashSoll = $hashSoll;
       $this->view->hashIst = $hashIst;
       $this->view->hashNumber = $hashNumber;
       $this->view->fileNames = $fileNames;
       $this->view->fileNumber = $fileNumber;
    }
}