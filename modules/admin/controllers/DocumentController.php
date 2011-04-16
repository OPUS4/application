<?php


/**
 * Controller for showing and editing a document in the administration.
 */
class Admin_DocumentController extends Controller_Action {

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
        'misc',
        'thesis'
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
            'ServerDateUnlocking', 'Type', 'PublicationState');
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

            $this->view->overviewHelper = new Admin_Model_DocumentOverviewHelper($model);

            if (!empty($model)) {
                $this->view->docHelper = new Review_Model_DocumentAdapter(
                        $this->view, $model);
            }

            $this->prepareEditLinks();

            return $model;
        }
        else {
            // missing or bad parameter => go back to main page
            $this->_helper->redirector('index', null, 'documents', 'admin');
        }
    }

    public function editAction() {
        $section = $this->getRequest()->getParam('section');

        if (!empty($section)) {
        }

        $this->_redirectTo('index');
    }

    public function updateAction() {
    }

    /**
     * Prepares URLs for action links, e.g frontdoor, delete, publish.
     *
     *
     */
    public function prepareActionLinks() {
    }

    public function prepareEditLinks() {
        $editUrls = array();
        $editLabels = array();

        foreach ($this->sections as $section) {
            $editUrls[$section] = $this->view->url(array(
                'module' => 'admin',
                'controller' => 'document',
                'action' => 'edit',
                'section' => $section
            ), 'default', false);
            $editLabels[$section] = $this->view->translate('admin_document_edit_section');
        }

        $this->view->editUrls = $editUrls;
        $this->view->editLabels = $editLabels;
    }

}

?>
