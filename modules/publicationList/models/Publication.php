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

    private $address;
    private $authors = array();
    private $bibtexUrl;
    private $completedYear;
    private $editors = array();
    private $institution;
    private $issue;
    private $pageFirst;
    private $pageLast;
    private $publisher;
    private $risUrl;
    private $school;
    private $title;
    private $titleParent;
    private $volume;
    private $year;


    public function __construct($id) {
        $doc = new Opus_Document($id);
        $collections = $doc->getCollection();
        if ($doc->getCompletedYear() && ($doc->getCompletedYear() !== "0000")) {
           $this->setCompletedYear($doc->getCompletedYear());
        }
        if ($doc->getIssue()) {
            $this->setIssue($doc->getIssue());
        }

        if ($doc->getPageFirst()) {
            $this->setpageFirst($doc->getPageFirst());
        }

        if ($doc->getPageLast()) {
            $this->setpageLast($doc->getPageLast());
        }


        foreach ($doc->getPersonAuthor() as $author) {
             $firstName = $author->getFirstName();
             $lastName = $author->getLastName();
             $author = new PublicationList_Model_Author($firstName, $lastName);

             foreach ($collections as $c) {
                   if (strcmp($c->getName(), $lastName.", ".$firstName) === 0) {
                       $author->setUrl($c->getId());
                   }
             }
  
             $this->addAuthor($author);

        }

        foreach ($doc->getPersonEditor() as $editor) {
              $firstName = $editor->getFirstName();
              $lastName = $editor->getLastName();
              $this->addEditor($firstName." ".$lastName);
        }

        if ($doc->getPublishedYear()) {
              $this->setYear($doc->getPublishedYear());
        }

        if ($doc->getTitleMain()) {
              $this->setTitle($doc->getTitleMain(0)->getValue());
        }

        if ($doc->getTitleParent()) {
              $this->setTitleParent($doc->getTitleParent(0)->getValue());
        }

        if ($doc->getVolume()) {
              $this->setVolume($doc->getVolume());
        }


        $this->bibtexUrl = array(
                'module' => 'citationExport',
                'controller' => 'index',
                'action' => 'index',
                'output' => 'bibtex',
                'docId' => $id);

        $this->risUrl = array(
                'module' => 'citationExport',
                'controller' => 'index',
                'action' => 'index',
                'output' => 'ris',
                'docId' => $id);

    }

    public function setAddress($string) {
        $this->address = $string;
    }

    public function getAddress() {
        return $this->address;
    }


    public function addAuthor($author) {
        array_push($this->authors, $author);
    }

    public function getAuthors() {
        return $this->authors;
    }
    
    public function getAuthorString() {
        $string = "";
        foreach ($this->authors as $author) {
            $string .= $author.", ";
        }
        return preg_replace('/,\s$/', '', $string);
     }
     /*
     public function setBibtexUrl($string) {
        $this->bibtexUrl = "<a href=\"http://maiwald.zib.de/opus4-devel/citationExport/index/index/output/bibtex/docId/".$string."\">BibTeX</a>";
    }
      * */

    public function getBibtexUrl() {
        return $this->bibtexUrl;
    }


    public function setCompletedYear($string) {
        $this->completedYear = $string;
    }

    public function getCompletedYear() {
        return $this->completedYear;
    }

    public function addEditor($string) {
        array_push($this->editors, $string);
    }

    public function getEditors() {
        return $this->editors;
    }

    public function getEditorString() {
        $string = "";
        foreach ($this->editors as $editor) {
            $string .= $editor.", ";
        }
        return preg_replace('/,\s$/', '', $string);
     }

    public function setInstitution($string) {
        $this->instituition = $string;
    }

    public function getInstitution() {
        return $this->institution;
    }

    public function setIssue($string) {
        $this->issue = $string;
    }

    public function getIssue() {
        return $this->issue;
    }

    public function setPageFirst($string) {
        $this->pageFirst = $string;
    }

    public function getPageFirst() {
        return $this->pageFirst;
    }


    public function setPageLast($string) {
        $this->pageLast = $string;
    }

    public function getPageLast() {
        return $this->pageLast;
    }


    public function setPublisher($string) {
        $this->publisher = $string;
    }

    public function getPublisher() {
        return $this->publisher;
    }
    /*
     public function setRisUrl($string) {
        $this->risUrl = "<a href=\"http://maiwald.zib.de/opus4-devel/citationExport/index/index/output/ris/docId/".$string."\">RIS</a>";
    }
     *
     */

    public function getRisUrl() {
        return $this->risUrl;
    }       


    public function setSchool($string) {
        $this->school = $string;
    }

    public function getSchool() {
        return $this->title;
    }

    public function setTitle($string) {
        $this->title = $string;
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitleParent($string) {
        $this->titleParent = $string;
    }

    public function getTitleParent() {
        return $this->titleParent;
    }

    public function setVolume($string) {
        $this->volume = $string;
    }

    public function getVolume() {
        return $this->volume;
    }


    public function setYear($string) {
        $this->year = $string;
    }

    public function getYear() {
        return $this->year;
    }
}
?>
