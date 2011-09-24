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
 * @package     Module_Review
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Wrapper around Opus_Document to prepare presentation.
 *
 * TODO split off base class, URLs are controller specific
 * TODO move code to admin module (is used there as well and belongs there, or?)
 */
class Review_Model_DocumentAdapter {

    /**
     * Document identifier.
     * @var int
     */
    public $docId = null;

    /**
     * Wrapped document.
     * @var Opus_Document
     */
    public $document = null;

    /**
     * Zend_View for presentation.
     * @var Zend_View
     */
    private $view;

    /**
     * Array of author names.
     * @var array
     */
    private $authors = null;

    /**
     * Constructs wrapper around document.
     * @param Zend_View $view
     * @param int $id
     */
    public function __construct($view, $value) {
        $this->view = $view;
        if ($value instanceof Opus_Document) {
            $this->document = $value;
            $this->docId = $this->document->getId();
        }
        else {
            $this->docId = $value;
            $this->document = new Opus_Document( (int) $value);
        }
    }

    /**
     * Returns the Opus_Document object for this adapter.
     * @return Opus_Document
     */
    public function getDocument() {
        return $this->document;
    }

    /**
     * Returns document identifier.
     * @return int
     */
    public function getDocId() {
        return htmlspecialchars($this->docId);
    }

    /**
     * Returns state of document or 'undefined'.
     * @return string
     */
    public function getState() {
        try {
            return htmlspecialchars($this->document->getServerState());
        }
        catch (Exception $e) {
            return 'undefined';
        }
    }

    /**
     * Returns first title for document.
     * @return string
     */
    public function getDocTitle() {
        $titles = $this->document->getTitleMain();
        if (count($titles) > 0) {
            return $titles[0]->getValue();
        }
        else {
            return $this->view->translate('document_no_title') . '(id = ' . $this->getDocId() . ')';
        }
    }

    /**
     * Returns document type.
     * @return string
     */
    public function getDocType() {
        try {
            return htmlspecialchars($this->document->getType());
        }
        catch (Exception $e) {
            return 'undefined';
        }
    }

    /**
     * Return published date.
     *
     * TODO or should it be getPublishedYear (?)
     */
    public function getPublishedDate($yearOnly = false) {
        try {
            $date = $this->document->getPublishedDate();

            if (empty($date)) {
                $date = $this->document->getPublishedYear();
            }

            if ($yearOnly && !empty($date) && ($date instanceof Opus_Date)) {
                return htmlspecialchars($date);
            }
            else {
                return htmlspecialchars($date);
            }
        }
        catch (Exception $e) {
            return 'unknown';
        }
    }

    public function getCompletedDate($yearOnly = false) {
        try {
            $date = $this->document->getCompletedDate();

            if (empty($date)) {
                $date = $this->document->getCompletedYear();
            }

            if ($yearOnly && !empty($date) && ($date instanceof Opus_Date)) {
                return htmlspecialchars($date->getYear());
            }
            else {
                return htmlspecialchars($date);
            }
        }
        catch (Exception $e) {
            return 'unknown';
        }
    }

    public function getDate($yearOnly = false) {
        $date = $this->getCompletedDate($yearOnly);
        if (empty($date) || strcmp($date, 'unknown') === 0) {
            $date = $this->getPublishedDate($yearOnly);
        }
        if (empty($date) || strcmp($date, 'unknown') === 0) {
            $date = 'unknown';
        }
        return $date;
    }

    public function getYear() {
        $date = $this->getDate(true);
        return $date;
    }

    /**
     * Return list of authors.
     * @return array
     */
    public function getAuthors() {
        if ($this->authors) {
            return $this->authors;
        }

        try {
            $c = count($this->document->getPersonAuthor());
        }
        catch (Exception $e) {
            $c = 0;
        }

        $authors = array();

        for ($counter = 0; $counter < $c; $counter++) {

            $name = $this->document->getPersonAuthor($counter)->getName();
            $firstName = $this->document->getPersonAuthor($counter)->getFirstName();
            $lastName = $this->document->getPersonAuthor($counter)->getLastName();

            $author = array();

            $author['name'] = htmlspecialchars($name);
            $author['url'] = $this->getAuthorUrl($firstName . ' ' . $lastName);

            $authors[$counter] = $author;
        }

        $this->authors = $authors;

        return $authors;
    }

    /**
     * Returns the search URL for an author.
     */
    public function getAuthorUrl($author) {
        $author = str_replace(' ', '+', $author);
        $url = array(
            'module' => 'solrsearch',
            'controller' => 'index',
            'action' => 'search',
            'searchtype' => 'authorsearch',
            'author' => '"' . $author . '"');
        return $this->view->url($url, null, true);
    }

    /**
     * Returns the document state.
     * @return string
     */
    public function getDocState() {
        try {
            return $this->document->getServerState();
        }
        catch (Exception $e) {
            return 'undefined';
        }
    }

    /**
     * Returns true if the document is deleted.
     * @return boolean
     */
    public function isDeleted() {
        return ($this->getDocState() === 'deleted');
    }

    public function isPublished() {
        return ($this->getDocState() === 'published');
    }

    public function isUnpublished() {
        return ($this->getDocState() === 'unpublished');
    }

    public function hasFiles() {
        return $this->document->hasField('File');
    }


    public function getReviewer() {
        $return = array();
        foreach ($this->document->getEnrichment() AS $e) {
            if ($e->getKeyName() != 'reviewer.user_id') {
                continue;
            }
            $user_id = $e->getValue();
            $account = new Opus_Account($user_id);
            $return[$account->getId()] = $account->getLogin();
        }
        return $return;
    }

    public function getSubmitter() {
        $return = array();
        foreach ($this->document->getEnrichment() AS $e) {
            if ($e->getKeyName() != 'submitter.user_id') {
                continue;
            }
            $user_id = $e->getValue();
            $account = new Opus_Account($user_id);
            $return[$account->getId()] = $account->getLogin();
        }
        return $return;
    }
}
?>
