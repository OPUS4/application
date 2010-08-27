<?php
/*
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
 * @package     Application - Module Review
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Wrapper around Opus_Document to prepare presentation.
 *
 * TODO split off base class, URLs are controller specific
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
    public function __construct($view, $id) {
        $this->view = $view;
        $this->docId = $id;
        $this->document = new Opus_Document( (int) $id);
    }

    /**
     * Returns document identifier.
     * @return int
     */
    public function getDocId() {
        return $this->docId;
    }

    /**
     * Returns state of document or 'undefined'.
     * @return string
     */
    public function getState() {
        try {
            return $this->document->getServerState();
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
        try {
            return $this->document->getTitleMain(0)->getValue();
        }
        catch (Exception $e) {
            return $this->view->translate('document_no_title') . $id;
        }
    }

    /**
     * Returns frontdoor URL for document.
     * @return  url
     */
    public function getUrlFrontdoor() {
        $url_frontdoor = array(
            'module'     => 'frontdoor',
            'controller' => 'index',
            'action'     => 'index',
            'docId'      => $this->docId
        );
        return $this->view->url($url_frontdoor, 'default', true);
    }

    /**
     * Returns URL for editing document.
     * @return url
     */
    public function getUrlEdit() {
        $url_edit = array(
            'module'     => 'admin',
            'controller' => 'documents',
            'action'     => 'edit',
            'id'         => $this->docId
        );
        return $this->view->url($url_edit, 'default', true);
    }

    /**
     * Returns URL for deleting document.
     * @return url
     */
    public function getUrlDelete() {
        $url_delete = array (
            'module'     => 'admin',
            'controller' => 'documents',
            'action'     => 'delete',
            'id'         => $this->docId
        );
        return $this->view->url($url_delete, 'default', true);
    }

    /**
     * Returns URL for permanently deleting document.
     * @return url
     */
    public function getUrlPermanentDelete() {
        $url_permadelete = array (
            'module'     => 'admin',
            'controller' => 'documents',
            'action'     => 'permanentdelete',
            'id'         => $this->docId
        );
        return $this->view->url($url_permadelete, 'default', true);
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

            $authors[$counter] = $name;
        }

        $this->authors = $authors;

        return $authors;
    }

}

?>

