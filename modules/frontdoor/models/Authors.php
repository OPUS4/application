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

use Opus\Common\Document;
use Opus\Common\DocumentInterface;
use Opus\Common\Mail\SendMail;
use Opus\Common\Model\NotFoundException;

class Frontdoor_Model_Authors
{
    /** @var DocumentInterface */
    private $document;

    /**
     * @param int|DocumentInterface $arg Either an instance of Document or an int that is interpreted
     *                                   as a document ID
     * @throws Frontdoor_Model_Exception Thrown if no document with id $docId exists or requested document exists but
     *                                   is not in server_state published.
     */
    public function __construct($arg)
    {
        if ($arg instanceof DocumentInterface) {
            $this->document = $arg;
        } else {
            try {
                $this->document = Document::get($arg);
            } catch (NotFoundException $e) {
                throw new Frontdoor_Model_Exception('invalid value for parameter docId given', 0, $e);
            }
        }

        // check if document access is allowed
        // TODO document access check will be refactored in later releases
        try {
            new Application_Util_Document($this->document);
        } catch (Application_Exception $e) {
            throw new Frontdoor_Model_Exception('access to requested document is forbidden');
        }
    }

    /**
     * Returns all authors for the given document. Returns an empty array in case
     * no authors exists.
     *
     * @return array An array of authors for the given document.
     */
    public function getAuthors()
    {
        $authors = [];
        foreach ($this->document->getPersonAuthor() as $author) {
            $authorId = $author->getId();
            array_push(
                $authors,
                [
                    'id'        => $authorId[0],
                    'name'      => $author->getName(),
                    'mail'      => $author->getEmail(),
                    'allowMail' => $author->getAllowEmailContact(),
                ]
            );
        }
        return $authors;
    }

    /**
     * Returns all authors that are contactable via email for the given document.
     *
     * @return array An array of author names and email addresses in the form of
     * array ('id' => 123, 'name' => 'John Doe', 'mail' => 'doe@example.org', 'allowMail' => 0 or 1).
     * Returns an empty array if no authors exists or no author allows email conact.
     */
    public function getContactableAuthors()
    {
        $authors = [];
        foreach ($this->getAuthors() as $author) {
            if ($author['allowMail'] && ! empty($author['mail'])) {
                array_push($authors, $author);
            }
        }
        return $authors;
    }

    /**
     * Returns the underlying document that was given at object creation time.
     *
     * @return DocumentInterface
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Returns all contactable authors that were selected by the users. Ignores
     * all authors that are not contactable.
     *
     * @param array $checkboxSelection
     * @return array An array with elements of the form
     * array('address' => 'doe@example.org', 'name' => 'Doe') that can be used
     * without conversion as input for the last argument of SendMail:sendMail().
     */
    private function validateAuthorCheckboxInput($checkboxSelection)
    {
        $authors = [];
        foreach ($this->getContactableAuthors() as $author) {
            $authorId = $author['id'];
            if (array_key_exists($authorId, $checkboxSelection) && (int) $checkboxSelection[$authorId] === 1) {
                array_push($authors, ['address' => $author['mail'], 'name' => $author['name']]);
            }
        }
        return $authors;
    }

    /**
     * @param SendMail $mailProvider A class that provides mail service.
     * @param string   $from
     * @param string   $fromName
     * @param string   $subject
     * @param string   $bodyText
     * @param array    $authorSelection
     * @throws Frontdoor_Model_Exception
     */
    public function sendMail($mailProvider, $from, $fromName, $subject, $bodyText, $authorSelection)
    {
        try {
            $mailProvider->sendMail(
                $from,
                $fromName,
                $subject,
                $bodyText,
                $this->validateAuthorCheckboxInput($authorSelection)
            );
        } catch (Exception $e) {
            throw new Frontdoor_Model_Exception('failure while sending mail', null, $e);
        }
    }
}
