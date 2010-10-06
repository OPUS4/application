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
    private $doiUrl;
    private $imageAbstract;
    private $imageAbstractExternal;
    private $imageBibtex;
    private $imageBibtexExternal;
    private $imageDoi;
    private $imageDoiExternal;
    private $imageRis;
    private $imageRisExternal;
    private $imageUrl;
    private $imageUrlExternal;
    private $inSeries;
    private $issue;
    private $note;
    private $pageFirst;
    private $pageLast;
    private $pdfUrl;
    private $pdfUrlExternal;
    private $publishedYear;
    private $risUrl;
    private $risUrlExternal;
    private $titleAbstract;
    private $titleMain;
    private $titleParent;
    private $volume;
    
    public function __construct($id, $externalUrl = null) {
        $config = Zend_Registry::get('Zend_Config');

        $doc = new Opus_Document($id);
        $collections = $doc->getCollection();

        $fc = new Zend_Controller_Request_Http();
        $hostname = $fc->getHttpHost();

        $fc = Zend_Controller_Front::getInstance();
        $request = $fc->getRequest();
        $baseUrl = $request->getBaseUrl();

        $theme = null;
        if (isset($config->theme)) { $theme = $config->theme; }

        foreach ($doc->getPersonAuthor() as $author) {
             $firstName = $author->getFirstName();
             $lastName = $author->getLastName();
             $author = new PublicationList_Model_Author($firstName, $lastName);

             foreach ($collections as $c) {
                 $names = explode(", ", $c->getName());
		 if (strcmp($names[0], trim($lastName)) != 0) { continue; }
		 $first = trim(str_replace(".","", $firstName));
		 if (stripos($names[1], $first) === 0) {
                       $author->setUrl($baseUrl."/publicationList/index/index/id/".$c->getId());
                       if (!is_null($baseUrl)) {
                            $author->setUrlExternal($externalUrl.$c->getNumber());
                       }
                 }
             }
             $this->addAuthor($author);
        }

        $this->bibtexUrl = $baseUrl."/citationExport/index/index/output/bibtex/docId/".$doc->getId();
        $this->bibtexUrlExternal = "http://".$hostname.$this->bibtexUrl."/theme/plain";

        if ($doc->getCompletedYear() && ($doc->getCompletedYear() !== "0000") && ($doc->getCompletedYear() !== $doc->getPublishedYear())) {
            $this->completedYear = $doc->getCompletedYear();
        }

        foreach ($doc->getPersonEditor() as $editor) {
            $firstName = $editor->getFirstName();
            $lastName = $editor->getLastName();
            $this->addEditor($firstName." ".$lastName);
        }

        if (isset($config->publicationlist->doiresolver->url)) {
            if ($doc->getIdentifierDoi()) {
                $this->doiUrl = "http://".$config->publicationlist->doiresolver->url."/".$doc->getIdentifierDoi(0)->getValue();
            }
        }

        if ($doc->getIdentifierUrl()) {
            if (stripos($this->pdfUrl, "http:") === 0) {
                $this->pdfUrl = $doc->getIdentifierUrl(0)->getValue();
            } 
        }

       
        $this->imageAbstract = $baseUrl."/layouts/".$theme."/img/publicationlist/Abstract_icon.png";
        $this->imageAbstractExternal = "../../img/Abstract_icon.png";
        $this->imageBibtex = $baseUrl."/layouts/".$theme."/img/publicationlist/BibTeX_icon.png";
        $this->imageBibtexExternal = "../../img/BibTeX_icon.png";
        $this->imagePdf = $baseUrl."/layouts/".$theme."/img/publicationlist/PDF_icon.png";
        $this->imagePdfExternal = "../../img/PDF_icon.png";
	$this->imageDoi = $baseUrl."/layouts/".$theme."/img/publicationlist/DOI_icon.png";
        $this->imageDoiExternal = "../../img/DOI_icon.png";
        $this->imageRis = $baseUrl."/layouts/".$theme."/img/publicationlist/RIS_icon.png";
        $this->imageRisExternal = "../../img/RIS_icon.png";


        foreach ($collections as $c) {
            $roleId = $c->getRoleId();
            $role = new Opus_CollectionRole($roleId);
            if ($role->getName() === 'series') {
                $this->inSeries = "Erschienen als ".$c->getName();
                $this->pdfUrl = $baseUrl."/frontdoor/index/index/docId/".$doc->getId();
                $this->pdfUrlExternal = "http://".$hostname.$this->pdfUrl."/theme/plain";
            }
        }
        
        if ($doc->getIssue()) {
            $this->issue = $doc->getIssue();
        }

        if ($doc->getNote()) {
            $note = $doc->getNote(0)->getMessage();
            if (strcmp($note, 'printed version not available') === 0) { 
                /* TODO: muss im Import abgefangen werden */
                $note = str_replace("(","", $note);
                $note = str_replace(")","", $note);
                $this->note = $note;
            }
        }

        if ($doc->getPageFirst()) {
            $this->pageFirst = $doc->getPageFirst();
        }

        if ($doc->getPageLast()) {
            $this->pageLast = $doc->getPageLast();
        }

        $this->risUrl = $baseUrl."/citationExport/index/index/output/ris/docId/".$doc->getId();
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

    public function setImageAbstract($string) {
        $this->imageAbstract = $string;
    }

    public function setImageBibtex($string) {
        $this->imageBibtex = $string;
    }

    public function setImageDoi($string) {
        $this->imageDoi = $string;
    }
    
    public function setImagePdf($string) {
        $this->imagePdf = $string;
    }

    public function setImageRis($string) {
        $this->imageRis = $string;
    }

    public function setPdfUrl($string) {
        $this->pdfUrl = $string;
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

    public function getDoiUrl() {
        return $this->doiUrl;
    }

    public function getEditors() {
        return $this->editors;
    }

    public function getImageAbstract() {
        return $this->imageAbstract;
    }

    public function getImageAbstractExternal() {
        return $this->imageAbstractExternal;
    }

    public function getImageBibtex() {
        return $this->imageBibtex;
    }

    public function getImageBibtexExternal() {
        return $this->imageBibtexExternal;
    }

    public function getImageDoi() {
        return $this->imageDoi;
    }

    public function getImageDoiExternal() {
        return $this->imageDoiExternal;
    }

    public function getImagePdf() {
        return $this->imagePdf;
    }

    public function getImagePdfExternal() {
        return $this->imagePdfExternal;
    }

    public function getImageRis() {
        return $this->imageRis;
    }

    public function getImageRisExternal() {
        return $this->imageRisExternal;
    }

    public function getInSeries() {
        return $this->inSeries;
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

    public function getPdfUrl() {
        return $this->pdfUrl;
    }

    public function getPdfUrlExternal() {
        return $this->pdfUrlExternal;
    }

    public function getPublishedYear() {
        return $this->publishedYear;
    }

    public function getRisUrl() {
        return $this->risUrl;
    }

    public function getRisUrlExternal() {
        return $this->risUrlExternal;
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
