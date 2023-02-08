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
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Model für das Speichern von Informationen in der Session während des Editierens eines Dokuments.
 */
class Admin_Model_DocumentEditSession extends Application_Model_Abstract
{
    /** @var int Dokument-ID. */
    private $docId;

    /** @var string Name für allgemeinen Session Namespace. */
    private $namespace = 'admin';

    /** @var Zend_Session_Namespace Allgemeiner Session Namespace. */
    private $session;

    /**
     * Session Namespaces fuer einzelne Dokument.
     *
     * Wenn beim Editieren der Metadaten eines Dokuments auf eine andere Seite gewechselt wird (Collections, Personen),
     * wird der letzte POST in einem Namespace für eine Dokumenten-ID abgespeichert, um den Zustand des Formulares
     * wieder herstellen zu können, wenn zur Formularseite zurück gewechselt wird.
     *
     * @var array
     *
     * TODO Review solution (Wie funktioniert Namespace Bereinigung?)
     */
    private $documentNamespaces = [];

    /**
     * Konstruiert Model für Zugriff auf Edit Session eines Dokuments.
     *
     * @param int $documentId Dokument-ID
     * @throws InvalidArgumentException Wenn $documentId keine Zahl oder kleiner als 1 ist.
     */
    public function __construct($documentId)
    {
        if (is_numeric($documentId) && $documentId > 0) {
            $this->docId = $documentId;
        } else {
            // should never happen
            throw new InvalidArgumentException(__CLASS__ . " mit document ID '$documentId' aufgerufen.");
        }
    }

    /**
     * Fügt eine Person zur List der Personen, die dem Metadaten-Formular hinzugefügt werden müssen.
     *
     * @param array $linkProps
     */
    public function addPerson($linkProps)
    {
        $namespace = $this->getDocumentSessionNamespace();

        if (isset($namespace->addedPersons)) {
            $persons = $namespace->addedPersons;
        } else {
            $persons = [];
        }

        $persons[] = $linkProps;

        $namespace->addedPersons = $persons;
    }

    /**
     * Liefert die Liste der Personen, die dem Metadaten-Formular hinzugefügt werden müssen.
     *
     * @return array
     */
    public function retrievePersons()
    {
        $namespace = $this->getDocumentSessionNamespace();

        if (isset($namespace->addedPersons)) {
            $persons                 = $namespace->addedPersons;
            $namespace->addedPersons = null;
        } else {
            $persons = [];
        }

        return $persons;
    }

    /**
     * Liefert die Anzahl der in der Session gespeicherten Personen-Links.
     *
     * @return int
     */
    public function getPersonCount()
    {
        $namespace = $this->getDocumentSessionNamespace();

        if (isset($namespace->addedPersons)) {
            return count($namespace->addedPersons);
        } else {
            return 0;
        }
    }

    /**
     * Speichert POST in session.
     *
     * @param array       $post
     * @param string|null $name
     */
    public function storePost($post, $name = null)
    {
        $namespace = $this->getDocumentSessionNamespace();

        if ($name === null) {
            $name = 'lastPost';
        }

        $namespace->$name = $post;
    }

    /**
     * Liefert gespeicherten POST.
     *
     * @param string|null $name
     * @return array|null
     */
    public function retrievePost($name = null)
    {
        $namespace = $this->getDocumentSessionNamespace();

        // TODO BUG no matter $name is provided here it becomes 'lastPost'
        if ($name === null) {
            $name = 'lastPost';
        }

        if (isset($namespace->$name)) {
            $post             = $namespace->$name;
            $namespace->$name = null;
            return $post;
        } else {
            return null;
        }
    }

    /**
     * Liefert Session Namespace fuer diesen Controller.
     *
     * @return Zend_Session_Namespace
     */
    public function getSessionNamespace()
    {
        if (null === $this->session) {
            $this->session = new Zend_Session_Namespace($this->namespace);
        }

        return $this->session;
    }

    /**
     * Liefert Session Namespace fuer einzelnes Dokument.
     *
     * @return Zend_Session_Namespace
     */
    public function getDocumentSessionNamespace()
    {
        $key = 'doc' . $this->docId;

        if (! array_key_exists($key, $this->documentNamespaces)) {
            $namespace                      = new Zend_Session_Namespace($key);
            $this->documentNamespaces[$key] = $namespace;
        } else {
            $namespace = $this->documentNamespaces[$key];
        }

        return $namespace;
    }

    /**
     * Gibt die Dokument-ID für das Model zurück.
     *
     * @return int
     */
    public function getDocumentId()
    {
        return $this->docId;
    }
}
