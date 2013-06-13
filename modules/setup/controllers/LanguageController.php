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
 * @category    TODO
 * @package     TODO
 * @author      Edouard Simon (edouard.simon@zib.de)
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * 
 */
class Setup_LanguageController extends Controller_SetupAbstract {

    protected $sortKeys = array('unit', 'module', 'directory', 'filename', 'language', 'variant');

    public function indexAction() {
        $this->view->form = $this->getSearchForm();
    }

    public function showAction() {

        $searchTerm = $this->_request->getParam('search');
        $sortKey = $this->_request->getParam('sort', 'unit');
        $config = Zend_Registry::get('Zend_Config')->toArray();
        if(!isset($config['setup']['translation']['modules']['allowed']))
            $this->_redirectTo ('error', $this->view->translate('setup_language_translation_modules_missing'));

        
        $moduleNames = explode(',', $config['setup']['translation']['modules']['allowed']);

        $translationManager = new Setup_Model_Language_TranslationManager();
        $translationManager->setModules($moduleNames);
        if (!empty($searchTerm)) {
            $translationManager->setFilter($searchTerm);
        }

        $this->view->translations = $translationManager->getTranslations($sortKey);
        $this->view->sortKeys = $this->sortKeys;
        $this->view->currentSortKey = $sortKey;
        $this->view->searchTerm = $searchTerm;
        $this->view->form = $this->getSearchForm($searchTerm, $sortKey);
    }

    protected function getForm() {
        $translationKey = $this->_request->getParam('key');

        if (empty($translationKey))
            throw new Application_Exception('Parameters missing');

        $keyForm = new Zend_Form_SubForm();

        $keyForm->addElement('textarea', 'en', array('label' => 'en'));
        $keyForm->addElement('textarea', 'de', array('label' => 'de'));
        $keyForm->addDisplayGroup(array('de', 'en'), $translationKey, array('legend' => $translationKey));
        $form = new Zend_Form_SubForm();
        $form->addSubForm($keyForm, $translationKey);

        return $form;
    }

    protected function getModel() {
        $translationKey = $this->_request->getParam('key');
        $sourceFileEncoded = $this->_request->getParam('file');

        if (empty($translationKey) || empty($sourceFileEncoded))
            throw new Application_Exception('Parameters missing');

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

        $form = new Zend_Form();

        $form->addElement('text', 'search', array('label' => $this->view->translate('setup_language_searchTerm')));

        $sortKeysTranslated = array();
        foreach ($this->sortKeys as $option) {
            $sortKeysTranslated[$option] = $this->view->translate('setup_language_' . $option);
        }

        $form->addElement('select', 'sort', array('label' => $this->view->translate('setup_language_sortKey'), 'multiOptions' => $sortKeysTranslated));
        $form->addElement('submit', 'Anzeigen');
        $form->setAction($this->view->url(array('action' => 'show')));

        if (!empty($searchTerm))
            $form->search->setValue($searchTerm);
        if (!empty($sortKey))
            $form->sort->setValue($sortKey);

        return $form;
    }

}
