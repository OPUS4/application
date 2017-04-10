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
 * @package     Module_Admin
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Controller fuer die Verwaltung von Personen.
 *
 * Dieser Controller enthaelt Funktionen fuer das Anlegung und Editieren von Personen. Er wird im Zusammespiel mit dem
 * DocumentController verwendet um Personen für ein Dokument zu manipulieren.
 *
 * TODO Erweitern um Personen in Datenbank zu verwalten (z.B. Deduplizieren) (OPUSVIER-nnnn, noch kein Ticket)
 */
class Admin_PersonController extends Application_Controller_Action {

    private $_documentsHelper;

    private $_dates;

    /**
     * Initializes controller.
     */
    public function init()
    {
        parent::init();

        $this->_documentsHelper = $this->_helper->getHelper('Documents');
        $this->_dates = $this->_helper->getHelper('Dates');
    }

    /**
     * List persons.
     *
     * If a parameter has an invalid value, the parameter is removed and a redirect is used to clean up the URL.
     *
     * If a parameter is missing a default value is used.
     */
    public function indexAction()
    {
        $redirect = false;

        // check limit parameter
        $limit = $this->getParam('limit');

        if ((!ctype_digit($limit) || $limit <= 0) && !is_null($limit))
        {
            $limit = null;
            $redirect = true;
        }

        // check role parameter
        $role = $this->getParam('role');
        $allowedRoles = array_merge(array('all'), Admin_Form_Document_Persons::getRoles());

        // TODO redirect for 'all' (since it is default)
        if ((!ctype_alpha($role) || !in_array(strtolower($role), $allowedRoles)) && !is_null($role))
        {
            $role = null;
            $redirect = true;
        }

        // check page parameter
        $page = $this->getParam('page');

        if ((!ctype_digit($page) || $page <= 0) && !is_null($page))
        {
            $page = null;
            $redirect = true;
        }

        // get filter parameter
        $filter = $this->getParam('filter');

        // redirect to get Zend style URL for bookmarking or fixing bad parameters
        if ($this->getRequest()->isPost() || $redirect)
        {
            $redirectParams = array('role' => $role, 'limit' => $limit, 'filter' => $filter, 'page' => $page);

            $redirectParams = array_filter($redirectParams, function($value) {
                return !is_null($value) && strlen(trim($value)) > 0;
            });

            $this->_helper->getHelper('Redirector')->gotoSimple(
                'index', 'person', 'admin', $redirectParams
            );

            return;
        }

        if (is_null($limit))
        {
            $limit = 50;
        }

        if ($role === 'all') {
            $role = null;
        }

        if (!is_null($page)) {
            $page = $this->getParam('page', 1);
            $start = ($page - 1) * $limit + 1;
        }
        else {
            $start = 1;
        }


        // TODO only include 'limit' and 'start' if provided as URL parameters (not defaults)
        $form = new Admin_Form_PersonListControl();
        $form->setMethod(Zend_Form::METHOD_POST);
        // TODO only include limit if not default
        $form->setAction($this->view->url(
            array(
                'module' => 'admin', 'controller' => 'person', 'action' => 'index',
                'limit' => $limit
            ), null, true
        ));
        $form->setName('persons');
        $form->setIsArray(false);

        $params = $this->getRequest()->getParams();
        $form->populate($params);

        // TODO move into replaceable model class
        $personsTotal = Opus_Person::getAllPersonsCount($role, $filter);

        if ($start > $personsTotal)
        {
            if ($personsTotal > 0 && ($personsTotal > $limit))
            {
                $start = intdiv($personsTotal, $limit) * $limit;
            }
            else {
                $start = 1;
            }
        }

        $page = intdiv($start, $limit) + 1;

        $persons = Opus_Person::getAllPersons($role, $start - 1, $limit, $filter);

        $this->view->headScript()->appendFile($this->view->layoutPath() . '/js/admin.js');

        $end = $start + $limit - 1;

        if ($end > $personsTotal)
        {
            $end = $personsTotal;
        }

        $paginator = Zend_Paginator::factory(( int )$personsTotal);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($limit);

        $this->view->paginator = $paginator;
        $this->view->role = $role;
        $this->view->filter = $filter;
        $this->view->limit = $limit;
        $this->view->start = $start;
        $this->view->end = $end;
        $this->view->totalCount = $personsTotal;
        $this->view->form = $form;
        $this->view->persons = $persons;
    }

    /**
     * Listing documents for a person.
     */
    public function documentsAction()
    {
        $columns = array('last_name', 'first_name', 'identifier_orcid', 'identifier_gnd', 'identifier_misc');

        $person = array();

        foreach ($columns as $name)
        {
            if ($this->hasParam($name))
            {
                $person[$name] = $this->getParam($name);
            }
        }

        $documents = Opus_Person::getPersonDocuments($person);

        $this->view->documents = $documents;
    }

    /**
     * Show edit form for a person.
     */
    public function editAction()
    {

    }

    /**
     * Fuegt Person zu Dokument hinzu.
     *
     * HTTP Parameter:
     * - Dokument-ID (document)
     * - Rolle (role)
     */
    public function assignAction()
    {
        $docId = $this->getRequest()->getParam('document');

        $document = $this->_documentsHelper->getDocumentForId($docId);

        if (!isset($document)) {
            return $this->_redirectTo(
                'index', array('failure' => 'admin_document_error_novalidid'),
                'documents', 'admin'
            );
        }

        if (!$this->getRequest()->isPost()) {
            // Neues Formular anzeigen
            $form = new Admin_Form_Document_PersonAdd();

            $role = $this->getRequest()->getParam('role', 'author');
            $form->setSelectedRole($role);

            $this->view->form = $form;
        }
        else {
            // POST verarbeiten
            $post = $this->getRequest()->getPost();

            $form = new Admin_Form_Document_PersonAdd();

            $form->populate($post);

            $result = $form->processPost($post, $post);

            switch ($result) {
                case Admin_Form_Document_PersonAdd::RESULT_SAVE:
                case Admin_Form_Document_PersonAdd::RESULT_NEXT:
                    if ($form->isValid($post)) {
                        $person = $form->getModel();
                        $person->store();

                        $linkProps = $form->getPersonLinkProperties($person->getId());
                        $editSession = new Admin_Model_DocumentEditSession($docId);

                        if ($result == Admin_Form_Document_PersonAdd::RESULT_SAVE) {
                            // Zurück zum Metadaten-Formular springen
                            if ($editSession->getPersonCount() > 0) {
                                // Link Informationen durch Session übermitteln
                                $editSession->addPerson($linkProps);
                                return $this->_redirectToAndExit(
                                    'edit', null, 'document', 'admin', array(
                                    'id' => $docId, 'continue' => 'addperson')
                                );
                            }
                            else {
                                // Link Informationen direkt als Parameter übergeben
                                return $this->_redirectToAndExit(
                                    'edit', null, 'document', 'admin', array_merge(
                                        array(
                                        'id' => $docId, 'continue' => 'addperson'), $linkProps
                                    )
                                );
                            }
                        }
                        else {
                            // Person in Session merken
                            $editSession->addPerson($linkProps);
                            // Neues Formular erzeugen
                            $role = $form->getSelectedRole();
                            $form = new Admin_Form_Document_PersonAdd();
                            $form->setSelectedRole($role);
                        }
                    }
                    else {
                        // TODO Validierungsfehlernachricht für Formular anzeigen
                        $form->addError($this->view->translate('admin_document_error_validation'));
                    }
                    break;
                case Admin_Form_Document_PersonAdd::RESULT_CANCEL:
                    // Aktuelle Person nicht speichern, aber eventuell gemerkte Personen hinzufügen
                    return $this->_redirectToAndExit(
                        'edit', null, 'document', 'admin', array(
                        'id' => $docId, 'continue' => 'addperson')
                    );
                default:
                    break;
            }

            $this->view->form = $form;
        }

        $this->view->document = $document;
        $this->view->documentAdapter = new Application_Util_DocumentAdapter($this->view, $document);

        // Beim wechseln der Sprache würden Änderungen in editierten Felder verloren gehen
        $this->view->languageSelectorDisabled = true;
        $this->view->breadcrumbsDisabled = true;
    }

    /**
     * Action zum Editieren einer Person, die mit einem Dokument verknüpft ist.
     *
     * Wird über den Edit-Link für eine Person im Metadatenformular eines Dokuments aufgerufen.
     */
    public function editlinkedAction() {
        $docId = $this->getRequest()->getParam('document');

        $document = $this->_documentsHelper->getDocumentForId($docId);

        if (!isset($document)) {
            return $this->_redirectTo(
                'index', array('failure' => 'admin_document_error_novalidid'),
                'documents', 'admin'
            );
        }

        $form = new Admin_Form_Person();

        if (!$this->getRequest()->isPost()) {
            // Formular anzeigen
            $personId = $this->getRequest()->getParam('personId');

            if (strlen(trim($personId)) == 0 || is_null($personId)) {
                $this->getLogger()->err(__METHOD__ . ' No personId parameter.');
                return $this->returnToMetadataForm($docId);
            }

            if (!is_numeric($personId)) {
                $this->getLogger()->err(__METHOD__ . " Bad personId = '$personId' parameter.");
                return $this->returnToMetadataForm($docId);
            }

            try {
                $person = new Opus_Person($personId);
            }
            catch (Opus_Model_NotFoundException $omnfe) {
                $this->getLogger()->err(__METHOD__ . ' ' . $omnfe->getMessage());
                return $this->returnToMetadataForm($docId);
            }

            $form->populateFromModel($person);

            $this->view->form = $form;
        }
        else {
            // POST verarbeiten
            $post = $this->getRequest()->getPost();

            $form->populate($post);

            $result = $form->processPost($post, $post);

            switch ($result) {
                case Admin_Form_Person::RESULT_SAVE:
                    if ($form->isValid($post)) {
                        $person = $form->getModel();
                        $person->store();
                        return $this->_redirectToAndExit(
                            'edit', null, 'document', 'admin', array('id' => $docId,
                            'continue' => 'updateperson',
                            'person' => $person->getId()
                            )
                        );
                    }
                    else {
                        // TODO Validierungsfehlernachricht für Formular anzeigen (notwendig?)
                    }
                    break;
                case Admin_Form_Person::RESULT_CANCEL:
                    // Person nicht speichern
                    return $this->returnToMetadataForm($docId);
                    break;
                default:
                    break;
            }

            $this->view->form = $form;
        }

        $this->view->document = $document;
        $this->view->documentAdapter = new Application_Util_DocumentAdapter($this->view, $document);

        // Beim wechseln der Sprache würden Änderungen in editierten Felder verloren gehen
        $this->view->languageSelectorDisabled = true;
        $this->view->breadcrumbsDisabled = true;
    }

    /**
     * Führt Redirect zum Metadatenformular des Dokuments aus.
     * @param $docId Dokument-ID
     */
    public function returnToMetadataForm($docId)
    {
        return $this->_redirectToAndExit(
            'edit', null, 'document', 'admin', array('id' => $docId,
            'continue' => 'true')
        );
    }

}
