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

    /**
     * Helper for verifying document IDs.
     * @var Controller_Helper_Documents
     */
    private $documentsHelper;

    /**
     * Initializes controller.
     */
    public function init() {
        parent::init();
        $this->documentsHelper = $this->_helper->getHelper('Documents');
    }

    /**
     * Produces metadata overview page of a document.
     * @return Opus_Document
     */
    public function indexAction() {
        $docId = $this->getRequest()->getParam('id');

        if ($this->documentsHelper->isValidId($docId)) {
            $model = new Opus_Document($docId);

            $this->view->document = $model;
            $this->view->overviewHelper = new Admin_Model_DocumentHelper($model);

            $this->__prepareActionLinks($model);
            $this->__prepareSectionLinks($docId);

            return $model;
        }
        else {
            // missing or bad parameter => go back to main page
            return $this->_redirectTo('index', array('failure' =>
                $this->view->translate('admin_document_error_novalidid')),
                    'documents', 'admin');
        }
    }

    /**
     * Shows the edit page for a metadata section.
     */
    public function editAction() {
        $docId = $this->getRequest()->getParam('id');

        if ($this->documentsHelper->isValidId($docId)) {
            $section = $this->getRequest()->getParam('section');

            if (Admin_Model_DocumentHelper::isValidGroup($section)) {
                $this->view->section = $section;
                $this->view->docId = $docId;

                $document = new Opus_Document($docId);

                switch ($section) {
                    case 'collections':
                        $this->view->assignedCollections =
                            $this->__prepareAssignedCollections($document);
                        return $this->renderScript(
                                'document/editCollections.phtml');
                    default:
                        $this->view->editForm = $this->__getEditForm($document,
                                $section);
                        return $this->renderScript('document/edit.phtml');
                }
            }
            else {
                return $this->_redirectTo('index', null, 'document', 'admin',
                        array('id' => $docId));
            }
        }
        else {
            return $this->_redirectTo('index', array('failure' =>
                $this->view->translate('admin_document_error_novalidid')),
                    'documents', 'admin');
        }
    }

    /**
     * Prepares rendering of add form for document metadata section.
     *
     * @return string Target script
     */
    public function addAction() {
        $docId = $this->getRequest()->getParam('id');

        if ($this->documentsHelper->isValidId($docId)) {
            $section = $this->getRequest()->getParam('section');

            if (Admin_Model_DocumentHelper::isValidGroup($section)) {
                switch ($section) {
                    // Redirect for sections that do not have 'Add' page
                    case 'collections':
                    case 'general':
                    case 'dates':
                    case 'thesis':
                    case 'other':
                        return $this->_redirectTo('index', null, 'document',
                                'admin', array('id' => $docId));
                    default:
                        $doc = new Opus_Document($docId);
                        $this->view->section = $section;
                        $this->view->docId = $docId;
                        $this->view->addForm = $this->__getAddForm($doc,
                                $section);
                        return $this->renderScript('document/add.phtml');
                }
            }
            else {
                return $this->_redirectTo('index', null, 'document', 'admin',
                        array('id' => $docId));
            }
        }
        else {
            return $this->_redirectTo('index', array('failure' =>
                $this->view->translate('admin_document_error_novalidid')),
                    'documents', 'admin');
        }
    }

    /**
     * Create new model and add to document.
     *
     * TODO handle processing failures.
     */
    public function createAction() {
        $docId = $this->getRequest()->getParam('id');

        if ($this->documentsHelper->isValidId($docId)) {
            $section = $this->getRequest()->getParam('section');

            if ($this->getRequest()->isPost() &&
                    Admin_Model_DocumentHelper::isValidGroup($section)) {
                $postData = $this->getRequest()->getPost();

                $document = new Opus_Document($docId);

                $this->__processCreatePost($postData, $document);

                return $this->_redirectTo('edit', null, 'document', 'admin',
                        array('id' => $docId, 'section' => $section));
            }
            else {
                // no valid section provided
                return $this->_redirectTo('index', null, 'document', 'admin',
                        array('id' => $docId));
            }
        }
        else {
            // no valid document ID provided
            return $this->_redirectTo('index', array('failure' =>
                $this->view->translate('admin_document_error_novalidid')),
                    'documents', 'admin');
        }
    }

    /**
     * Switches the status of a document to published.
     */
    public function publishAction() {
        $docId = $this->getRequest()->getParam('docId');

        // Check if document identifier is valid
        if (!$this->documentsHelper->isValidId($docId)) {
            return $this->_redirectTo('index', array('failure' =>
                $this->view->translate('admin_document_error_novalidid')),
                    'documents', 'admin');
        }

        $doc = new Opus_Document($docId);

        if ($doc->getServerState() === 'published') {
            return $this->_redirectTo('index', array('failure' =>
                $this->view->translate(
                        'admin_document_error_already_published')),
                    'document', 'admin', array('id' => $docId));
        }

        switch ($this->__confirm($docId, 'publish')) {
            case 'YES':
                $doc->setServerState('published');
                //        $doc->setServerDatePublished(date('Y-m-d'));
                //        $doc->setServerDatePublished(date('c'));
                $date = new Zend_Date();
                $doc->setServerDatePublished(
                        $date->get('yyyy-MM-ddThh:mm:ss') . 'Z');
                $doc->store();

                $message = $this->view->translate('document_published', $docId);
                return $this->_redirectTo('index', $message, 'document',
                        'admin', array('id' => $docId));
                break;
            case 'NO':
                $this->_redirectTo('index', null, 'document', 'admin',
                        array('id' => $docId));
                break;
            default:
                break;
        }
    }

    /**
     * Deletes a document permanently (removes it from database and disk).
     */
    public function permanentdeleteAction() {
        $docId = $this->getRequest()->getParam('docId');

        // Check if document identifier is valid
        if (!$this->documentsHelper->isValidId($docId)) {
            return $this->_redirectTo('index', array('failure' =>
                $this->view->translate('admin_document_error_novalidid')),
                    'documents', 'admin');
        }

        $doc = new Opus_Document($docId);

        switch ($this->__confirm($docId, 'permanentdelete')) {
            case 'YES':
                try {
                    $doc->deletePermanent();
                }
                catch (Exception $e) {
                    $this->_redirectTo('index', array('failure' =>
                        $e->getMessage()), 'documents', 'admin');
                }
                return $this->_redirectTo('index', $this->view->translate(
                        'admin_documents_permanent_delete_success'),
                        'documents', 'admin');
                break;
            case 'NO':
                $this->_redirectTo('index', null, 'document', 'admin',
                        array('id' => $docId));
                break;
            default:
                break;
        }
    }

    /**
     * Unpublishes a document (sets ServerState to unpublished).
     */
    public function unpublishAction() {
        $docId = $this->getRequest()->getParam('docId');

        // Check if document identifier is valid
        if (!$this->documentsHelper->isValidId($docId)) {
            return $this->_redirectTo('index', array('failure' =>
                $this->view->translate('admin_document_error_novalidid')),
                    'documents', 'admin');
        }

        $doc = new Opus_Document($docId);

        if ($doc->getServerState() === 'unpublished') {
            return $this->_redirectTo('index', array('failure' =>
                $this->view->translate(
                        'admin_document_error_already_unpublished')),
                    'document', 'admin', array('id' => $docId));
        }

        switch ($this->__confirm($docId, 'unpublish')) {
            case 'YES':
                $doc->setServerState('unpublished');
                $doc->store();

                $message = $this->view->translate('document_unpublished',
                        $docId);
                return $this->_redirectTo('index', $message, 'document',
                        'admin', array('id' => $docId));
                break;
            case 'NO':
                $this->_redirectTo('index', null, 'document', 'admin',
                        array('id' => $docId));
                break;
            default:
                break;
        }
    }

    /**
     * Deletes a document (sets state to deleted).
     */
    public function deleteAction() {
        $docId = $this->getRequest()->getParam('docId');

        // Check if document identifier is valid
        if (!$this->documentsHelper->isValidId($docId)) {
            return $this->_redirectTo('index', array('failure' =>
                $this->view->translate('admin_document_delete_novalidid')),
                    'documents', 'admin');
        }

        $doc = new Opus_Document($docId);

        if ($doc->getServerState() === 'deleted') {
            return $this->_redirectTo('index', array('failure' =>
                $this->view->translate('admin_document_error_already_deleted')),
                    'document', 'admin', array('id' => $docId));
        }

        switch ($this->__confirm($docId, 'delete')) {
            case 'YES':
                $doc->delete();
                return $this->_redirectTo('index', $this->view->translate(
                        'admin_documents_delete_success'), 'document',
                        'admin', array('id' => $docId));
                break;
            case 'NO':
                return $this->_redirectTo('index', null, 'document', 'admin',
                        array('id' => $docId));

                break;
            default:
                break;
        }
    }

    /**
     * Updates values of fields and models.
     *
     * TODO Handle processing failures.
     */
    public function updateAction() {
        $docId = $this->getRequest()->getParam('id');

        if ($this->documentsHelper->isValidId($docId)) {
            $section = $this->getRequest()->getParam('section');

            if ($this->getRequest()->isPost() &&
                    Admin_Model_DocumentHelper::isValidGroup($section)) {
                $postData = $this->getRequest()->getPost();

                if (!array_key_exists('cancel', $postData)) {
                    $document = new Opus_Document($docId);

                    $this->__processUpdatePost($postData, $document, $section);

                    $message = $this->view->translate(
                            'admin_document_update_success');

                    return $this->_redirectTo('edit', $message, 'document',
                        'admin', array('id' => $docId, 'section' => $section));
                }
                else {
                    // 'cancel' received
                    return $this->_redirectTo('index', null, 'document',
                        'admin', array('id' => $docId));
                }
            }
            else {
                // no valid section provided
                return $this->_redirectTo('index', null, 'document', 'admin',
                    array('id' => $docId));
            }
        }
        else {
            // no valid document ID provided
            return $this->_redirectTo('index', array('failure' =>
                $this->view->translate('admin_document_error_novalidid')),
                    'documents', 'admin');
        }
    }

    /**
     * Removes a document from a collection.
     *
     * TODO Handle processing failure
     */
    public function unlinkcollectionAction() {
        $docId = $this->getRequest()->getParam('id');

        if ($this->documentsHelper->isValidId($docId)) {
            if ($this->getRequest()->isPost()) {
                $document = new Opus_Document($docId);

                $deletedCollectionName =
                    $this->__processUnlinkPost($document);

                $message = $this->view->translate(
                        'admin_document_remove_collection_success',
                        $deletedCollectionName);

                $this->_redirectTo('edit', $message, 'document', 'admin',
                    array('id' => $docId, 'section' => 'collections'));
            }
            else {
                // not a post request
                return $this->_redirectTo('index', null, 'document', 'admin',
                        array('id' => $docId));
            }
        }
        else {
            // no valid document ID
            return $this->_redirectTo('index', array('failure' =>
                $this->view->translate('admin_document_error_novalidid')),
                    'documents', 'admin');
        }
    }

    /**
     * Prepare or processes POST from confirmation page.
     * @param type $docId
     * @param type $action
     * @return type
     */
    private function __confirm($docId, $action) {
        // Check if request is POST and if yes check for user response
        if ($this->getRequest()->isPost()) {
            $sureyes = $this->getRequest()->getPost('sureyes');
            $sureno = $this->getRequest()->getPost('sureno');

            if (isset($sureyes) === true) {
                return 'YES';
            }
            else if (isset($sureno) === true) {
                return 'NO';
            }
        }

        // show confirmation page if not a POST and if not answered YES or NO
        $this->view->title = $this->view->translate('admin_doc_' . $action);
        $this->view->text = $this->view->translate(
                'admin_doc_' . $action . '_sure', $docId);
        $yesnoForm = $this->__getConfirmationForm($docId, $action);
        $this->view->form = $yesnoForm;
        $this->renderScript('document/confirm.phtml');
    }

    /**
     * Populates a model with the provided values.
     * @param Opus_Model_Abstract $model Model instance
     * @param array $fieldValues
     */
    private function __populateModel($model, $fieldValues) {
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
     * Returns array with hashes for information about assigned collections.
     * @return array of hashes containing collection metadata
     */
    private function __prepareAssignedCollections($document) {
        $assignedCollections = array();

        foreach ($document->getCollection() as $assignedCollection) {
            $assignedCollections[] = array(
                'collectionName' => $assignedCollection->getDisplayName(),
                'collectionId' => $assignedCollection->getId(),
                'roleName' => $assignedCollection->getRole()->getName(),
                'roleId' => $assignedCollection->getRole()->getId()
            );
        }

        return $assignedCollections;
    }

    /**
     * Prepares URLs for action links, e.g frontdoor, delete, publish.
     *
     * TODO remove dependency on Review_Model_DocumentAdapter
     */
    private function __prepareActionLinks($model) {
        $actions = array();

        $docId = $model->getId();
        $docHelper = new Review_Model_DocumentAdapter($this->view, $model);

        $documentUrl = $this->view->documentUrl();

        $action = array();
        $action['label'] = 'admin_documents_open_frontdoor';
        $action['url'] = $documentUrl->frontdoor($docId);
        $actions['frontdoor'] = $action;

        $action = array();
        $action['label'] = 'admin_document_files';
        $action['url'] = $documentUrl->adminFileManager($docId);
        $actions['files'] = $action;

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
            $action['label'] = 'admin_doc_permanentdelete';
            $action['url'] = $documentUrl->adminDeletePermanent($docId);
            $actions['permanentDelete'] = $action;
        }

        $this->view->actions = $actions;

        return $actions;
    }

    /**
     * Generates URLs for add and edit links of metadata sections and sets them
     * in the view.
     * @param int $docId Document identifier
     */
    private function __prepareSectionLinks($docId) {
        $editUrls = array();
        $editLabels = array();
        $addUrls = array();
        $addLabels = array();

        $sections = Admin_Model_DocumentHelper::getGroups();

        foreach ($sections as $section) {
            // Links for 'Edit' pages
            $editUrls[$section] = $this->view->url(array(
                'module' => 'admin',
                'controller' => 'document',
                'action' => 'edit',
                'id' => $docId,
                'section' => $section
            ), 'default', false);

            $editLabels[$section] = $this->view->translate(
                    'admin_document_edit_section');

            // Links for 'Add' pages
            $addUrls[$section] = $this->view->url(array(
                'module' => 'admin',
                'controller' => 'document',
                'action' => 'add',
                'id' => $docId,
                'section' => $section
            ), 'default', false);

            $addLabels[$section] = $this->view->translate(
                    'admin_document_add_section');
        }

        $this->view->editUrls = $editUrls;
        $this->view->editLabels = $editLabels;
        $this->view->addUrls = $addUrls;
        $this->view->addLabels = $addLabels;
    }

    /**
     * Generates Zend_Form for adding a value to a metadata section of a
     * document.
     * @param Opus_Document $doc Document instance
     * @param string $section Name of metadata section
     * @return Zend_Form
     */
    private function __getAddForm($doc, $section) {
        $form = null;

        $docId = $doc->getId();

        $includedFields = Admin_Model_DocumentHelper::getFieldNamesForGroup(
                $section);

        $sectionModel = Admin_Model_DocumentHelper::getModelClassForGroup(
                $section);

        $sectionField = Admin_Model_DocumentHelper::getFieldNameForGroup(
                $section);

        $addForm = null;

        if (!empty($sectionModel)) {
            $addForm = new Admin_Form_Model($sectionModel);
        }
        elseif (!empty($sectionField)) {
            $field = $doc->getField($sectionField);
            $addForm = $this->__getFormForField($field);
        }

        if (!empty($addForm)) {
            $hiddenDocId = new Zend_Form_Element_Hidden('docid');
            $hiddenDocId->setValue($docId);

            $addForm->addElement($hiddenDocId);

            $submit = new Zend_Form_Element_Submit('submit_add');
            $submit->setLabel('admin_document_button_add');

            $addForm->addElement($submit);

            $addForm->removeDecorator('Fieldset');
            $addForm->removeDecorator('DtDdWrapper');

            $form = new Zend_Form('AddMetadata');

            $addUrl = $this->view->url(array(
                'action' => 'create',
                'id' => $docId,
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

    /**
     * Generates form for editing the values of document fields.
     * @param Opus_Document $doc
     * @param type $section
     * @return Zend_Form
     */
    private function __getEditForm($doc, $section) {
        $includedFields = Admin_Model_DocumentHelper::getFieldNamesForGroup(
                $section);

        $form = new Zend_Form('edit');

        switch ($section) {
            case 'general':
            case 'misc':
            case 'other':
            case 'dates':
            case 'thesis':
                $subform = new Admin_Form_Model($doc, $includedFields);
                $subform->populateFromModel($doc);
                $form->addSubForm($subform, 'Opus_Document');
                break;

            default:
                foreach ($includedFields as $index => $fieldName) {
                    $field = $doc->getField($fieldName);

                    $fieldNameSub = new Zend_Form_SubForm($fieldName);
                    $fieldNameSub->removeDecorator('fieldset');
                    $fieldNameSub->removeDecorator('DtDdWrapper');

                    $values = $field->getValue();

                    if (is_array($values)) {
                        foreach ($values as $index2 => $value) {
                            $subform = $this->__getFormForField($field);
                            $subform->removeDecorator('DtDdWrapper');
                            $subform->populateFromModel($value);
                            $subform->setLegend($field->getValueModelClass()); // TODO remove/replace
                            $remove = new Zend_Form_Element_Submit('remove');
                            $remove->setValue(
                                    $field->getValueModelClass() . $index2);
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
            'id' => $doc->getId(),
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
     * Returns empty form for a model field.
     * @param Opus_Model_Field $field
     * @return Admin_Form_Model
     */
    private function __getFormForField($field) {
        $subform = null;
        switch ($field->getName()) {
            case 'Licence':
                $subform = new Admin_Form_Model(
                        'Opus_Document', array('Licence'));
                break;
            default:
                $subform = new Admin_Form_Model($field);
                break;
        }
        return $subform;
    }

    /**
     * Returns form for asking yes/no question like 'Delete file?'.
     *
     * @param int $id Document identifier
     * @param string $action Target action that needs to be confirmed
     * @return Admin_Form_YesNoForm
     */
    private function __getConfirmationForm($docId, $action) {
        $yesnoForm = new Admin_Form_YesNoForm();
        $idElement = new Zend_Form_Element_Hidden('id');
        $idElement->setValue($docId);
        $yesnoForm->addElement($idElement);
        $yesnoForm->setAction($this->view->url(
                array("controller" => "document", "action" => $action)));
        $yesnoForm->setMethod('post');
        return $yesnoForm;
    }

    /**
     * Processes POST request for adding new value (model) to document.
     */
    private function __processCreatePost($postData, $document) {
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
    }

    /**
     * Process POST request for updating document fields.
     * @param type $postData
     * @param type $document
     * @param type $section
     */
    private function __processUpdatePost($postData, $model, $section){
        switch ($section) {
            case 'general':
            case 'misc':
            case 'dates':
            case 'other':
            case 'thesis':
                $fields = $postData['Opus_Document'];
                foreach ($fields as $fieldName => $value) {
                    $field = $model->getField($fieldName);
                    if (!empty($field)) {
                        // TODO handle NULL
                        switch ($field->getValueModelClass()) {
                            case 'Opus_Date':
                                $dateFormat = Admin_Model_DocumentHelper::getDateFormat();
                                $this->_logger->debug('Saving date format'
                                        . $dateFormat);
                                if (!empty($value)) {
                                    if (!Zend_Date::isDate($value, $dateFormat)) {
                                        throw new Exception('Invalid date entered');
                                    }
                                    $this->_logger->debug('Saving date '
                                            . $value . ' to field '
                                            . $field->getName());
                                    $date = new Zend_Date($value, $dateFormat);
                                    $this->_logger->debug('Saving Zend_Date = '
                                            . $date . ' to field '
                                            . $field->getName());
                                    $dateModel = new Opus_Date();
                                    $dateModel->setZendDate($date);
                                    $this->_logger->debug('Saving Opus_Date = '
                                            . $dateModel . ' to field '
                                            . $field->getName());
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
                $this->_logger->debug('ServerDatePublished = ' .
                        $model->getServerDatePublished());
                break;
            case 'licences':
                // TODO merge with default case
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

                                $fieldValues[$index]->setModel(
                                        new Opus_Licence($licenceIndex));
                            }
                        }
                        $field->setValue($fieldValues);
                    }
                }
                $model->store();
                break;
            default:
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
                            $this->__populateModel($fieldValues[$index],
                                    $modelValues);
                        }
                    }
                }
                $model->store();
                break;
        }
    }

    /**
     * Processes POST request for unlinking collection from document.
     * @param Opus_Document $document
     * @return string Name of collection that was unlinked
     */
    private function __processUnlinkPost($document) {
        $deletedCollectionName = null;

        $collectionId = $this->getRequest()->getParam('collection');
        $collections = array();
        foreach ($document->getCollection() as $collection) {
            if ($collection->getId() !== $collectionId) {
                array_push($collections, $collection);
            }
            else {
                // Get name of removed collection
                if ($collection->isRoot()) {
                    $deletedCollectionName =
                            $collection->getRole()->getDisplayName();
                }
                else {
                    $deletedCollectionName =
                            $collection->getDisplayName();
                }
            }
        }
        $document->setCollection($collections);
        $document->store();

        return $deletedCollectionName;
    }

}
