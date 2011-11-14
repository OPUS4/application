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
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Controller for showing and editing a document in the administration.
 */
class Admin_DocumentController extends Controller_Action {

    // TODO move to documenthelper (or configuration file)
    private $sections = array(
        'general',
        'titles',
        'abstracts',
        'persons',
        'dates',
        'identifiers',
        'references',
        'licences',
        'subjects',
        'collections',
        'thesis',
        'other',
        'patents',
        'notes',
        'enrichments'
    );

    private $sectionModel = array(
        'titles' => 'Opus_Title',
        'abstracts' => 'Opus_TitleAbstract',
        'identifiers' => 'Opus_Identifier',
        'references' => 'Opus_Reference',
        'subjects' => 'Opus_Subject',
        'patents' => 'Opus_Patent',
        'notes' => 'Opus_Note',
        'enrichments' => 'Opus_Enrichment'
    );

    private $sectionField = array(
        'persons' => 'Person',
        'licences' => 'Licence'

    );

    /**
     * Returns a filtered representation of the document.
     *
     * @param  Opus_Document  $document The document to be filtered.
     * @return Opus_Model_Filter The filtered document.
     */
    private function __createFilter(Opus_Document $document, $page = null) {
        $filter = new Opus_Model_Filter();
        $filter->setModel($document);
        $blacklist = array('Collection', 'IdentifierOpus3', 'Source', 'File',
            'ServerState', 'ServerDatePublished', 'ServerDateModified',
            'Type', 'PublicationState');
        $filter->setBlacklist($blacklist);
        // $filter->setSortOrder($type->getAdminFormSortOrder());
        return $filter;
    }

    public function indexAction() {
        $id = $this->getRequest()->getParam('id');

        if (!empty($id) && is_numeric($id)) {
            $model = new Opus_Document($id);

            $filter = new Opus_Model_Filter();
            $filter->setModel($model);
            $blacklist = array('PublicationState');
            $filter->setBlacklist($blacklist);

            $this->view->document = $model;
            $this->view->entry = $filter->toArray();
            $this->view->objectId = $id;

            $this->view->overviewHelper = new Admin_Model_DocumentHelper($model);

            $this->view->docId = $id;

            if (!empty($model)) {
                $this->prepareActionLinks($model);
            }

            $this->prepareEditLinks($id);

            return $model;
        }
        else {
            // missing or bad parameter => go back to main page
            $this->_redirectTo('index', null, 'documents', 'admin');
        }
    }

    public function editAction() {
        $id = $this->getRequest()->getParam('id');

        $section = $this->getRequest()->getParam('section');
        $this->view->section = $section;

        if (!empty($section) && !empty($id) && is_numeric($id)) {
            switch ($section) {
                case 'collections':
                    $document = new Opus_Document($id);
                    $assignedCollections = array();
                    foreach ($document->getCollection() as $assignedCollection) {
                        $assignedCollections[] = array(
                            'collectionName' => $assignedCollection->getDisplayName(),
                            'collectionId' => $assignedCollection->getId(),
                            'roleName' => $assignedCollection->getRole()->getName(),
                            'roleId' => $assignedCollection->getRole()->getId()
                        );
                    }
                    $this->view->assignedCollections = $assignedCollections;
                    $this->view->docId = $id;
                    return $this->renderScript('document/editCollections.phtml');
                    break;
                default:
                    $model = new Opus_Document($id);
                    $this->view->docId = $id;
                    $this->view->editForm = $this->getEditForm($model, $section);
                    return $this->renderScript('document/edit' /* . ucfirst($section) */ . '.phtml');
            }
        }

        $this->_redirectTo('index');
    }

    /**
     * Prepares rendering of add form for document metadata child model.
     *
     * @return type Target script
     */
    public function addAction() {
        $id = $this->getRequest()->getParam('id');
        $section = $this->getRequest()->getParam('section');
        $this->view->section = $section;
        $model = new Opus_Document($id);
        $this->view->docId = $id;
        $this->view->addForm = $this->getAddForm($model, $section);
        return $this->renderScript('document/add' . '.phtml');
    }

    /**
     * Create new model and add to document.
     */
    public function createAction() {
        $id = $this->getRequest()->getParam('id');
        $section = $this->getRequest()->getParam('section');

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();

            $document = new Opus_Document($id);

            foreach ($postData as $modelClass => $fields) {
                $processFields = true;

                switch ($modelClass) {
                    case 'Opus_Person':
                        $person = new Opus_Person();
                        $model = $document->addPerson($person);
                        break;
                    case 'Opus_Licence':
                        // TODO no duplicate entries
                        $licenceIndex = $fields['Licence'];
                        $licences = Opus_Licence::getAll();
                        $currentLicences = $document->getLicence();
                        $licenceAlreadyAssigned = false;
                        foreach ($currentLicences as $index => $currentLicence) {
                            if ($currentLicence->getModel()->getId() == $licenceIndex) {
                                $licenceAlreadyAssigned = true;
                                // TODO print out message
                            }
                        }
                        if (!$licenceAlreadyAssigned) {
                            $document->addLicence(new Opus_Licence($licenceIndex));
                        }
                        $processFields = false;
                        break;
                    default:
                        $model = new $modelClass;
                        break;
                }

                if ($processFields) {
                    foreach ($fields as $name => $value) {
                        // TODO filter buttons
                        $field = $model->getField($name);
                        if (!empty($field)) {
                            switch ($field->getValueModelClass()) {
                                case 'Opus_Date':
                                    $dateFormat = Admin_Model_DocumentHelper::getDateFormat();
                                    if (!empty($value)) {
                                        $date = new Zend_Date($value);
                                        $dateModel = new Opus_Date();
                                        $dateModel->setZendDate($date);
                                    }
                                    else {
                                        $dateModel = null;
                                    }
                                    $field->setValue($dateModel);
                                    break;
                                default:
                                    $field->setValue($value);
                                    break;
                            }
                        }
                    }
                }

                // TODO move in class that can be shared with publishing
                switch ($modelClass) {
                    case 'Opus_Identifier':
                        $document->addIdentifier($model);
                        break;
                    case 'Opus_Person':
                        $document->addPerson($model);
                        break;
                    case 'Opus_Reference':
                        $document->addReference($model);
                        break;
                    case 'Opus_Title':
                        switch ($model->getType()) {
                            case 'main':
                                $document->addTitleMain($model);
                                break;
                            case 'sub':
                                $document->addTitleSub($model);
                                break;
                            case 'parent':
                                $document->addTitleParent($model);
                                break;
                            case 'additional':
                                $document->addTitleAdditional($model);
                                break;
                            default:
                                break;
                        }
                        break;
                    case 'Opus_TitleAbstract':
                        $model->setType('abstract');
                        $document->addTitleAbstract($model);
                        break;
                    case 'Opus_Subject':
                        $document->addSubject($model);
                        break;
                    case 'Opus_SubjectSwd':
                        $document->addSubjectSwd($model);
                        break;
                    case 'Opus_Patent':
                        $document->addPatent($model);
                        break;
                    case 'Opus_Enrichment':
                        $document->addEnrichment($model);
                        break;
                    case 'Opus_Note':
                        $document->addNote($model);
                        break;
                    default:
                        break;
                }

                $document->store();
            }

            $this->_redirectTo('edit', null, 'document', 'admin', array(
                'id' => $id,
                'section' => $section
            ));
        }
        else {
            // TODO What if there is no $id?
            $this->_redirectTo('index', null, 'document', 'admin', array(
                'id' => $id
            ));
        }
    }

    /**
     * Publishes a document
     *
     * @return void
     */
    public function publishAction() {
        if (($this->_request->isPost() === false) && ($this->getRequest()->getParam('docId') === null)) {
            $this->_redirect('index', null, 'documents', 'admin');
        }

        $id = null;
        $id = $this->getRequest()->getParam('docId');
        if ($id === null) {
            $id = $this->getRequest()->getPost('id');
        }
        $sureyes = $this->getRequest()->getPost('sureyes');
        $sureno = $this->getRequest()->getPost('sureno');

        if (isset($sureyes) === true) {
            // publish document
            $doc = new Opus_Document($id);
            $doc->setServerState('published');
            //        $doc->setServerDatePublished(date('Y-m-d'));
            //        $doc->setServerDatePublished(date('c'));
            $date = new Zend_Date();
            $doc->setServerDatePublished($date->get('yyyy-MM-ddThh:mm:ss') . 'Z');
            $doc->store();

            $message = $this->view->translate('document_published', $id);
            $this->_redirectTo('index', $message, 'document', 'admin',
                    array('id' => $id));
        }
        else if (isset($sureno) === true) {
            $message = null;
            $this->_redirectTo('index', $message, 'document', 'admin',
                    array('id' => $id));
        }
        else {
            // show safety question
            $this->view->title = $this->view->translate('admin_doc_publish');
            $this->view->text = $this->view->translate('admin_doc_publish_sure', $id);
            $yesnoForm = $this->_getConfirmationForm($id, 'publish');
            $this->view->form = $yesnoForm;
            return $this->renderScript('document/confirm.phtml');
        }
    }

    /**
     * Deletes a document permanently (removes it from database and disk)
     *
     * @return void
     */
    public function permanentdeleteAction() {
        if ($this->_request->isPost() === true || $this->getRequest()->getParam('docId') !== null) {
            $id = null;
            $id = $this->getRequest()->getParam('docId');
            if ($id === null) {
                $id = $this->getRequest()->getPost('id');
            }
            $sureyes = $this->getRequest()->getPost('sureyes');
            $sureno = $this->getRequest()->getPost('sureno');
            if (isset($sureyes) === true or isset($sureno) === true) {
            	// Safety question answered, deleting
            	if (isset($sureyes) === true) {
                    $model = new Opus_Document($id);
                    try {
                    	$model->deletePermanent();
                    }
                    catch (Exception $e) {
                    	$this->_redirectTo('index', array('failure' => $e->getMessage()), 'documents', 'admin');
                    }
                    $this->_redirectTo('index', $this->view->translate('admin_documents_permanent_delete_success'), 'documents', 'admin');
            	}
            	else {
                    $this->_redirectTo('index', null, 'documents', 'admin');
            	}
            }
            else {
                // show safety question
                $this->view->title = $this->view->translate('admin_doc_delete_permanent');
                $this->view->text = $this->view->translate('admin_doc_delete_permanent_sure', $id);
                $yesnoForm = $this->_getConfirmationForm($id, 'permanentdelete');
                $this->view->form = $yesnoForm;
                return $this->renderScript('document/confirm.phtml');
            }
        } else {
            $this->_redirectTo('index', null, 'documents', 'admin');
        }
    }

    /**
     * Unpublishes a document
     *
     * @return void
     */
    public function unpublishAction() {
        if (($this->_request->isPost() === false) && ($this->getRequest()->getParam('docId') === null)) {
            $this->_redirect('index', null, 'documents', 'admin');
        }

        $id = null;
        $id = $this->getRequest()->getParam('docId');
        if ($id === null) {
            $id = $this->getRequest()->getPost('id');
        }
        $sureyes = $this->getRequest()->getPost('sureyes');
        $sureno = $this->getRequest()->getPost('sureno');

        if (isset($sureyes) === true) {
            $doc = new Opus_Document($id);
            $doc->setServerState('unpublished');
            $doc->store();

            $message = $this->view->translate('document_unpublished', $id);
            $this->_redirectTo('index', $message, 'document', 'admin',
                    array('id' => $id));
        }
        else if (isset($sureno) === true) {
            $message = null;
            $this->_redirectTo('index', $message, 'document', 'admin',
                    array('id' => $id));
        }
        else {
            // show safety question
            $this->view->title = $this->view->translate('admin_doc_unpublish');
            $this->view->text = $this->view->translate('admin_doc_unpublish_sure', $id);
            $yesnoForm = $this->_getConfirmationForm($id, 'unpublish');
            $this->view->form = $yesnoForm;
            return $this->renderScript('document/confirm.phtml');
        }

    }

    /**
     * Deletes a document (sets state to deleted)
     *
     * @return void
     */
    public function deleteAction() {

	$this->_logger->info('call delete action');
        if ($this->_request->isPost() !== true && is_null($this->getRequest()->getParam('docId'))) {
            $this->_redirectTo('index', null, 'documents', 'admin');
        }

	$this->_logger->info('was post request');

        $id = $this->getRequest()->getParam('docId');
        if ($id === null) {
            $id = $this->getRequest()->getPost('id');
        }

	$this->_logger->info('id is ' . $id);

        if ($id === null || !is_numeric($id)) {
            // no valid docId provided, redirect
            $this->_redirectTo('index', array('failure' =>
                $this->view->translate('admin_document_delete_novalidid')),
                    'documents', 'admin');
        }

        $sureyes = $this->getRequest()->getPost('sureyes');
        $sureno = $this->getRequest()->getPost('sureno');

	$this->_logger->info('sureyes is ' . $sureyes);

        if (isset($sureyes) === true or isset($sureno) === true) {
            // Safety question answered, deleting
            if (isset($sureyes) === true) {
		$this->_logger->info('try to remove doc id ' . $id);

                $model = new Opus_Document($id);
                $model->delete();

		$this->_logger->info('deletion successfully');
                $this->_redirectTo('index', $this->view->translate('admin_documents_delete_success'), 'document', 'admin', array('id' => $id));
            }
            else {
                $this->_redirectTo('index', null, 'document', 'admin', array('id' => $id));
            }
        }
        else {
            $doc = null;

            try {
                $doc = new Opus_Document($id);
            }
            catch (Opus_Model_NotFoundException $omnfe) {
                $doc = null;
            }

            if (empty($doc)) {
                $this->_logger->info("trying to delete invalid id " + htmlspecialchars($id));
                return $this->_redirectToAndExit('index', array('failure' =>
                    $this->view->translate('admin_document_delete_novalidid')),
                        'documents', 'admin');
            }
            else if ($doc->getServerState() === 'deleted') {
                return $this->_redirectToAndExit('index', array('failure' =>
                    $this->view->translate('admin_document_error_already_deleted')),
                        'document', 'admin', array('id' => $id));
            }
            else {
                // show safety question
                $this->view->title = $this->view->translate('admin_doc_delete');
                $this->view->text = $this->view->translate('admin_doc_delete_sure', $id);
                $yesnoForm = $this->_getConfirmationForm($id, 'delete');
                $this->view->form = $yesnoForm;
                return $this->renderScript('document/confirm.phtml');
            }
        }
    }

    /**
     * Updates values of fields and models.
     */
    public function updateAction() {
        $id = $this->getRequest()->getParam('id');
        $section = $this->getRequest()->getParam('section');

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            if (!array_key_exists('cancel', $postData)) {
                switch ($section) {
                    case 'general':
                    case 'misc':
                    case 'dates':
                    case 'other':
                    case 'thesis':
                        $model = new Opus_Document($id);
                        $fields = $postData['Opus_Document'];
                        foreach ($fields as $fieldName => $value) {
                            $field = $model->getField($fieldName);
                            if (!empty($field)) {
                                // TODO handle NULL
                                switch ($field->getValueModelClass()) {
                                    case 'Opus_Date':
                                        $dateFormat = Admin_Model_DocumentHelper::getDateFormat();
                                        $this->_logger->debug('Saving date format' . $dateFormat);
                                        if (!empty($value)) {
                                            if (!Zend_Date::isDate($value, $dateFormat)) {
                                                throw new Exception('Invalid date entered');
                                            }
                                            $this->_logger->debug('Saving date ' . $value . ' to field ' . $field->getName());
                                            $date = new Zend_Date($value, $dateFormat);
                                            $this->_logger->debug('Saving Zend_Date = ' . $date . ' to field ' . $field->getName());
                                            $dateModel = new Opus_Date();
                                            $dateModel->setZendDate($date);
                                            $this->_logger->debug('Saving Opus_Date = ' . $dateModel . ' to field ' . $field->getName());
                                        }
                                        else {
                                            $dateModel = null;
                                        }
                                        $field->setValue($dateModel);
                                        break;
                                    case 'Opus_DnbInstitute':
                                        if ($value === 'nothing') {
                                            $field->setValue(null);
                                        }
                                        else {
                                            $institute = new Opus_DnbInstitute($value);
                                            // TODO simplify
                                            switch ($field->getName()) {
                                                case 'ThesisGrantor':
                                                    $model->setThesisGrantor($institute);
                                                    break;
                                                case 'ThesisPublisher':
                                                    $model->setThesisPublisher($institute);
                                                    break;
                                            }
                                        }
                                        break;
                                    default:
                                        if (empty($value)) {
                                           $field->setValue(null);
                                        }
                                        else {
                                            $field->setValue($value);
                                        }
                                        break;
                                }
                            }
                        }
                        $model->store();
                        $this->_logger->debug('ServerDatePublished = ' . $model->getServerDatePublished());
                        break;
                    case 'licences':
                        // TODO merge with default case
                        $model = new Opus_Document($id);
                        foreach ($postData as $fieldName => $modelData) {
                            $field = $model->getField($fieldName);
                            if (!empty($field)) {
                                foreach ($modelData as $index => $modelValues) {
                                    $fieldValues = $field->getValue();
                                    $licenceIndex = $modelValues['Licence'];
                                    if (array_key_exists('remove', $modelValues)) {
                                        unset($fieldValues[$index]);
                                        $field->setValue($fieldValues);
                                        break;
                                    }
                                    else {
                                        $licences = Opus_Licence::getAll();

                                        $fieldValues[$index]->setModel(new Opus_Licence($licenceIndex));
                                    }
                                }
                                $field->setValue($fieldValues);
                            }
                        }
                        $model->store();
                        break;
                    default:
                        $model = new Opus_Document($id);
                        foreach ($postData as $fieldName => $modelData) {
                            $field = $model->getField($fieldName);
                            foreach ($modelData as $index => $modelValues) {
                                $fieldValues = $field->getValue();
                                if (array_key_exists('remove', $modelValues)) {
                                    unset($fieldValues[$index]);
                                    $field->setValue($fieldValues);
                                    break;
                                }
                                else {
                                    $this->populateModel($fieldValues[$index], $modelValues);
                                }
                            }
                        }
                        $model->store();
                        break;
                }

                $message = $this->view->translate('admin_document_update_success');

                $this->_redirectTo('edit', $message, 'document', 'admin', array(
                    'id' => $id,
                    'section' => $section
                ));
            }
            else {
                // TODO what if no $id
                $this->_redirectTo('index', null, 'document', 'admin', array(
                    'id' => $id
                ));
            }
        }
        else {
            // TODO what if no $id, no POST
            $this->_redirectTo('index', null, 'document', 'admin', array(
                'id' => $id
            ));
        }
    }

    /**
     * Removes a document from a collection.
     *
     * @return void
     */
    public function unlinkcollectionAction() {
        if (!$this->_request->isPost()) {
            return $this->_redirectTo('index');
        }
        $document = new Opus_Document($this->getRequest()->getParam('id'));
        $collection_id = $this->getRequest()->getParam('collection');
        $collections = array();
        $deletedCollectionName = null;
        foreach ($document->getCollection() as $collection) {
            if ($collection->getId() !== $collection_id) {
                array_push($collections, $collection);
            }
            else {
                if ($collection->isRoot()) {
                    $deletedCollectionName = $collection->getRole()->getDisplayName();
                }
                else {
                    $deletedCollectionName = $collection->getDisplayName();
                }
            }
        }
        $document->setCollection($collections);
        $document->store();
        $params = $this->getRequest()->getUserParams();
        $module = array_shift($params);
        $controller = array_shift($params);
        $action = array_shift($params);

        $message = $this->view->translate('admin_document_remove_collection_success', $deletedCollectionName);

        $this->_redirectTo('edit', $message, 'document', 'admin', $params);
    }

    protected function populateModel($model, $fieldValues) {
        $this->_logger->debug('Populate model ' . $model);
        foreach($fieldValues as $fieldName => $value) {
            $field = $model->getField($fieldName);
            if (!empty($field)) {
                switch ($field->getValueModelClass()) {
                    case 'Opus_Date':
                        $dateFormat = Admin_Model_DocumentHelper::getDateFormat();
                        if (!empty($value)) {
                            if (!Zend_Date::isDate($value, $dateFormat)) {
                                throw new Exception('Invalid date entered');
                            }
                            $this->_logger->debug('Saving date: ' . $value . ' for field ' . $field->getName() . ' using format ' . $dateFormat);
                            $date = new Zend_Date($value, $dateFormat);
                            $this->_logger->debug('Saving Zend_Date = ' . $date . ' for field ' . $field->getName());
                            $dateModel = new Opus_Date();
                            $dateModel->setZendDate($date);
                            $this->_logger->debug('Saving Opus_Date = ' . $dateModel . ' for field ' . $field->getName());
                        }
                        else {
                            $dateModel = null;
                        }
                        $field->setValue($dateModel);
                        break;
                    default:
                        if (empty($value)) {
                            $field->setValue(null);
                        }
                        else {
                            $field->setValue($value);
                        }
                        break;
                }
            }
        }
    }

    /**
     * Removes a value (model) from document.
     */
    public function removeAction() {

    }

    /**
     * Prepares URLs for action links, e.g frontdoor, delete, publish.
     *
     *
     */
    public function prepareActionLinks($model) {
        $actions = array();

        $docId = $model->getId();
        $docHelper = new Review_Model_DocumentAdapter($this->view, $model);

        $documentUrl = $this->view->documentUrl();

        $action = array();
        $action['label'] = 'admin_documents_open_frontdoor';
        $action['url'] = $documentUrl->frontdoor($docId);
        $actions['frontdoor'] = $action;

        // TODO should always be shown, or?
        if ($docHelper->hasFiles()) {
            $action = array();
            $action['label'] = 'admin_document_files';
            $action['url'] = $documentUrl->adminFileManager($docId);
            $actions['files'] = $action;
        }

        // TODO implement docHelper method
        // TODO: Disabled, since feature is not usable for the user!
//        $action = array();
//        $action['label'] = 'admin_document_access';
//        $action['url'] = $docHelper->getUrlAccessManager();
//        $actions['access'] = $action;

        if ($docHelper->getDocState() === 'unpublished' ||
                $docHelper->getDocState() === 'restricted' ||
                $docHelper->getDocState() === 'inprogress') {
            $action = array();
            $action['label'] = 'admin_doc_delete';
            $action['url'] = $documentUrl->adminDelete($docId);
            $actions['delete'] = $action;

            $action = array();
            $action['label'] = 'admin_documents_publish';
            $action['url'] = $documentUrl->adminPublish($docId);
            $actions['publish'] = $action;
        }
        elseif ($docHelper->getDocState() === 'published') {
            $action = array();
            $action['label'] = 'admin_doc_delete';
            $action['url'] = $documentUrl->adminDelete($docId);
            $actions['delete'] = $action;

            $action = array();
            $action['label'] = 'admin_documents_unpublish';
            $action['url'] = $documentUrl->adminUnpublish($docId);
            $actions['unpublish'] = $action;
        }
        elseif ($docHelper->getDocState() === 'deleted') {
            $action = array();
            $action['label'] = 'admin_doc_undelete';
            $action['url'] = $documentUrl->adminPublish($docId);
            $actions['publish'] = $action;

            $action = array();
            $action['label'] = 'admin_doc_delete_permanent';
            $action['url'] = $documentUrl->adminDeletePermanent($docId);
            $actions['permanentDelete'] = $action;
        }

        $this->view->actions = $actions;

        return $actions;
    }

    public function prepareEditLinks($docId) {
        $editUrls = array();
        $editLabels = array();
        $addUrls = array();

        foreach ($this->sections as $section) {
            $editUrls[$section] = $this->view->url(array(
                'module' => 'admin',
                'controller' => 'document',
                'action' => 'edit',
                'id' => $docId,
                'section' => $section
            ), 'default', false);
            $addUrls[$section] = $this->view->url(array(
                'module' => 'admin',
                'controller' => 'document',
                'action' => 'add',
                'id' => $docId,
                'section' => $section
            ), 'default', false);
            $editLabels[$section] = $this->view->translate('admin_document_edit_section');
            $addLabels[$section] = $this->view->translate('admin_document_add_section');
        }

        $this->view->editUrls = $editUrls;
        $this->view->editLabels = $editLabels;
        $this->view->addUrls = $addUrls;
        $this->view->addLabels = $addLabels;
    }

    public function getAddForm($model, $section) {
        $form = null;

        $id = $model->getId();

        $includedFields = Admin_Model_DocumentHelper::getFieldNamesForGroup($section);

        if (isset($this->sectionModel[$section])) {
            $sectionModel = $this->sectionModel[$section];
        }
        if (isset($this->sectionField[$section])) {
            $sectionField = $this->sectionField[$section];
        }

        if (!empty($sectionModel)) {
            $addForm = new Admin_Form_Model($sectionModel);
        }
        elseif (!empty($sectionField)) {
            $temp = new Opus_Document();
            $field = $temp->getField($sectionField);
            switch ($sectionField) {
                case 'Licence':
                    $addForm = new Admin_Form_Model('Opus_Document', array('Licence'));
                    break;
                default:
                    $addForm = new Admin_Form_Model($temp->getField($sectionField));
                    break;
            }
        }
        else {
            $addForm = null;
        }

        if (!empty($addForm)) {
            $hiddenDocId = new Zend_Form_Element_Hidden('docid');
            $hiddenDocId->setValue($id);

            $addForm->addElement($hiddenDocId);

            $submit = new Zend_Form_Element_Submit('submit_add');
            $submit->setLabel('admin_document_button_add');

            $addForm->addElement($submit);

            $addForm->removeDecorator('Fieldset');
            $addForm->removeDecorator('DtDdWrapper');

            $form = new Zend_Form('AddMetadata');

            $addUrl = $this->view->url(array(
                'action' => 'create',
                'id' => $id,
                'section' => $section
            ));
            $form->setAction($addUrl);

            if (!empty($sectionModel)) {
                $form->addSubForm($addForm, $sectionModel);
            }
            elseif (!empty($field)) {
                $form->addSubForm($addForm, $field->getValueModelClass());
            }
            else {
                // TODO take care of this case
            }
        }

        return $form;
    }

    public function getEditForm($model, $section) {
        $includedFields = Admin_Model_DocumentHelper::getFieldNamesForGroup($section);

        $form = new Zend_Form('edit');

        switch ($section) {
            case 'general':
            case 'misc':
            case 'other':
            case 'dates':
            case 'thesis':
                $subform = new Admin_Form_Model($model, $includedFields);
                $subform->populateFromModel($model);
                $form->addSubForm($subform, 'Opus_Document');
                break;

            default:
                foreach ($includedFields as $index => $fieldName) {
                    $field = $model->getField($fieldName);

                    $fieldNameSub = new Zend_Form_SubForm($fieldName);
                    $fieldNameSub->removeDecorator('fieldset');
                    $fieldNameSub->removeDecorator('DtDdWrapper');

                    $values = $field->getValue();

                    if (is_array($values)) {
                        foreach ($values as $index2 => $value) {
                            switch ($fieldName) {
                                case 'Licence':
                                    $subform = new Admin_Form_Model('Opus_Document', array('Licence'));
                                    break;
                                default:
                                    $subform = new Admin_Form_Model($field);
                                    break;
                            }
                            $subform->removeDecorator('DtDdWrapper');
                            $subform->populateFromModel($value);
                            $subform->setLegend($field->getValueModelClass()); // TODO remove/replace
                            $remove = new Zend_Form_Element_Submit('remove');
                            $remove->setValue($field->getValueModelClass() . $index2);
                            $remove->setLabel('admin_document_button_remove');
                            $subform->addElement($remove);
                            $fieldNameSub->addSubForm($subform, $index2);
                            $form->addSubForm($fieldNameSub, $fieldName);
                        }
                    }
                }
                break;
        }

        $updateUrl = $this->view->url(array(
            'action' => 'update',
            'id' => $model->getId(),
            'section'=> $section
        ));

        $form->setAction($updateUrl);

        $submit = new Zend_Form_Element_Submit('save');
        $submit->setLabel('admin_document_button_save');
        $form->addElement($submit);

        $cancel = new Zend_Form_Element_Submit('cancel');
        $cancel->setLabel('admin_document_button_back');
        $form->addElement($cancel);

        $reset = new Zend_Form_Element_Reset('reset');
        $reset->setLabel('admin_document_button_reset');
        $form->addElement($reset);

        return $form;
    }

    /**
     * Returns form for asking yes/no question like 'Delete file?'.
     *
     * @param type $id
     * @param type $action
     * @return Admin_Form_YesNoForm
     */
    protected function _getConfirmationForm($id, $action) {
        $yesnoForm = new Admin_Form_YesNoForm();
        $idElement = new Zend_Form_Element_Hidden('id');
        $idElement->setValue($id);
        $yesnoForm->addElement($idElement);
        $yesnoForm->setAction($this->view->url(array("controller"=>"document", "action"=>$action)));
        $yesnoForm->setMethod('post');
        return $yesnoForm;
    }

}

?>
