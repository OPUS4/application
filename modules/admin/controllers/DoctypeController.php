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
 */

/**
 * Dieser Controller zeigt alle Dokumenttypen an und deren Validierungsstatus (einschließlich möglicher
 * Fehlermeldungen).
 *
 * @category    Application
 * @package     Module_Admin
 * @author      Michael Lang <lang@zib.de>
 * @copyright   Copyright (c) 2008-2014, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Admin_DoctypeController extends Application_Controller_Action {

    private $_documentTypesHelper;

    public function init() {
        parent::init();
        $this->_documentTypesHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('DocumentTypes');
    }

    public function indexAction() {
        $validationArray = $this->_documentTypesHelper->validateAll();
        ksort($validationArray);
        $this->view->content = $validationArray;
        $this->view->activeDoctypes = $this->_documentTypesHelper->getDocumentTypes();
        $this->view->title = $this->view->translate('admin_doctype_index');
        $this->view->numDocs = sizeof($validationArray);
        $this->view->numActiveDocs = sizeof($this->view->activeDoctypes);
    }

    public function showAction() {
        $doctype = $this->getRequest()->getParam('doctype');
        if (!$this->_documentTypesHelper->isValid($doctype)) {
            return $this->_helper->Redirector->redirectTo(
                'index', array('failure' => 'admin_doctype_invalid'),
                'doctype', 'admin'
            );
        }
        $this->view->doctypeName = $doctype . ':';
        $this->_documentTypesHelper->validate($doctype);
        $errors = $this->_documentTypesHelper->getErrors();
        $this->view->errorArray = $errors[$doctype];
        $this->view->title = $doctype;
        $this->_breadcrumbs->setLabelFor('admin_doctype_show', $doctype);
    }
}
