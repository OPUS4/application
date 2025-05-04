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

use Opus\Common\Model\NotFoundException;
use Opus\Common\Person;

/**
 * Controller fuer die Verwaltung von Personen.
 *
 * Dieser Controller enthaelt Funktionen fuer das Anlegung und Editieren von Personen. Er wird im Zusammespiel mit dem
 * DocumentController verwendet um Personen für ein Dokument zu manipulieren.
 *
 * TODO Erweitern um Personen in Datenbank zu verwalten (z.B. Deduplizieren) (OPUSVIER-nnnn, noch kein Ticket)
 */
class Admin_PersonController extends Application_Controller_Action
{
    public const SESSION_NAMESPACE = 'Person';

    /** @var Application_Controller_Action_Helper_Documents */
    private $documentsHelper;

    /**
     * Initializes controller.
     */
    public function init()
    {
        parent::init();

        $this->documentsHelper              = $this->_helper->getHelper('Documents');
        $this->view->contentWrapperDisabled = true;
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

        if ($limit !== null && (! ctype_digit($limit) || $limit <= 0)) {
            $limit    = null;
            $redirect = true;
        }

        // check role parameter
        $role         = $this->getParam('role');
        $allowedRoles = array_merge(['all'], Admin_Form_Document_Persons::getRoles());

        // TODO redirect for 'all' (since it is default)
        if ($role !== null && (! ctype_alpha($role) || ! in_array(strtolower($role), $allowedRoles))) {
            $role     = null;
            $redirect = true;
        }

        // check page parameter
        $page = $this->getParam('page');

        if ($page !== null && (! ctype_digit($page) || $page <= 0)) {
            $page     = null;
            $redirect = true;
        }

        // get filter parameter
        $filter = $this->getParam('filter');

        // redirect to get Zend style URL for bookmarking or fixing bad parameters
        if ($this->getRequest()->isPost() || $redirect) {
            $redirectParams = ['role' => $role, 'limit' => $limit, 'filter' => $filter, 'page' => $page];

            $redirectParams = array_filter($redirectParams, function ($value) {
                return $value !== null && strlen(trim($value)) > 0;
            });

            $this->_helper->getHelper('Redirector')->gotoSimple(
                'index',
                'person',
                'admin',
                $redirectParams
            );

            return;
        }

        if ($limit === null) {
            $limit = 50;
        }

        if ($role === 'all') {
            $role = null;
        }

        if ($page !== null) {
            $page  = $this->getParam('page', 1);
            $start = ($page - 1) * $limit + 1;
        } else {
            $start = 1;
        }

        // TODO only include 'limit' and 'start' if provided as URL parameters (not defaults)
        $form = new Admin_Form_PersonListControl();
        $form->setMethod(Zend_Form::METHOD_POST);

        // TODO only include limit if not default
        $form->setAction($this->view->url(
            [
                'module'     => 'admin',
                'controller' => 'person',
                'action'     => 'index',
                'limit'      => $limit,
            ],
            null,
            true
        ));
        $form->setName('persons');
        $form->setIsArray(false);

        $params = $this->getRequest()->getParams();
        $form->populate($params);

        $persons = Person::getModelRepository();

        // TODO move into replaceable model class
        $personsTotal = $persons->getAllPersonsCount($role, $filter);

        if ($start > $personsTotal) {
            if ($personsTotal > 0 && ($personsTotal > $limit)) {
                $start = intdiv($personsTotal, $limit) * $limit;
            } else {
                $start = 1;
            }
        }

        $page = intdiv($start, $limit) + 1;

        $persons = $persons->getAllPersons($role, $start - 1, $limit, $filter);

        $this->view->headScript()->appendFile($this->view->layoutPath() . '/js/admin.js');

        $end = $start + $limit - 1;

        if ($end > $personsTotal) {
            $end = $personsTotal;
        }

        $paginator = Zend_Paginator::factory((int) $personsTotal);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($limit);

        $this->view->paginator  = $paginator;
        $this->view->role       = $role;
        $this->view->filter     = $filter;
        $this->view->limit      = $limit;
        $this->view->start      = $start;
        $this->view->end        = $end;
        $this->view->totalCount = $personsTotal;
        $this->view->form       = $form;
        $this->view->persons    = $persons;
    }

    /**
     * Listing documents for a person.
     */
    public function documentsAction()
    {
        $person = $this->getPersonCrit();

        $persons = Person::getModelRepository();

        $documents = $persons->getPersonDocuments($person);

        $this->view->documents = $documents;
    }

    /**
     * Show edit form for a person.
     *
     * TODO clean up the workflow logic (should be expandable to multiple steps)
     */
    public function editAction()
    {
        $this->view->languageSelectorDisabled = true;

        $request = $this->getRequest();

        $person = $this->getPersonCrit();

        // check if parameters identifying person are provided
        if (empty($person)) {
            $this->_helper->Redirector->redirectTo(
                'index',
                null // TODO array('failure' => 'parameters missing (TODO translate)')
            );
        }

        $personRepository = Person::getModelRepository();

        $personValues = $personRepository->getPersonValues($person);

        if ($personValues === null) {
            $this->_helper->Redirector->redirectTo(
                'index',
                ['failure' => 'admin_person_error_not_found']
            );
        }

        $data = null;

        $processForm = false;

        if ($request->isPost()) {
            $data        = $request->getPost();
            $processForm = true;
        } elseif ($request->getParam('step') === 'Back') {
            $formId = $this->getParam('formId');

            // check if the request is coming from the 'Back' button of the confirmation form
            $session = new Zend_Session_Namespace(self::SESSION_NAMESPACE);

            if (isset($session->{$formId})) {
                $data = $session->{$formId};
                unset($session->{$formId});
            }
        }

        if ($data !== null) {
            $form = new Admin_Form_Persons();
            $form->setPerson($person);

            $form->populate($data);

            if ($processForm) {
                $result = $form->processPost($data, $data);

                switch ($result) {
                    case Admin_Form_Persons::RESULT_SAVE:
                        if ($form->isValid($data)) {
                            $formId = $form->getElement(Admin_Form_Persons::ELEMENT_FORM_ID)->getValue();

                            // TODO store data in session for back button
                            $personNamespace            = new Zend_Session_Namespace(self::SESSION_NAMESPACE);
                            $personNamespace->{$formId} = $data;

                            $changes = $form->getChanges();

                            // TODO find better way to access convertToFieldNames or avoid it entirely
                            $personModel = Person::new();

                            $confirmForm = new Admin_Form_PersonsConfirm();
                            $confirmForm->getElement(Admin_Form_PersonsConfirm::ELEMENT_FORM_ID)->setValue($formId);
                            $confirmForm->setOldValues($personModel->convertToFieldNames($personValues));
                            $confirmForm->populateFromModel($person);
                            $confirmForm->setChanges($personModel->convertToFieldNames($changes));
                            $confirmForm->setAction($this->view->url([
                                'module'     => 'admin',
                                'controller' => 'person',
                                'action'     => 'update',
                            ], null, false));

                            $this->renderForm($confirmForm);
                            return;
                        }
                        break;
                    case Admin_Form_Persons::RESULT_CANCEL:
                        $this->_helper->Redirector->redirectTo('index', null);
                        return;
                }
            }
        } else {
            $form = new Admin_Form_Persons();
            $data = [];
        }

        $form->populateFromModel($personValues);
        $form->populate($data);

        $this->renderForm($form);
    }

    public function updateAction()
    {
        $this->view->languageSelectorDisabled = true;

        $request = $this->getRequest();

        $person = $this->getPersonCrit();

        if ($request->isPost()) {
            $form = new Admin_Form_PersonsConfirm();
            $form->populateFromModel($person); // TODO setPerson instead (rename)?

            $data = $request->getPost();

            $form->populate($data);

            $result = $form->processPost($data, $data);

            switch ($result) {
                case Admin_Form_PersonsConfirm::RESULT_BACK:
                    // redirect back to edit form (it will get the form data from the session)
                    $formId = $form->getElementValue(Admin_Form_PersonsConfirm::ELEMENT_FORM_ID);
                    $this->_helper->Redirector->redirectTo(
                        'edit',
                        null,
                        'person',
                        'admin',
                        array_merge($person, [
                            'step'   => 'Back',
                            'formId' => $formId,
                        ])
                    );
                    break;
                case Admin_Form_PersonsConfirm::RESULT_SAVE:
                    $formId = $form->getElementValue(Admin_Form_PersonsConfirm::ELEMENT_FORM_ID);

                    // make changes in database and redirect to list of persons with success message
                    $session = new Zend_Session_Namespace(self::SESSION_NAMESPACE);

                    if (isset($session->{$formId})) {
                        $formData = $session->{$formId};
                        unset($session->{$formId});

                        if (! empty($formData)) {
                            $personForm = new Admin_Form_Persons();
                            $personForm->populate($formData);
                            $changes   = $personForm->getChanges();
                            $documents = $form->getDocuments();

                            $persons = Person::getModelRepository();
                            $persons->updateAll($person, $changes, $documents);

                            $message = 'admin_person_bulk_update_success';
                        }
                    } else {
                        $message = 'admin_person_bulk_update_failure';
                    }

                    // TODO success or failure message
                    // TODO do another validation here?
                    $this->_helper->Redirector->redirectTo('index', $message);
                    break;
                case Admin_Form_PersonsConfirm::RESULT_CANCEL:
                    // go back to list of persons
                    $this->_helper->Redirector->redirectTo('index', null);
                    break;
            }
        }
    }

    /**
     * Builds an array for identifying person from parameters.
     *
     * @return array
     *
     * TODO move into model
     */
    public function getPersonCrit()
    {
        $columns = ['last_name', 'first_name', 'identifier_orcid', 'identifier_gnd', 'identifier_misc'];

        $person = [];

        foreach ($columns as $name) {
            if ($this->hasParam($name)) {
                $person[$name] = $this->getParam($name);
            }
        }

        return $person;
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

        $document = $this->documentsHelper->getDocumentForId($docId);

        if (! isset($document)) {
            $this->_helper->Redirector->redirectTo(
                'index',
                ['failure' => 'admin_document_error_novalidid'],
                'documents',
                'admin'
            );
            return;
        }

        if (! $this->getRequest()->isPost()) {
            // Neues Formular anzeigen
            $form = new Admin_Form_Document_PersonAdd();

            $role = $this->getRequest()->getParam('role', 'author');
            $form->setSelectedRole($role);

            $this->view->form = $form;
        } else {
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

                        $linkProps   = $form->getPersonLinkProperties($person->getId());
                        $editSession = new Admin_Model_DocumentEditSession($docId);

                        if ($result === Admin_Form_Document_PersonAdd::RESULT_SAVE) {
                            // Zurück zum Metadaten-Formular springen
                            if ($editSession->getPersonCount() > 0) {
                                // Link Informationen durch Session übermitteln
                                $editSession->addPerson($linkProps);
                                $this->_helper->Redirector->redirectToAndExit(
                                    'edit',
                                    null,
                                    'document',
                                    'admin',
                                    [
                                        'id'       => $docId,
                                        'continue' => 'addperson',
                                    ]
                                );
                            } else {
                                // Link Informationen direkt als Parameter übergeben
                                $this->_helper->Redirector->redirectToAndExit(
                                    'edit',
                                    null,
                                    'document',
                                    'admin',
                                    array_merge(
                                        [
                                            'id'       => $docId,
                                            'continue' => 'addperson',
                                        ],
                                        $linkProps
                                    )
                                );
                            }
                            return;
                        } else {
                            // Person in Session merken
                            $editSession->addPerson($linkProps);
                            // Neues Formular erzeugen
                            $role = $form->getSelectedRole();
                            $form = new Admin_Form_Document_PersonAdd();
                            $form->setSelectedRole($role);
                        }
                    } else {
                        // TODO Validierungsfehlernachricht für Formular anzeigen
                        $form->addError($this->view->translate('admin_document_error_validation'));
                    }
                    break;
                case Admin_Form_Document_PersonAdd::RESULT_CANCEL:
                    // Aktuelle Person nicht speichern, aber eventuell gemerkte Personen hinzufügen
                    $this->_helper->Redirector->redirectToAndExit(
                        'edit',
                        null,
                        'document',
                        'admin',
                        [
                            'id'       => $docId,
                            'continue' => 'addperson',
                        ]
                    );
                    return;
                default:
                    break;
            }

            $this->view->form = $form;
        }

        $this->view->document        = $document;
        $this->view->documentAdapter = new Application_Util_DocumentAdapter($this->view, $document);

        // Beim wechseln der Sprache würden Änderungen in editierten Felder verloren gehen
        $this->view->languageSelectorDisabled = true;
        $this->view->breadcrumbsDisabled      = true;
    }

    /**
     * Action zum Editieren einer Person, die mit einem Dokument verknüpft ist.
     *
     * Wird über den Edit-Link für eine Person im Metadatenformular eines Dokuments aufgerufen.
     */
    public function editlinkedAction()
    {
        $docId = $this->getRequest()->getParam('document');

        $document = $this->documentsHelper->getDocumentForId($docId);

        if (! isset($document)) {
            $this->_helper->Redirector->redirectTo(
                'index',
                ['failure' => 'admin_document_error_novalidid'],
                'documents',
                'admin'
            );
            return;
        }

        $form = new Admin_Form_Person();

        if (! $this->getRequest()->isPost()) {
            // Formular anzeigen
            $personId = $this->getRequest()->getParam('personId');

            if ($personId === null || strlen(trim($personId)) === 0) {
                $this->getLogger()->err(__METHOD__ . ' No personId parameter.');
                $this->returnToMetadataForm($docId);
                return;
            }

            if (! is_numeric($personId)) {
                $this->getLogger()->err(__METHOD__ . " Bad personId = '$personId' parameter.");
                $this->returnToMetadataForm($docId);
                return;
            }

            try {
                $person = Person::get($personId);
            } catch (NotFoundException $omnfe) {
                $this->getLogger()->err(__METHOD__ . ' ' . $omnfe->getMessage());
                $this->returnToMetadataForm($docId);
                return;
            }

            $form->populateFromModel($person);

            $this->view->form = $form;
        } else {
            // POST verarbeiten
            $post = $this->getRequest()->getPost();

            $form->populate($post);

            $result = $form->processPost($post, $post);

            switch ($result) {
                case Admin_Form_Person::RESULT_SAVE:
                    if ($form->isValid($post)) {
                        $person = $form->getModel();
                        $person->store();
                        $this->_helper->Redirector->redirectToAndExit(
                            'edit',
                            null,
                            'document',
                            'admin',
                            [
                                'id'       => $docId,
                                'continue' => 'updateperson',
                                'person'   => $person->getId(),
                            ]
                        );
                        return;
                    }
                    // TODO ELSE Validierungsfehlernachricht für Formular anzeigen (notwendig?)
                    break;
                case Admin_Form_Person::RESULT_CANCEL:
                    // Person nicht speichern
                    $this->returnToMetadataForm($docId);
                    return;
                default:
                    break;
            }

            $this->view->form = $form;
        }

        $this->view->document        = $document;
        $this->view->documentAdapter = new Application_Util_DocumentAdapter($this->view, $document);

        // Beim wechseln der Sprache würden Änderungen in editierten Felder verloren gehen
        $this->view->languageSelectorDisabled = true;
        $this->view->breadcrumbsDisabled      = true;
    }

    /**
     * Führt Redirect zum Metadatenformular des Dokuments aus.
     *
     * @param int $docId Dokument-ID
     */
    public function returnToMetadataForm($docId)
    {
        $this->_helper->Redirector->redirectToAndExit(
            'edit',
            null,
            'document',
            'admin',
            [
                'id'       => $docId,
                'continue' => 'true',
            ]
        );
    }
}
