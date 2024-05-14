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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Account;
use Opus\Common\Date;
use Opus\Common\Document;
use Opus\Common\DocumentInterface;
use Opus\Common\Model\ModelException;
use Opus\Common\Model\NotFoundException;

/**
 * Wrapper around Document to prepare presentation.
 *
 * TODO split off base class, URLs are controller specific
 * TODO move code to admin module (is used there as well and belongs there, or?)
 * TODO remove dependency on View (and update unit tests accordingly)
 * TODO replace with view helpers
 */
class Application_Util_DocumentAdapter
{
    /**
     * Document identifier.
     *
     * @var int
     */
    public $docId;

    /**
     * Wrapped document.
     *
     * @var DocumentInterface
     */
    public $document;

    /**
     * Zend_View for presentation.
     *
     * @var Zend_View
     */
    private $view;

    /**
     * Array of author names.
     *
     * @var array
     */
    private $authors;

    /**
     * Constructs wrapper around document.
     *
     * @param Zend_View_Interface   $view
     * @param int|DocumentInterface $value
     */
    public function __construct($view, $value)
    {
        if ($view !== null) {
            $this->view = Zend_Registry::get('Opus_View');
        } else {
            $this->view = $view;
        }

        if ($value instanceof DocumentInterface) {
            $this->document = $value;
            $this->docId    = $this->document->getId();
        } else {
            $this->docId    = $value;
            $this->document = Document::get((int) $value);
        }
    }

    /**
     * Returns the Document object for this adapter.
     *
     * @return DocumentInterface
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Returns document identifier.
     *
     * @return string
     */
    public function getDocId()
    {
        return $this->docId !== null ? htmlspecialchars($this->docId) : '';
    }

    /**
     * Returns state of document or 'undefined'.
     *
     * @return string
     */
    public function getState()
    {
        try {
            return htmlspecialchars($this->document->getServerState());
        } catch (Exception $e) {
            return 'undefined';
        }
    }

    /**
     * Returns first title for document.
     *
     * @return string
     */
    public function getDocTitle()
    {
        $titles = $this->document->getTitleMain();
        if (count($titles) > 0) {
            return $titles[0]->getValue();
        } else {
            return $this->view->translate('results_missingtitle') . ' (id = ' . $this->getDocId() . ')';
        }
    }

    /**
     * Returns title in document language.
     *
     * @return string
     */
    public function getMainTitle()
    {
        $title = $this->document->getMainTitle();

        if ($title === null) {
            return $this->view->translate('results_missingtitle') . " (id = '{$this->getDocId()}')";
        } else {
            return $title->getValue();
        }
    }

    /**
     * Returns document type.
     *
     * @return string
     */
    public function getDocType()
    {
        try {
            return htmlspecialchars($this->document->getType() ?? '');
        } catch (Exception $e) {
            return 'undefined';
        }
    }

    /**
     * Return published date.
     *
     * TODO or should it be getPublishedYear (?)
     *
     * @param bool $yearOnly
     * @return string
     */
    public function getPublishedDate($yearOnly = false)
    {
        $datesHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('Dates');

        try {
            $date = $this->document->getPublishedDate();

            if (empty($date)) {
                $date = $this->document->getPublishedYear();
            }

            if (! empty($date) && $date instanceof Date) {
                if ($yearOnly) {
                    $date = $date->getYear();
                } else {
                    $date = $datesHelper->getDateString($date);
                }
            }
            return htmlspecialchars($date ?? '');
        } catch (Exception $e) {
            return 'unknown';
        }
    }

    /**
     * @param bool $yearOnly
     * @return string
     */
    public function getCompletedDate($yearOnly = false)
    {
        $datesHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('Dates');

        try {
            $date = $this->document->getCompletedDate();

            if (empty($date)) {
                $date = $this->document->getCompletedYear();
            }

            if (! empty($date) && $date instanceof Date) {
                if ($yearOnly) {
                    $date = $date->getYear();
                } else {
                    $date = $datesHelper->getDateString($date);
                }
            }

            return htmlspecialchars($date ?? '');
        } catch (Exception $e) {
            return 'unknown';
        }
    }

    /**
     * @param bool $yearOnly
     * @return string
     */
    public function getDate($yearOnly = false)
    {
        $date = $this->getCompletedDate($yearOnly);
        if (empty($date) || strcmp($date, 'unknown') === 0) {
            $date = $this->getPublishedDate($yearOnly);
        }
        if (empty($date) || strcmp($date, 'unknown') === 0) {
            $date = 'unknown';
        }
        return $date;
    }

    /**
     * @return string
     */
    public function getYear()
    {
        return $this->getDate(true);
    }

    /**
     * Return list of authors.
     *
     * @return array
     */
    public function getAuthors()
    {
        if ($this->authors) {
            return $this->authors;
        }

        $authorsInfo = [];

        $authors = $this->document->getPersonAuthor();

        foreach ($authors as $person) {
            $name      = $person->getName();
            $firstName = $person->getFirstName();
            $lastName  = $person->getLastName();

            $author = [];

            $author['name']   = htmlspecialchars($name);
            $author['url']    = $this->getAuthorUrl($firstName . ' ' . $lastName);
            $author['person'] = $person;

            $authorsInfo[] = $author;
        }

        $this->authors = $authorsInfo;

        return $authorsInfo;
    }

    /**
     * Returns the search URL for an author.
     *
     * @param string $author
     * @return string|null
     */
    public function getAuthorUrl($author)
    {
        if ($this->view !== null) {
            $author = str_replace(' ', '+', $author);
            $url    = [
                'module'     => 'solrsearch',
                'controller' => 'index',
                'action'     => 'search',
                'searchtype' => 'authorsearch',
                'author'     => '"' . $author . '"',
            ];
            return $this->view->url($url, null, true);
        } else {
            return null;
        }
    }

    /**
     * Returns the document state.
     *
     * @return string
     */
    public function getDocState()
    {
        try {
            return $this->document->getServerState();
        } catch (Exception $e) {
            return 'undefined';
        }
    }

    /**
     * @return bool
     *
     * TODO PHP7 on old systems getBelongsToBibliography returns strings ("0" or "1")
     */
    public function isBelongsToBibliography()
    {
        return filter_var($this->document->getBelongsToBibliography(), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Returns true if the document is deleted.
     *
     * @return bool
     */
    public function isDeleted()
    {
        return $this->getDocState() === 'deleted';
    }

    /**
     * @return bool
     */
    public function isPublished()
    {
        return $this->getDocState() === 'published';
    }

    /**
     * @return bool
     */
    public function isUnpublished()
    {
        return $this->getDocState() === 'unpublished';
    }

    /**
     * @return bool
     */
    public function hasFiles()
    {
        return count($this->document->getFile()) !== 0;
    }

    /**
     * @return int
     */
    public function getFileCount()
    {
        return count($this->document->getFile());
    }

    /**
     * @return array
     * @throws ModelException
     * @throws NotFoundException
     */
    public function getReviewer()
    {
        $return = [];
        foreach ($this->document->getEnrichment() as $e) {
            if ($e->getKeyName() !== 'reviewer.user_id') {
                continue;
            }
            $userId                    = $e->getValue();
            $account                   = Account::get($userId);
            $return[$account->getId()] = strtolower($account->getLogin());
        }
        return $return;
    }

    /**
     * @return array
     * @throws ModelException
     * @throws NotFoundException
     */
    public function getSubmitter()
    {
        $return = [];
        foreach ($this->document->getEnrichment() as $e) {
            if ($e->getKeyName() !== 'submitter.user_id') {
                continue;
            }
            $userId                    = $e->getValue();
            $account                   = Account::get($userId);
            $return[$account->getId()] = strtolower($account->getLogin());
        }
        return $return;
    }
}
