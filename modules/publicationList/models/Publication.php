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
 * @package     Module_SolrSearch
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class PublicationList_Model_Publication {

    private $authors = array();
    private $bibtexUrl;
    private $bibtexUrlExternal;
    private $completedYear;
    private $editors = array();
    private $identifierDoi;
    private $identifierUrl;
    private $issue;
    private $note;
    private $pageFirst;
    private $pageLast;
    private $publishedYear;
    private $risUrl;
    private $risUrlExternal;
    private $titleMain;
    private $titleParent;
    private $volume;
    
    public function __construct($id, $externalUrl = null) {
        $doc = new Opus_Document($id);
        $collections = $doc->getCollection();

        $fc = new Zend_Controller_Request_Http();
        $hostname = $fc->getHttpHost();

        $fc = Zend_Controller_Front::getInstance();
        $request = $fc->getRequest();
        $baseUrl = $request->getBaseUrl();

        $config = Zend_Registry::get('Zend_Config');

        foreach ($doc->getPersonAuthor() as $author) {
             $firstName = $author->getFirstName();
             $lastName = $author->getLastName();
             $author = new PublicationList_Model_Author($firstName, $lastName);

             foreach ($collections as $c) {
                   if (strcmp($c->getName(), $lastName.", ".$firstName) === 0) {
                       $author->setUrl($baseUrl."/publicationList/index/search/searchtype/collection/id/".$c->getId());
                       if (!is_null($baseUrl)) {
                            $author->setUrlExternal($externalUrl.$c->getNumber());
                       }
                   }
             }
             $this->addAuthor($author);
        }

        $this->bibtexUrl = $baseUrl."/citationExport/index/index/output/bibtex/docId/".$id;
        $this->bibtexUrlExternal = "http://".$hostname.$this->bibtexUrl."/theme/plain";

        if ($doc->getCompletedYear() && ($doc->getCompletedYear() !== "0000")) {
            $this->completedYear = $doc->getCompletedYear();
        }

        foreach ($doc->getPersonEditor() as $editor) {
            $firstName = $editor->getFirstName();
            $lastName = $editor->getLastName();
            $this->addEditor($firstName." ".$lastName);
        }

        if (isset($config->publicationlist->doiresolver->url)) {
            if ($doc->getIdentifierDoi()) {
                $this->identifierDoi = "http://".$config->publicationlist->doiresolver->url.$doc->getIdentifierDoi(0)->getValue();
            }
        }

        if ($doc->getIdentifierUrl()) {
            $this->identifierUrl = $doc->getIdentifierUrl(0)->getValue();
        }
        
        if ($doc->getIssue()) {
            $this->issue = $doc->getIssue();
        }

        if ($doc->getNote()) {
            $this->note = $doc->getNote(0)->getMessage();
        }

        if ($doc->getPageFirst()) {
            $this->pageFirst = $doc->getPageFirst();
        }

        if ($doc->getPageLast()) {
            $this->pageLast = $doc->getPageLast();
        }

        $this->risUrl = $baseUrl."/citationExport/index/index/output/ris/docId/".$id;
        $this->risUrlExternal = "http://".$hostname.$this->risUrl."/theme/plain";


        if ($doc->getPublishedYear()) {
            $this->publishedYear = $doc->getPublishedYear();
        } 

        if ($doc->getTitleMain()) {
            $this->titleMain = $doc->getTitleMain(0)->getValue();
        }
        
        if ($doc->getTitleParent()) {
            $this->titleParent = $doc->getTitleParent(0)->getValue();
        }        

        if ($doc->getVolume()) {
            $this->volume = $doc->getVolume();
        }

        unset($doc);
        unset($collections);
        unset($fc);
        unset($hostname);
        unset($request);
        unset($baseUrl);

   }

   /* Adder--Methoden */

    public function addAuthor($author) {
        array_push($this->authors, $author);
    }


    public function addEditor($string) {
        array_push($this->editors, $string);
    }

    /* Setter--Methoden */

    public function setBibtexUrl($string) {
        $this->bibtexUrl = $string;
    }


    public function setRisUrl($string) {
        $this->risUrl = $string;
    }

    /* Getter-Methoden */


    public function getAuthors() {
        return $this->authors;
    }

    public function getBibtexUrl() {
        return $this->bibtexUrl;
    }

    public function getBibtexUrlExternal() {
        return $this->bibtexUrlExternal;
    }

    public function getCompletedYear() {
        return $this->completedYear;
    }

    public function getEditors() {
        return $this->editors;
    }

    public function getIdentifierDoi() {
        return $this->identifierDoi;
    }


    public function getIdentifierUrl() {
        return $this->identifierUrl;
    }

    public function getIssue() {
        return $this->issue;
    }

    public function getNote() {
        return $this->note;
    }

    public function getPageFirst() {
        return $this->pageFirst;
    }

    public function getPageLast() {
        return $this->pageLast;
    }

    public function getPublishedYear() {
        return $this->publishedYear;
    }

    public function getRisUrlExternal() {
        return $this->risUrlExternal;
    }

    public function getRisUrl() {
        return $this->risUrl;
    }
    
    public function getTitleMain() {
        return $this->titleMain;
    }

    public function getTitleParent() {
        return $this->titleParent;
    }

    public function getVolume() {
        return $this->volume;
    }

}
?>
