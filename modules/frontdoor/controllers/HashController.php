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
class Frontdoor_HashController extends Controller_Action {

    /**
     *
     * getting hashvalues from Opus_Document to display them
     *
     */
    public function indexAction() {
        // get document
        $request = $this->getRequest();
        $docId = $request->getParam('docId');
        $this->view->docId = $docId;
        $document = new Opus_Document($docId);
        // get authors
        $author_names = array();
        $authors = $document->getPersonAuthor();
        // more than one author
        if (true === is_array($authors)) {
            $ni = 0;
            foreach ($authors as $author) {
                $author_names[$ni] = $author->getName();
                $ni = $ni + 1;
            }
            // only one author
        } else {
            $author_names[0] = $document->getPersonAuthor()->getName();
        }
        $this->view->author = $author_names;

        // get title
        $title = $document->getTitleMain();
        $title_value = null;
        if (true === is_array($title)) {
            $title_value = $title[0]->getValue();
        } else {
            $title_value = $title->getValue();
        }
        $this->view->title = $title_value;

        // get type
        $type = $document->getType();
        $this->view->type = $type;

        //searching for files, getting filenumbers and hashes
        $fileNumber = 0;
        $files = $document->getFile();
        if (true === is_array($files) && count($files) > 0) {
            $fileNumber = count($files);
            $this->view->fileNumber = $fileNumber;
        }
        // Iteration over all files, hashtypes and -values
        $gpg = new Opus_GPG();
        $this->view->verifyResult = array();
        $fileNames = array();
        $hashType = array();
        $hashSoll = array();
        $hashIst = array();
        $hashNumber = array();
        for ($fi = 0; $fi < $fileNumber; $fi++) {
            $fileNames[$fi] = $document->getFile($fi)->getPathName();
            $hashNumber[$fi] = 0;
            if ($document->getFile($fi)->exists() === true) {
                if (true === is_array($hashes = $document->getFile($fi)->getHashValue())) {
                    $countHash = count($hashes);
                    for ($hi = 0; $hi < $countHash; $hi++) {
                        $hashNumber[$fi] = $countHash;
                        $hashSoll[$fi][$hi] = $document->getFile($fi)->getHashValue($hi)->getValue();
                        $hashType[$fi][$hi] = $document->getFile($fi)->getHashValue($hi)->getType();
                        if (substr($hashType[$fi][$hi], 0, 3) === 'gpg') {
                            try {
                                $this->view->verifyResult[$fileNames[$fi]] = $gpg->verifyPublicationFile($document->getFile($fi));
                            } catch (Exception $e) {
                                $this->view->verifyResult[$fileNames[$fi]] = array('result' => array($e->getMessage()), 'signature' => $hashSoll[$fi][$hi]);
                            }
                        } else {
                            $hashIst[$fi][$hi] = 0;
                            if (true === $document->getFile($fi)->canVerify()) {
                                $hashIst[$fi][$hi] = $document->getFile($fi)->getRealHash($hashType[$fi][$hi]);
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