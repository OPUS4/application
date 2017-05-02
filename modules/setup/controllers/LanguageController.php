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
 * @package     Module_Setup
 * @author      Edouard Simon (edouard.simon@zib.de)
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 *
 */
class Setup_LanguageController extends Application_Controller_SetupAbstract {

    protected $_sortKeys = array('unit', 'module', 'directory', 'filename', 'language', 'variant');

    public function init() {
        parent::init();

        $this->getHelper('MainMenu')->setActive('admin');
    }

    public function indexAction() {
        $this->view->form = $this->getSearchForm();
    }

    public function showAction() {

        $searchTerm = $this->_request->getParam('search');
        $sortKey = $this->_request->getParam('sort', 'unit');
        $config = $this->getConfig()->toArray();
        if (!isset($config['setup']['translation']['modules']['allowed'])) {
            $this->_helper->Redirector->redirectTo(
                'error', array('failure' => 'setup_language_translation_modules_missing')
            );
        }


        $moduleNames = explode(',', $config['setup']['translation']['modules']['allowed']);

        $translationManager = new Setup_Model_Language_TranslationManager();
        $translationManager->setModules($moduleNames);
        if (!empty($searchTerm)) {
            $translationManager->setFilter($searchTerm);
        }

        $this->view->form = $this->getSearchForm($searchTerm, $sortKey);

        $this->view->translations = $translationManager->getTranslations($sortKey);
        $this->view->sortKeys = $this->_sortKeys;
        $this->view->currentSortKey = $sortKey;
        $this->view->searchTerm = $searchTerm;

    }

    protected function getForm() {
        $translationKey = $this->_request->getParam('key');

        if (empty($translationKey)) {
            throw new Application_Exception('Parameters missing');
        }

        $form = new Zend_Form_SubForm();
        $form->addSubForm(new Setup_Form_LanguageKey($translationKey), $translationKey);

        return $form;
    }

    protected function getModel() {
        $translationKey = $this->_request->getParam('key');
        $sourceFileEncoded = $this->_request->getParam('file');

        if (empty($translationKey) || empty($sourceFileEncoded)) {
            throw new Application_Exception('Parameters missing');
        }

        $sourceFile = urldecode($sourceFileEncoded);

        list($moduleName, $languageDir, $fileName) = explode('/', $sourceFile);

        $basePath = APPLICATION_PATH . '/modules';

        $targetFile = "$basePath/$moduleName/language_custom/$fileName";

        $translationSourceParams = array(
            'moduleBasepath' => $basePath,
            'moduleName' => $moduleName,
            'languageDirectory' => $languageDir,
            'filename' => $fileName
        );

        $config = array(
            'translationSourceParams' => $translationSourceParams,
            'translationTarget' => $targetFile);
        return new Setup_Model_Language($config);
    }

    protected function getSearchForm($searchTerm = null, $sortKey = null) {

        $sortKeysTranslated = array();
        foreach ($this->_sortKeys as $option) {
            $sortKeysTranslated[$option] = $this->view->translate('setup_language_' . $option);
        }

        $form = new Setup_Form_LanguageSearch();

        $form->getElement('search')->setLabel($this->view->translate('setup_language_searchTerm'));
        $form->getElement('sort')
                ->setLabel($this->view->translate('setup_language_sortKey'))
                ->setMultiOptions($sortKeysTranslated);

        $form->setAction($this->view->url(array('action' => 'show')));

        if (!empty($searchTerm)) {
            $form->search->setValue($searchTerm);
        }
        if (!empty($sortKey)) {
            $form->sort->setValue($sortKey);
        }

        return $form;
    }

}
