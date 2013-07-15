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
 * @category    Application
 * @package     Module_Admin
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Model für das Speichern von Informationen in der Session während des Editierens eines Dokuments.
 */
class Admin_Model_DocumentEditSession extends Admin_Model_AbstractModel {
    
    private $docId;
    
    /**
     * Name für allgemeinen Session Namespace.
     * @var type 
     */
    private $__namespace = 'admin';

    /**
     * Allgemeiner Session Namespace.
     * @Zend_Session_Namespace type 
     */
    private $__session;

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
    private $__documentNamespaces = array();
    
    public function __construct($documentId) {
        $this->docId = $documentId;
    }
    
    
    /**
     * Fügt eine Person zur List der Personen, die dem Metadaten-Formular hinzugefügt werden müssen.
     * @param type $form
     */
    public function addPerson($linkProps) {
        $namespace = $this->getDocumentSessionNamespace();
        
        if (isset($namespace->addedPersons)) {
            $persons = $namespace->addedPersons;
        }
        else {
            $persons = array();
        }
        
        $persons[] = $linkProps;
        
        $namespace->addedPersons = $persons;
    }
    
    /**
     * Liefert die Liste der Personen, die dem Metadaten-Formular hinzugefügt werden müssen.
     */
    public function retrievePersons() {
        $namespace = $this->getDocumentSessionNamespace();
        
        if (isset($namespace->addedPersons)) {
            $persons = $namespace->addedPersons;
            $namespace->addedPersons = null;
        }
        else {
            $persons = array();
        }
        
        return $persons;
    }
    
    public function getPersonCount() {
        $namespace = $this->getDocumentSessionNamespace();
        
        if (isset($namespace->addedPersons)) {
            return count($namespace->addedPersons);
        }
        else {
            return 0;
        }
    }
    
    /**
     * Speichert POST in session.
     * @param array $post
     */
    public function storePost($post) {
        $namespace = $this->getDocumentSessionNamespace();
        
        $namespace->lastPost = $post;
    }
    
    /**
     * Liefert gespeicherten POST.
     * @param string $hash Hash für Formular
     * @return array
     */
    public function retrievePost() {
        $namespace = $this->getDocumentSessionNamespace();
        
        if (isset($namespace->lastPost)) {
            $post = $namespace->lastPost;
            $namespace->lastPost = null;
            return $post;
        }
        else {
            return null;
        }
    }
    
    /**
     * Liefert Session Namespace fuer diesen Controller.
     * @return Zend_Session_Namespace
     */
    public function getSessionNamespace() {
        if (null === $this->__session) {
            $this->__session = new Zend_Session_Namespace($this->__namespace);
        }
 
        return $this->__session;        
    }
    
    /**
     * Liefert Session Namespace fuer einzelnes Dokument.
     * @return Zend_Session_Namespace
     */
    public function getDocumentSessionNamespace() {
        $key = 'doc' . $this->docId;
        
        if (!array_key_exists($key, $this->__documentNamespaces)) {
            $namespace = new Zend_Session_Namespace($key);
            $this->__documentNamespaces[$key] = $namespace;
        }
        else {
            $namespace = $this->__documentNamespaces[$key];
        }
 
        return $namespace;        
    }
    
}
