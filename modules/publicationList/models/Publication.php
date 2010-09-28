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

    private $doc;
    private $authors = array();
    private $bibtexUrl;
    private $editors = array();
    private $risUrl;

    public function __construct($id) {
        $this->doc = new Opus_Document($id);
        $collections = $this->doc->getCollection();

        foreach ($this->doc->getPersonAuthor() as $author) {
             $firstName = $author->getFirstName();
             $lastName = $author->getLastName();
             $author = new PublicationList_Model_Author($firstName, $lastName);

             foreach ($collections as $c) {
                   if (strcmp($c->getName(), $lastName.", ".$firstName) === 0) {
                       $author->setUrl($c->getId());
                       $author->setIdentifier($c->getNumber());
                   }
             }
             $this->addAuthor($author);
        }

        foreach ($this->doc->getPersonEditor() as $editor) {
              $firstName = $editor->getFirstName();
              $lastName = $editor->getLastName();
              $this->addEditor($firstName." ".$lastName);
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

    public function getDoc() {
        return $this->doc;
    }

    public function addAuthor($author) {
        array_push($this->authors, $author);
    }

    public function getAuthors() {
        return $this->authors;
    }

    public function addEditor($string) {
        array_push($this->editors, $string);
    }

    public function getEditors() {
        return $this->editors;
    }

    public function getRisUrl() {
        return $this->risUrl;
    }
    
    public function getBibtexUrl() {
        return $this->bibtexUrl;
    }

}
?>
