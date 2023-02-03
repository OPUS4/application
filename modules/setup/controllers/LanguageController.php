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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * TODO rename controller to TranslationController
 * TODO sorting using table header
 *
 * After canceling an Edit form the user gets returned to the search page displaying the last search.
 */
class Setup_LanguageController extends Application_Controller_Action
{
    public const PARAM_SORT   = 'sort';
    public const PARAM_SEARCH = 'search';
    public const PARAM_MODULE = 'modules';
    public const PARAM_SCOPE  = 'scope';
    public const PARAM_STATE  = 'state';

    /** @var string[] TODO update name and values for new functionality */
    private $sortKeys = ['key', 'translation'];

    /**
     * Initialize controller.
     */
    public function init()
    {
        parent::init();

        $this->getHelper('MainMenu')->setActive('admin');
        $this->view->headLink()->appendStylesheet($this->view->layoutPath() . '/css/setup.css');
    }

    /**
     * Action for searching and showing translations.
     *
     * TODO move handling of allowed modules into manager
     */
    public function indexAction()
    {
        $searchTerm = $this->getParam('search', null);
        $sortKey    = $this->getParam('sort', 'key');
        $modules    = $this->getParam('modules', null);
        $state      = $this->getParam('state', null);
        $scope      = $this->getParam('scope', null);

        $config = $this->getConfig();

        // TODO move to manager (the check) - error handling should be here
        if (! isset($config->setup->translation->modules->allowed)) {
            $this->_helper->Redirector->redirectTo(
                'error',
                ['failure' => 'setup_language_translation_modules_missing']
            );
        }

        $request = $this->getRequest();

        $form = $this->getSearchForm($searchTerm, $sortKey);

        $form->populateFromRequest($request);

        if ($request->isPost()) {
            $searchTerm = $form->getElement($form::ELEMENT_FILTER)->getValue();
            $modules    = $form->getElement($form::ELEMENT_MODULES)->getValue();
            $state      = $form->getElement($form::ELEMENT_STATE)->getValue();
            $scope      = $form->getElement($form::ELEMENT_SCOPE)->getValue();

            // redirect so using the back button in the browser doesn't require resubmitting the form
            $this->_helper->Redirector->redirectTo('index', null, null, null, [
                'search'  => $searchTerm,
                'sort'    => $sortKey,
                'modules' => $modules,
                'state'   => $state,
                'scope'   => $scope,
            ]);
        }

        $translationManager = $this->getTranslationManager();

        $this->setFilterParameters($translationManager);

        $translations = $translationManager->getMergedTranslations($sortKey);

        $this->view->translations   = $translations;
        $this->view->form           = $form;
        $this->view->sortKeys       = $this->sortKeys;
        $this->view->currentSortKey = $sortKey;
        $this->view->searchTerm     = $searchTerm;
        $this->view->searchState    = $state;
        $this->view->searchScope    = $scope;
        $this->view->headScript()->appendFile($this->view->layoutPath() . '/js/setup/setup.js');
    }

    /**
     * Action for adding a new translation key.
     *
     * TODO merge EDIT and ADD action?
     */
    public function addAction()
    {
        $request = $this->getRequest();

        $key    = $this->getParam('key', null);
        $module = $this->getParam('keymodule', null);

        if ($request->isPost()) {
            $post   = $request->getPost();
            $form   = $this->getTranslationForm();
            $result = $form->processPost($post, $post);

            switch ($result) {
                case Setup_Form_Translation::RESULT_SAVE:
                    if (! isset($post[$form::ELEMENT_KEY])) {
                        $post[$form::ELEMENT_KEY] = $key;
                    }
                    if (! isset($post[$form::ELEMENT_MODULE])) {
                        $post[$form::ELEMENT_MODULE] = $module;
                    }

                    if ($form->isValid($post)) {
                        $form->updateTranslation();
                        // TODO manipulate filter so new key is visible (?) - could lead to confusion
                        $form = null; // triggers redirect to index page
                    } else {
                        // go back to form
                        break;
                    }
                    // fall through to CANCEL
                default:
                    // fall through to CANCEL
                case Setup_Form_Translation::RESULT_CANCEL:
                    $form = null;
            }
            if ($form === null) {
                if ($this->getParam('back', null) !== null) {
                    $this->redirectBack();
                } else {
                    $this->redirectWithParameters();
                }
            }
        } else {
            $form = $this->getTranslationForm();

            if ($key !== null) {
                $form->getElement($form::ELEMENT_KEY)->setValue($key);
                $form->getElement($form::ELEMENT_MODULE)->setValue($module);
                $form->disableKeyEditing();
            }
        }

        // render form
        $this->_helper->viewRenderer->setNoRender(true);
        echo $form;
    }

    /**
     * Action for editing a single translation key.
     *
     * TODO check if form can be used to create arbitrary keys
     * TODO review and refactor if necessary
     */
    public function editAction()
    {
        $request = $this->getRequest();

        $post = null;

        $key = $this->getParam('key', null);

        if ($request->isPost()) {
            $post = $request->getPost();
            if (isset($post['Id'])) {
                $key = $post['Id'];
            }
            // TODO handle error (POST without 'Id' should not happen)
        }

        if ($key === null) {
            $this->_helper->Redirector->redirectTo('index');
        }

        $form = $this->getTranslationForm();

        if ($request->isPost()) {
            $post   = $request->getPost();
            $result = $form->processPost($post, $post);
            switch ($result) {
                case Setup_Form_Translation::RESULT_SAVE:
                    $form->populateFromKey($key);
                    $form->populate($post);

                    // add values for disabled elements to POST
                    if (! isset($post[$form::ELEMENT_KEY])) {
                        $post[$form::ELEMENT_KEY] = $key;
                    }

                    // KeyModule is not set if editing of module is disabled for edited key
                    if (! isset($post[$form::ELEMENT_MODULE])) {
                        $moduleElement = $form->getElement($form::ELEMENT_MODULE);
                        $moduleElement->setRequired(false);
                        $moduleElement->setValue(null);
                    }
                    // disable validation for duplicate keys when editing existing key
                    $form->getElement($form::ELEMENT_KEY)->removeValidator(
                        'Setup_Form_Validate_TranslationKeyAvailable'
                    );
                    if ($form->isValid($post)) {
                        $form->updateTranslation();
                        $form = null;
                        Application_Translate::getInstance()->clearCache(); // TODO encapsulate
                    }
                    // TODO ELSE go back to form if $post is not valid
                    break;
                case Setup_Form_Translation::RESULT_CANCEL:
                    // no break
                default:
                    // redirect back to search
                    $form = null;
            }
            if ($form === null) {
                if ($this->getParam('back', null) !== null) {
                    $this->redirectBack();
                } else {
                    $this->redirectWithParameters();
                }
                $form = null;
            }
        } else {
            $form->populateFromKey($key);
        }

        if ($form !== null) {
            $this->_helper->viewRenderer->setNoRender(true);
            echo $form;
        }
    }

    /**
     * Removes database entry for translations key from TMX files to reset the used value.
     *
     * If a "key" is provided as parameter the "all" parameter is ignored.
     * When reseting all changes a confirmation dialog is shown.
     * When reseting a single key the current and the original translation are shown.
     *
     * TODO handle removing all edited translations from database
     * TODO option to only reset current filter result
     */
    public function deleteAction()
    {
        $request = $this->getRequest();

        $key = $this->getParam('key', null);

        if ($request->isPost()) {
            $post = $request->getPost();
            if (isset($post['Id'])) {
                $key = $post['Id'];
            }
        }

        $manager = $this->getTranslationManager();

        $form = null;

        if ($key !== null) {
            $form = new Setup_Form_DeleteKeyConfirmation();

            if ($request->isPost()) {
                $result = $form->processPost($request->getPost());

                switch ($result) {
                    case $form::RESULT_YES:
                        if ($manager->keyExists($key)) {
                            $manager->delete($key);
                        }
                        // TODO ELSE key does not exist -> error invalid request

                        $form = null;
                        break;
                    case $form::RESULT_NO:
                        // fall through to default
                    default:
                        $form = null; // go back to index action
                        break;
                }
            } else {
                $form->setKey($key);

                if ($manager->isEdited($key)) {
                    $form->setQuestion('setup_language_confirm_reset_key');
                    $form->setLegend('setup_language_confirm_reset_key_title');
                } else {
                    $form->setQuestion('setup_language_confirm_delete_key');
                    $form->setLegend('setup_language_confirm_delete_key_title');
                }
            }
        }
        // ELSE ignore invalid requests (no key or all parameter)

        if ($form !== null) {
            $this->_helper->renderForm($form);
        } else {
            $this->redirectWithParameters();
        }
    }

    public function deleteallAction()
    {
        $request = $this->getRequest();

        $form = new Setup_Form_DeleteAllConfirmation();

        if ($request->isPost()) {
            $result = $form->processPost($request->getPost());

            switch ($result) {
                case $form::RESULT_YES:
                    $post = $request->getPost();
                    if ($form->isValid($post)) {
                        $delete = $form->getValue($form::ELEMENT_DELETE_ALL);
                        switch ($delete) {
                            case 'filter':
                                $manager = $this->getTranslationManager();
                                $this->setFilterParameters($manager);
                                $manager->deleteMatches();
                                break;
                            case 'all':
                                $manager = $this->getTranslationManager();
                                $manager->deleteAll();
                                break;
                            default:
                                // TODO error handling
                                break;
                        }
                        $form = null;
                    }
                    // TODO ELSE logging/message - go back to form
                    break;

                case $form::RESULT_NO:
                    // fall through to default
                default:
                    $form = null;
                    break;
            }
        }

        if ($form !== null) {
            $this->_helper->renderForm($form);
        } else {
            $this->redirectWithParameters();
        }
    }

    /**
     * Action for exporting custom translations.
     */
    public function exportAction()
    {
        $filename = $this->getParam('filename', null);

        if ($filename !== null) {
            $manager = $this->getTranslationManager();
            $this->setFilterParameters($manager);

            $unmodified = filter_var(
                $this->getParam('unmodified', null),
                FILTER_VALIDATE_BOOLEAN
            );

            $tmxFile = $manager->getExportTmxFile($unmodified);
            $doc     = $tmxFile->getDomDocument();

            $response = $this->getResponse();
            $response->setHeader('Content-Type', "text/xml; charset=UTF-8", true);
            $response->setHeader('Content-Disposition', "attachment; filename=opus.tmx", true);

            $this->disableViewRendering();
            echo $doc->saveXML();
        } else {
            $downloadLinks = [];

            $downloadLinks['setup_language_export_filtered'] = $this->view->url([
                'action'   => 'export',
                'filename' => 'opus.tmx',
            ]);

            $downloadLinks['setup_language_export_filtered_with_unmodified'] = $this->view->url([
                'action'     => 'export',
                'filename'   => 'opus.tmx',
                'unmodified' => 'true',
            ]);

            $downloadLinks['setup_language_export_all'] = $this->view->url([
                'action'     => 'export',
                'controller' => 'language',
                'module'     => 'setup',
                'filename'   => 'opus.tmx',
            ], null, true);

            $downloadLinks['setup_language_export_all_with_unmodified'] = $this->view->url([
                'action'     => 'export',
                'controller' => 'language',
                'module'     => 'setup',
                'filename'   => 'opus.tmx',
                'unmodified' => 'true',
            ], null, true);

            $this->view->downloadLinks = $downloadLinks;
        }
    }

    /**
     * Action for importing custom translations.
     */
    public function importAction()
    {
        $request = $this->getRequest();

        $form = new Setup_Form_ImportTranslations();

        if ($request->isPost()) {
            // TODO validate form
            $upload = new Zend_File_Transfer_Adapter_Http();
            $files  = $upload->getFileInfo();

            foreach ($files as $file => $fileInfo) {
                $manager = $this->getTranslationManager();
                if (isset($fileInfo['tmp_name'])) {
                    // TODO error handling (corrupt or incompatible file)

                    $post = $request->getPost();

                    if (isset($post['Clear']) === '1') {
                        $manager->deleteAll();
                    }

                    $tmxFile = new Application_Translate_TmxFile();
                    $tmxFile->load($fileInfo['tmp_name']);

                    if (count($tmxFile->toArray()) > 0) {
                        $manager->importTmxFile($tmxFile);
                        $manager->clearCache();
                    }
                    // TODO ELSE redirect with message

                    $this->redirectWithParameters();
                }
            }
        } else {
            // show information and upload form
            $this->view->form = $form;
        }
    }

    /**
     * Action for editing general language settings for the user interface.
     *
     *
     * TODO modify configuration
     * TODO create tests
     */
    public function settingsAction()
    {
        // TODO provide form with options config

        $form = new Admin_Form_Configuration([
            'supportedLanguages' => [
                'key'     => 'activatedLanguages',
                'type'    => 'supportedLanguages',
                'section' => 'general',
            ],
        ]);

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();

            $form->populate($data);

            $result = $form->processPost($data, $data);

            switch ($result) {
                case Admin_Form_Configuration::RESULT_SAVE:
                    if ($form->isValid($data)) {
                        $config = new Zend_Config([], true);
                        $form->updateModel($config);
                        Application_Configuration::save($config);
                    } else {
                        break;
                    }
                // after saving fall through for same redirect as 'Cancel'
                case Admin_Form_Configuration::RESULT_CANCEL:
                    $this->redirectWithParameters();
                    break;
                default:
                    break;
            }
        } else {
            $config = $this->getConfig();

            $form->populateFromModel($config);

            $element = $form->getElement('supportedLanguages');
            if (! isset($config->activatedLanguages)) {
                $element->setValue($config->supportedLanguages);
            }
        }

        $this->_helper->viewRenderer->setNoRender(true);

        echo $form;
    }

    /**
     * @return Setup_Form_Translation
     */
    protected function getTranslationForm()
    {
        return new Setup_Form_Translation();
    }

    /**
     * @param string|null $searchTerm
     * @param string|null $sortKey
     * @return Setup_Form_LanguageSearch
     */
    protected function getSearchForm($searchTerm = null, $sortKey = null)
    {
        $sortKeysTranslated = [];

        $sortKeys = array_diff($this->sortKeys, ['language', 'translation']);

        foreach ($sortKeys as $option) {
            $sortKeysTranslated[$option] = $this->view->translate('setup_language_' . $option);
        }

        $form = new Setup_Form_LanguageSearch();

        $form->setAttrib('id', 'filter');

        $form->getElement(Setup_Form_LanguageSearch::ELEMENT_FILTER)->setLabel(
            $this->view->translate('setup_language_searchTerm')
        );

        // remove search parameter from URL (gets set when returning from edit forms)
        $form->setAction($this->view->url([
            'action'     => 'index',
            'controller' => 'language',
            'module'     => 'setup',
            'search'     => null,
        ], null, true));

        if (! empty($searchTerm)) {
            $form->search->setValue($searchTerm);
        }

        return $form;
    }

    /**
     * @return Application_Translate_TranslationManager
     */
    protected function getTranslationManager()
    {
        return new Application_Translate_TranslationManager();
    }

    /**
     * @param string $action
     */
    protected function redirectWithParameters($action = 'index')
    {
        $this->_helper->Redirector->redirectTo(
            $action,
            null,
            'language',
            'setup',
            [
                self::PARAM_SEARCH => $this->getParam(self::PARAM_SEARCH),
                self::PARAM_MODULE => $this->getParam(self::PARAM_MODULE),
                self::PARAM_SCOPE  => $this->getParam(self::PARAM_SCOPE),
                self::PARAM_STATE  => $this->getParam(self::PARAM_STATE),
                self::PARAM_SORT   => $this->getParam(self::PARAM_SORT),
            ]
        );
    }

    protected function redirectBack()
    {
        $key = $this->getParam('key');

        $url = '/home/index/help';

        if (! empty($key)) {
            $url .= "#$key";
        }

        $this->_helper->Redirector->gotoUrl($url);
    }

    /**
     * @param Application_Translate_TranslationManager $manager
     */
    protected function setFilterParameters($manager)
    {
        $searchTerm = $this->getParam('search', null);
        $modules    = $this->getParam('modules', null);
        $state      = $this->getParam('state', null);
        $scope      = $this->getParam('scope', null);

        if (! empty($searchTerm)) {
            $manager->setFilter($searchTerm);
        }

        // check if modules parameter is allowed
        $allowedModules = $manager->getAllowedModules();
        if ($modules === null || ($allowedModules !== null && ! in_array($modules, $allowedModules)) || strcasecmp($modules, 'all') === 0) {
            // TODO log unknown modules ?
            $modules = null;
        }

        $manager->setModules($modules);

        switch (strtolower($state ?? '')) {
            case 'edited':
                $manager->setState($manager::STATE_EDITED);
                break;
            case 'added':
                $manager->setState($manager::STATE_ADDED);
                break;
            default:
                $manager->setState(null);
                break;
        }
        switch (strtolower($scope ?? '')) {
            case 'key':
                $manager->setScope($manager::SCOPE_KEYS);
                break;
            case 'text':
                $manager->setScope($manager::SCOPE_TEXT);
                break;
            default:
                $manager->setScope(null);
                break;
        }
    }
}
