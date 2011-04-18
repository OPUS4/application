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
        'other',
        'thesis',
        'enrichments'
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

            $this->view->overviewHelper = new Admin_Model_DocumentHelper($model);

            if (!empty($model)) {
                $this->view->docHelper = new Review_Model_DocumentAdapter(
                        $this->view, $model);
            }

            $this->prepareActionLinks($this->view->docHelper);

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
    public function prepareActionLinks($docHelper) {
        $actions = array();
        
        $action = array();
        $action['label'] = 'admin_documents_open_frontdoor';
        $action['url'] = $docHelper->getUrlFrontdoor();
        $actions['frontdoor'] = $action;
        
        // TODO should always be shown, or?
        if ($docHelper->hasFiles()) {
            $action = array();
            $action['label'] = 'admin_document_files';
            $action['url'] = $docHelper->getUrlFileManager();
            $actions['files'] = $action;
        }

        // TODO implement docHelper method
        $action = array();
        $action['label'] = 'admin_document_access';
        $action['url'] = $docHelper->getUrlAccessManager();
        $actions['access'] = $action;
        
        if ($docHelper->getDocState() === 'unpublished') {
            $action = array();
            $action['label'] = 'admin_doc_delete';
            $action['url'] = $docHelper->getUrlDelete();
            $actions['delete'] = $action;
            
            $action = array();
            $action['label'] = 'admin_documents_publish';
            $action['url'] = $docHelper->getUrlPublish();
            $actions['publish'] = $action;
        }
        elseif ($docHelper->getDocState() === 'published') {
            $action = array();
            $action['label'] = 'admin_doc_delete';
            $action['url'] = $docHelper->getUrlDelete();
            $actions['delete'] = $action;

            $action = array();
            $action['label'] = 'admin_documents_unpublish';
            $action['url'] = $docHelper->getUrlUnpublish();
            $actions['unpublish'] = $action;
        }
        elseif ($this->docHelper->getDocState() === 'deleted') {
            $action = array();
            $action['label'] = 'admin_doc_undelete';
            $action['url'] = $docHelper->getUrlPublish();
            $actions['publish'] = $action;

            $action = array();
            $action['label'] = 'admin_doc_delete_permanent';
            $action['url'] = $docHelper->getUrlPermanentDelete();
            $actions['permanentDelete'] = $action;
        }

        $this->view->actions = $actions;

        return $actions;
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
