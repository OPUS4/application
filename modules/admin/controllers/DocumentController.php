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
 * Controller for showing and editing a document in the administration.
 */
class Admin_DocumentController extends Controller_Action {

    /**
     * Helper for verifying document IDs.
     * @var Controller_Helper_Documents
     */
    private $documentsHelper;

    /**
     * Controller helper for handling dates.
     * @var Controller_Helper_Dates
     */
    private $__dates;

    /**
     * Initializes controller.
     */
    public function init() {
        parent::init();
        $this->documentsHelper = $this->_helper->getHelper('Documents');
        $this->__dates = $this->_helper->getHelper('Dates');
    }

    /**
     * Produces metadata overview page of a document.
     * @return Opus_Document
     */
    public function indexAction() {
        $docId = $this->getRequest()->getParam('id');

        $document = $this->documentsHelper->getDocumentForId($docId);

        if (isset($document)) {
            $this->view->document = $document;
            $this->view->overviewHelper = new Admin_Model_DocumentHelper($document);

            $this->__prepareActionLinks($document);
            $this->__prepareSectionLinks($docId);

            return $document;
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

        $document = $this->documentsHelper->getDocumentForId($docId);

        if (isset($document)) {
            $section = $this->getRequest()->getParam('section');

            if (Admin_Model_DocumentHelper::isValidGroup($section)) {
                $this->view->section = $section;
                $this->view->docId = $docId;

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

        $document = $this->documentsHelper->getDocumentForId($docId);

        if (isset($document)) {
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
                        $this->view->section = $section;
                        $this->view->docId = $docId;
                        $this->view->addForm = $this->__getAddForm($document,
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

        $document = $this->documentsHelper->getDocumentForId($docId);

        if (isset($document)) {
            $section = $this->getRequest()->getParam('section');

            if ($this->getRequest()->isPost() &&
                    Admin_Model_DocumentHelper::isValidGroup($section)) {
                $postData = $this->getRequest()->getPost();

                $form = $this->__getAddForm($document, $section);

                if ($form->isValid($postData)) {
                    $this->__processCreatePost($postData, $document);
                }
                else {
                    // Show form again
                    $this->view->inputErrorMessage = $this->view->translate('admin_error_invalid_input');
                    $this->view->section = $section;
                    $this->view->docId = $docId;
                    $this->view->addForm = $form;
                    return $this->renderScript('document/add.phtml');
                }

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
     * Updates values of fields and models.
     *
     * TODO Handle processing failures.
     */
    public function updateAction() {
        $docId = $this->getRequest()->getParam('id');

        $document = $this->documentsHelper->getDocumentForId($docId);

        if (isset($document)) {
            $section = $this->getRequest()->getParam('section');

            if ($this->getRequest()->isPost() &&
                    Admin_Model_DocumentHelper::isValidGroup($section)) {
                $postData = $this->getRequest()->getPost();

                if (!array_key_exists('cancel', $postData)) {

                    $form = $this->__getEditForm($document, $section);

                    if ($form->isValid($postData) || $this->_isRemoveRequest($postData)) {
                        $this->__processUpdatePost($postData, $document, $section);
                    }
                    else {
                        // Show form again
                        $this->view->inputErrorMessage = $this->view->translate('admin_error_invalid_input');
                        $this->view->section = $section;
                        $this->view->docId = $docId;
                        $this->view->editForm = $form;
                        return $this->renderScript('document/edit.phtml');
                    }

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
     * Checks if the request is for removing a model from a document.
     * This is currently used to prevent validation for 'remove' requests,
     * since the input for the model that is being removed is irrelevant.
     */
    protected function _isRemoveRequest($postData) {
        foreach ($postData as $fieldName => $modelData) {
            foreach ($modelData as $index => $modelValues) {
                if (array_key_exists('remove', $modelValues)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Removes a document from a collection.
     *
     * TODO Handle processing failure
     */
    public function unlinkcollectionAction() {
        $docId = $this->getRequest()->getParam('id');

        $document = $this->documentsHelper->getDocumentForId($docId);

        if (isset($document)) {
            if ($this->getRequest()->isPost()) {
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

        $workflowActions = array();

        $workflow = $this->_helper->getHelper('Workflow');

        $targetStates = $workflow->getAllowedTargetStatesForDocument($model);

        foreach ($targetStates as $targetState) {
            $action = array();
            $action['label'] = 'admin_workflow_' . $targetState;
            $action['url'] = $documentUrl->adminChangeState($docId, $targetState);
            $workflowActions[$targetState] = $action;
        }

        $this->view->workflowActions = $workflowActions;
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
            $addForm = $this->__getFormForField($field, $doc);
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
                throw new Exception('internal error'); // should not happen
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
                $subform = new Admin_Form_Model($doc, $includedFields, true);
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
                            $subform = $this->__getFormForField($field, $doc, true);
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
    private function __getFormForField($field, $doc, $editMode = false) {
        $subform = null;
        switch ($field->getName()) {
            case 'Licence':
                $subform = new Admin_Form_Model(
                        'Opus_Document', array('Licence'));
                break;
            case 'Series':
                $subform = new Admin_Form_SeriesEntry($doc);
                break;
            default:
                $subform = new Admin_Form_Model($field);
                // TODO hack for OPUSVIER-1544 to make SortOrder mandatory
                if ($editMode && strpos($field->getName(), 'Person') === 0) {
                    $subform->getElement('SortOrder')
                            ->setRequired(true);
                }
                break;
        }
        return $subform;
    }

    /**
     * Processes POST request for adding new value (model) to document.
     */
    private function __processCreatePost($postData, $document) {
        foreach ($postData as $modelClass => $fields) {
            switch ($modelClass) {
                case 'Opus_Person':
                    $person = new Opus_Person();
                    // Hack for OPUSVIER-1544
                    $role = $fields['Role'];
                    $method = 'addPerson' . ucfirst($role);
                    $model = $document->$method($person);
                    $this->__processFields($model, $fields);
                    break;
                case 'Opus_Licence':
                    $this->__addLicence($document, $fields);
                    break;
                case 'Opus_Series':
                    $this->__addSeries($document, $fields);
                    break;
                default:
                    $model = new $modelClass;
                    $this->__processFields($model, $fields);
                    $this->__addFieldValue($document, $modelClass, $model);
                    break;
            }

            $document->store();
        }
    }

    /**
     * Adds a new licence to a document.
     * @param Opus_Document $document Opus_Document instance
     * @param array $fields
     */
    private function __addLicence($document, $fields) {
        $licenceId = $fields['Licence'];
        if (!$this->__hasLicence($document, $licenceId)) {
            $document->addLicence(new Opus_Licence($licenceId));
        }
    }

    /**
     * Adds a series assignment to a document.
     * @param Opus_Document $document
     * @param array $fields
     *
     * TODO don't ignore conflict (use validation)
     */
    private function __addSeries($document, $fields) {
        $seriesId = $fields['Series'];
        $number = $fields['Number'];
        $sortOrder = $fields['SortOrder'];

        $series = $this->__hasSeries($document, $seriesId);

        if ($series === null) {
            $series = $document->addSeries(new Opus_Series($seriesId));
        }

        $series->setNumber($number);

        // if sortOrder is not set, it is set automatically by framework
        if (trim($sortOrder) !== '') {
            $series->setSortOrder($sortOrder);
        }
    }

    /**
     * Updates multiple licences associated with document.
     *
     * Only one licence can be removed at a time. The function exits if the
     * first licence has been removed.
     *
     * If the same licence is selected multiple times it is assigned only once
     * and the previous value of the modified select box is removed.
     *
     * @param Opus_Document $doc
     * @param hash $postData
     */
    private function __updateLicences($doc, $postData) {
        foreach ($postData as $fieldName => $modelData) {
            $field = $doc->getField($fieldName);
            if (!empty($field)) {
                // remove licence
                foreach ($modelData as $index => $modelValues) {
                    $fieldValues = $field->getValue();
                    if (array_key_exists('remove', $modelValues)) {
                        // remove licence
                        unset($fieldValues[$index]);
                        $field->setValue($fieldValues);
                        return; // exit update if licence was removed
                    }
                }
                // collect new licence IDs
                $newLicences = array();
                foreach ($modelData as $index => $modelValues) {
                    $licenceId = $modelValues['Licence'];
                    // prevent duplicate entries
                    if (!in_array($licenceId, $newLicences)) {
                        $newLicences[] = $licenceId;
                    }
                }

                $fieldValues = $field->getValue();

                // remove licences that are no longer selected
                $currentLicences = $doc->getLicence();
                foreach ($currentLicences as $index => $currentLicence) {
                    $licenceId = $currentLicence->getModel()->getId();

                    if (!in_array($licenceId, $newLicences)) {
                        unset($fieldValues[$index]);
                    }
                }

                $field->setValue($fieldValues);

                $doc->store(); // TODO can this additional store be avoided?

                // add new licences
                foreach ($newLicences as $licenceId) {
                    if (!$this->__hasLicence($doc, $licenceId)) {
                        $doc->addLicence(new Opus_Licence($licenceId));
                    }
                }
            }
        }
    }

    /**
     *
     * @param type $document
     * @param type $postData
     * @return type
     *
     * TODO Refactor similarities between __updateLicences and __updateSeries.
     */
    private function __updateSeries($doc, $postData) {
        foreach ($postData as $fieldName => $modelData) {
            $field = $doc->getField($fieldName);
            if (!empty($field)) {
                // remove series
                foreach ($modelData as $index => $modelValues) {
                    $fieldValues = $field->getValue();
                    if (array_key_exists('remove', $modelValues)) {
                        // remove series
                        unset($fieldValues[$index]);
                        $field->setValue($fieldValues);
                        return; // exit update if series was removed
                    }
                }

                // collect new series IDs
                $newSeries = array();
                foreach ($modelData as $index => $modelValues) {
                    $seriesId = $modelValues['Series'];
                    // prevent duplicate entries
                    if (!array_key_exists($seriesId, $newSeries)) {
                        $newSeries[$seriesId] = $modelValues;
                    }
                }

                $fieldValues = $field->getValue();

                $modified = false;

                // remove series that are no longer selected
                $currentSeries = $doc->getSeries();
                foreach ($currentSeries as $index => $currentSeries) {
                    $seriesId = $currentSeries->getModel()->getId();

                    if (!array_key_exists($seriesId, $newSeries)) {
                        unset($fieldValues[$index]);
                        $modified = true;
                    }
                }

                if ($modified) {
                    $field->setValue($fieldValues);

                    $doc->store(); // TODO can this additional store be avoided?
                }

                // add new licences
                foreach ($newSeries as $seriesId => $modelValues) {
                    $this->__addSeries($doc, $modelValues);
                }
            }
        }
    }

    /**
     * Verify if a document has a licence.
     * @param Opus_Document $document
     * @param int $licenceId
     */
    private function __hasLicence($document, $licenceId) {
        $currentLicences = $document->getLicence();
        foreach ($currentLicences as $index => $currentLicence) {
            if ($currentLicence->getModel()->getId() === $licenceId) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if series is already assigned to document.
     * @param Opus_Document $document
     * @param int $seriesId
     * @return boolean
     */
    private function __hasSeries($document, $seriesId) {
        $assignedSeries = $document->getSeries();
        foreach ($assignedSeries as $index => $series) {
            if ($series->getModel()->getId() === $seriesId) {
                return $series;
            }
        }
        return null;
    }

    /**
     * Processes submitted field values.
     * @param Opus_Model_Abstract $model Model instance for fields
     * @param array $fields POST array for model fields
     */
    private function __processFields($model, $fields) {
        foreach ($fields as $name => $value) {
            // TODO filter buttons
            $field = $model->getField($name);
            if (!empty($field)) {
                switch ($field->getValueModelClass()) {
                    case 'Opus_Date':
                        $this->__setDateField($field, $value);
                        break;
                    case 'Opus_DnbInstitute':
                        if ($value === 'nothing') {
                            $field->setValue(null);
                        }
                        else {
                            $institute = new Opus_DnbInstitute($value);
                            // TODO simplify?
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
                        // String that only contain whitespaces are considered
                        // empty
                        if (strlen(trim($value)) === 0) {
                            switch (get_class($model)) {
                                case 'Opus_Model_Dependent_Link_DocumentPerson':
                                    if ($field->getName() === 'SortOrder') {
                                        $field->setValue('x'); // TODO hack for OPUSVIER-1544
                                    }
                                    else {
                                        $field->setValue(null);
                                    }
                                    break;
                                default:
                                    $field->setValue(null);
                                    break;
                            }
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
     * Sets the value of a field with value type Opus_Date.
     * @param Opus_Model_Field $field
     * @param string $value Date value
     */
    private function __setDateField($field, $value) {
        if (!empty($value)) {
            // TODO hack to prevent bad data in database (fix properly)
            if (!$this->__dates->isValid($value)) {
                throw new Exception('Invalid date entered \'' . $value . '\'!');
            }

            $dateModel = $this->__dates->getOpusDate($value);
        }
        else {
            $dateModel = null;
        }
        $field->setValue($dateModel);
    }

    /**
     * Sets the field value for a specific value model class.
     * @param Opus_Document $document
     * @param string $valueModelClass Value model class
     * @param type $value
     *
     * TODO can this be simplified?
     */
    private function __addFieldValue($document, $valueModelClass, $model) {
        switch ($valueModelClass) {
            case 'Opus_Identifier':
                $document->addIdentifier($model);
                break;
            case 'Opus_Person':
                // TODO refactor: this is not used, right?
                $method = 'addPerson' . $model->getRole();
                $document->$method($model);
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
                // Unknown model class
                break;
        }
    }

    /**
     * Process POST request for updating document fields.
     * @param type $postData
     * @param type $document
     * @param type $section
     */
    private function __processUpdatePost($postData, $doc, $section){
        switch ($section) {
            case 'general':
            case 'misc':
            case 'dates':
            case 'other':
            case 'thesis':
                // Process section that contains multiple fields
                $fields = $postData['Opus_Document'];
                $this->__processFields($doc, $fields);
                $this->_logger->debug('ServerDatePublished = ' .
                        $doc->getServerDatePublished());
                break;
            case 'licences':
                // TODO merge with default case
                $this->__updateLicences($doc, $postData);
                break;
            case 'series':
                $this->__updateSeries($doc, $postData);
                break;
            default:
                foreach ($postData as $fieldName => $modelData) {
                    $field = $doc->getField($fieldName);
                    foreach ($modelData as $index => $modelValues) {
                        $fieldValues = $field->getValue();
                        if (array_key_exists('remove', $modelValues)) {
                            unset($fieldValues[$index]);
                            $field->setValue($fieldValues);
                            break;
                        }
                        else {
                            $this->__processFields($fieldValues[$index],
                                    $modelValues);
                        }
                    }
                }
                break;
        }

        $doc->store();
    }

    /**
     * Processes POST request for unlinking collection from document.
     *
     * In the case that the provided collectionId does not match any collection
     * associated with the document NULL is returned.
     *
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
