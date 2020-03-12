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
 * @author      Edouard Simon <edouard.simon@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * TODO show controller (functionality) in menu (currently hidden)
 * TODO limit editable keys to specific modules (?)
 * TODO update documentation
 * TODO rename controller to TranslationController
 * TODO sorting using table header
 * TODO link for adding new translations
 * TODO show module in translations
 *
 * After canceling an Edit form the user gets returned to the search page displaying the last search.
 *
 */
class Setup_LanguageController extends Application_Controller_Action
{

    protected $_sortKeys = ['key', 'language', 'variant'];

    public function init()
    {
        parent::init();

        $this->getHelper('MainMenu')->setActive('admin');
    }

    public function indexAction()
    {
        $this->view->form = $this->getSearchForm();
    }

    /**
     * @throws Setup_Model_FileNotReadableException
     *
     * TODO move handling of allowed modules into manager
     */
    public function showAction()
    {
        $searchTerm = $this->getParam('search');
        $sortKey = $this->getParam('sort', 'key');
        $config = $this->getConfig();

        if (! isset($config->setup->translation->modules->allowed)) {
            $this->_helper->Redirector->redirectTo(
                'error',
                ['failure' => 'setup_language_translation_modules_missing']
            );
        }

        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();
            $searchTerm = isset($post['search']) ? $post['search'] : $searchTerm;
        }

        $translationManager = $this->getTranslationManager();

        if (! empty($searchTerm)) {
            $translationManager->setFilter($searchTerm);
        }

        $this->view->form = $this->getSearchForm($searchTerm, $sortKey);

        $this->view->translations = $translationManager->getMergedTranslations($sortKey);
        $this->view->sortKeys = $this->_sortKeys;
        $this->view->currentSortKey = $sortKey;
        $this->view->searchTerm = $searchTerm;
    }

    /**
     * Action for adding a new translation key.
     *
     * TODO form with new key name
     * TODO action shows form and processes submit
     */
    public function addAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();
            $form = $this->getTranslationForm(true);
            $result = $form->processPost($post, $post);

            switch($result) {
                case Setup_Form_Translation::RESULT_SAVE:
                    // TODO save new translation key
                    // TODO check if key already exists
                    break;
                case Setup_Form_Translation::RESULT_CANCEL:
                default:
            }

            $this->_helper->Redirector->redirectTo(
                'show', null, 'language', 'setup',
                ['search' => $this->getParam('search')]
            );
        } else {
            $form = $this->getTranslationForm();

            // render form
            $this->_helper->viewRenderer->setNoRender(true);
            echo $form;
        }
    }

    /**
     * Action for editing a single translation key.
     *
     * TODO use Translation form
     * TODO action shows form and processes submit
     */
    public function editAction()
    {
        $translationKey = $this->getParam('key');

        if (is_null($translationKey)) {
            $this->_helper->Redirector->redirectTo('show');
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            $form = $this->getTranslationForm();
            $result = $form->processPost($post, $post);
            switch ($result) {
                case Application_Form_Translations::RESULT_SAVE:
                    $form->addKey($translationKey, true);
                    $form->populate($post);
                    $form->updateTranslations();
                    break;
                case Application_Form_Translations::RESULT_CANCEL:
                    // no break
                default:
                    // redirect back to search
            }
            // TODO process form
            // TODO validate
            // TODO save
            $this->_helper->Redirector->redirectTo(
                'show', null, 'language', 'setup',
                ['search' => $this->getParam('search')]
            );
        } else {
            $form = $this->getTranslationForm();
            $form->addKey($translationKey, true);
            $form->populateFromTranslations();
            // TODO use key as label (use different form?)

            $this->_helper->viewRenderer->setNoRender(true);
            echo $form;
        }
    }

    /**
     * Removes database entry for translations key from TMX files to reset the used value.
     *
     * TODO show confirmation form
     * TODO handle removing all edited translations from database
     */
    public function resetAction()
    {
        $all = $this->getParam('all', false);

        if (filter_var($all, FILTER_VALIDATE_BOOLEAN)) {
            $all = true;
        }

        $key = $this->getParam('key', null);

        if (! is_null($key) || $all) {

            $request = $this->getRequest();

            if ($request->isPost()) {
                $form = $this->getConfirmationForm();

                $result = $form->processPost($request->getPost());

                switch($result) {
                    case Setup_Form_Confirmation::RESULT_NO:
                        $this->_helper->Redirector->redirectTo(
                            'show', null, 'language', 'setup',
                            ['search' => $this->getParam('search')]
                        );
                        break;
                    default:
                }


                $translationManager = $this->getTranslationManager();



                if ($translationManager->keyExists($key)) {
                    $translationManager->reset($key);
                } else {
                    // TODO error invalid request
                }
            } else {
                $form = $this->getConfirmationForm();
                $form->setQuestion('setup_language_confirm_reset_all');
                $form->setLegend('setup_language_confirm_reset_all_title');

                $this->_helper->renderForm($form);
                return;
            }
        } else {
            // TODO error invalid request
        }

        $this->_helper->Redirector->redirectTo(
            'show', null, 'language', 'setup',
            ['search' => $this->getParam('search')]
        );
    }

    public function deleteAction()
    {

    }

    /**
     * Action for exporting custom translations.
     *
     * TODO make sure edited keys are stored with module information
     */
    public function exportAction()
    {
        $filename = $this->getParam('filename', null);

        if (! is_null($filename)) {
            $manager = $this->getTranslationManager();

            $tmxFile = $manager->getExportTmxFile();

            $doc = $tmxFile->getDomDocument();

            $this->disableViewRendering();

            $response = $this->getResponse();

            $response->setHeader('Content-Type', "text/xml; charset=UTF-8", true);
            $response->setHeader('Content-Disposition', "attachment; filename=opus.tmx", true);

            echo $doc->saveXML();
        } else {
            // show information and options for download
        }
    }

    /**
     * Action for importing custom translations.
     */
    public function importAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            // process upload post

        } else {
            // show information and upload form
            $form = new Setup_Form_ImportTmxFile();
            $this->view->form = $form;
        }
    }

    protected function getTranslationForm($add = false)
    {
        $form = new Setup_Form_Translation();

        if ($add) {
            $form->setKeyEditable(true);
            $form->addTranslationElement();
        }

        return $form;
    }

    protected function getSearchForm($searchTerm = null, $sortKey = null)
    {
        $sortKeysTranslated = [];

        $sortKeys = array_diff($this->_sortKeys, ['language', 'variant']);

        foreach ($sortKeys as $option) {
            $sortKeysTranslated[$option] = $this->view->translate('setup_language_' . $option);
        }

        $form = new Setup_Form_LanguageSearch();

        $form->getElement('search')->setLabel($this->view->translate('setup_language_searchTerm'));

        // remove search parameter from URL (gets set when returning from edit forms)
        $form->setAction($this->view->url(['action' => 'show', 'search' => null]));

        if (! empty($searchTerm)) {
            $form->search->setValue($searchTerm);
        }

        return $form;
    }

    protected function getTranslationManager()
    {
        $config = $this->getConfig();

        $moduleNames = explode(',', $config->setup->translation->modules->allowed);

        $translationManager = new Application_Translate_TranslationManager();
        $translationManager->setModules($moduleNames);

        return $translationManager;
    }

    protected function getConfirmationForm()
    {
        $form = new Setup_Form_Confirmation();

        return $form;
    }
}
