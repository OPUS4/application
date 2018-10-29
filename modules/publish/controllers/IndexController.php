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
 * @package     Module_Publish
 * @author      Susanne Gottwald <gottwald@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Publish_IndexController extends Application_Controller_Action {

    /**
     * Renders the first form:
     * a list of available document types (that can be configured in config.ini
     * and different upload fields
     *
     * @return void
     *
     */
    public function indexAction() {
        $session = new Zend_Session_Namespace('Publish');

        //unset all possible session content
        $session->unsetAll();

        $this->view->title = 'publish_controller_index';

        $form = new Publish_Form_PublishingFirst();

        $this->view->action_url = $this->view->url(array('controller' => 'form', 'action' => 'upload'));
        $this->view->showBib = $form->bibliographie;
        $this->view->showRights = $form->showRights;
        $this->view->enableUpload = $form->enableUpload;
        if (!$form->enableUpload) {
            $this->view->subtitle = 'publish_controller_index_sub_without_file';
        }
        else {
            $this->view->subtitle = 'publish_controller_index_sub';
        }

        //initialize session variables
        // TODO hide initialization routine
        $session->documentType = "";
        $session->documentId = "";
        $session->additionalFields = array();

        $config = $this->getConfig();

        if (isset($config->publish->filetypes->allowed)) {
            $this->view->extensions = $config->publish->filetypes->allowed;
        }

        // Quick bug fix for OPUSVIER-3564
        $translate = Zend_Registry::get('Zend_Translate');
        if ($translate->isTranslated('tooltip_documentType')) {
            $this->view->documentType['hint'] = 'tooltip_documentType';
        }

        // Adds translated messages for javascript files
        $javascriptTranslations = $this->view->getHelper('javascriptMessages');
        $javascriptTranslations->addMessage('uploadedFileHasErrorMessage');
        $javascriptTranslations->addMessage('fileExtensionFalse');
        $javascriptTranslations->addMessage('fileUploadErrorSize');
        $javascriptTranslations->addMessage('filenameLengthError');
        $javascriptTranslations->addMessage('filenameFormatError');
        $javascriptTranslations->addMessage('chooseAnotherFile');
    }
}
