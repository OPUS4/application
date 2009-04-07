<?php
/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the North Rhine-Westphalian Library Service Center,
 * the Cooperative Library Network Berlin-Brandenburg, the Saarland University
 * and State Library, the Saxon State Library - Dresden State and University
 * Library, the Bielefeld University Library and the University Library of
 * Hamburg University of Technology with funding from the German Research
 * Foundation and the European Regional Development Fund.
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
 * @author      Henning Gerhardt (henning.gerhardt@slub-dresden.de)
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Administrative work with document metadata.
 */
class Admin_DocumentsController extends Zend_Controller_Action {

    /**
     * Redirector - defined for code completion
     *
     * @var Zend_Controller_Action_Helper_Redirector
     */
    protected $_redirector = null;

    /**
     * Do some initialization on startup of every action.
     *
     * @return void
     */
    public function init() {
        $this->_redirector = $this->_helper->getHelper('Redirector');
    }

    /**
     * Display list of documents.
     *
     * @return void
     */
    public function indexAction() {
        //
        $this->view->title = 'Documents';
        // following could be handled inside a application model
        $documentLists = Opus_Document::getAllIds();
        $titlesList = Opus_Document::getAllDocumentTitles();
        $result = array();
        foreach ($documentLists as $docId) {
            $iterim = array(
                'title' => array_keys($titlesList, $docId),
                'docId' => $docId,
                );
            $result[] = $iterim;
        }
        $this->view->documentList = $result;
    }

    /**
     * Show edit form.
     *
     * @return void.
     */
    public function editAction() {

        $this->view->title = 'Document edit';

        $docId = (int) $this->getRequest()->getParam('docId', 0);
        if (true === empty($docId)) {
            $this->_redirector->gotoSimple('index');
        }
        $form_builder = new Opus_Form_Builder();
        $doc = new Opus_Document($docId);
        $form = $form_builder->build($doc);
        // submitting "hidden" docId !
        // save action based on this behaviour
        $action_url = $this->view->url(array('module' => 'admin', 'controller' => 'documents', 'action' => 'save'));
        $form->setAction($action_url);
        $this->view->form = $form;
    }

    /**
     * Save submitted data.
     *
     * @return void
     */
    public function saveAction() {
        $docId = (int) $this->getRequest()->getParam('docId', 0);

        if ((true === empty($docId)) or (false === $this->getRequest()->isPost())) {
            // docId not submitted, back to index
            $this->_redirector->gotoSimple('index');
        }

        $postdata = $this->getRequest()->getPost();
        $form_builder = new Opus_Form_Builder();
        $form = $form_builder->buildFromPost($postdata);
        if (true === $form->isValid($postdata)) {
            // retrieve old version from model
            $model = $form_builder->getModelFromForm($form);
            // overwrite old data in the model with the new data from the form
            $form_builder->setFromPost($model, $form->getValues());
            $model->store();
            // go back to index
            $this->_redirector->gotoSimple('index');
        } else {
            // submitting "hidden" docId !
            // save action based on this behaviour
            $action_url = $this->view->url(array('module' => 'admin', 'controller' => 'documents', 'action' => 'save'));
            $form->setAction($action_url);
            $this->view->form = $form;
            $this->render('edit');
            return;
        }
    }

    /**
     * Delete a document.
     *
     * @return void
     */
    public function deleteAction() {
        $docId = $this->getRequest()->getPost('docId', 0);
        if (true === empty($docId) or (false === $this->getRequest()->isPost())) {
            $this->_redirector->gotoSimple('index');
        }
        $this->view->title = 'Delete document ' . $docId;
    }
}