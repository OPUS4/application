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

use Opus\Common\Translate\UnknownTranslationKeyException;

/**
 * TODO show instructions for editing help content
 * TODO form for editing help.ini file
 * TODO improve styling of FAQ page
 */
class Setup_HelppageController extends Application_Controller_Action
{
    public function init()
    {
        parent::init();

        $this->getHelper('MainMenu')->setActive('admin');
        $this->view->headLink()->appendStylesheet($this->view->layoutPath() . '/css/setup.css');
    }

    public function indexAction()
    {
    }

    /**
     * Action for editing structure of FAQ page (help.ini).
     *
     * TODO load ini
     * TODO validate ini
     * TODO display unknown entries on FAQ
     */
    public function structureAction()
    {
        $request = $this->getRequest();

        $help = new Setup_Model_HelpPage();

        $config = $help->loadConfig();

        $form = new Setup_Form_HelpConfig();

        if ($request->isPost()) {
            $post   = $request->getPost();
            $result = $form->processPost($post);
            switch ($result) {
                case $form::RESULT_SAVE:
                    $form->populate($post);
                    $content = $form->getValue($form::ELEMENT_STRUCTURE);
                    $help->saveConfig($content);
                    $form = null;
                    break;

                case $form::RESULT_CANCEL:
                    $form = null;
                    // fall through to default
                default:
                    break;
            }

            if ($form === null) {
                $this->redirectWithParameters();
            }
        } else {
            $form->getElement($form::ELEMENT_STRUCTURE)->setValue($config);
        }

        $this->_helper->renderForm($form);
    }

    /**
     * Action for editing FAQ entry.
     *
     * @throws Zend_Form_Exception
     *
     * TODO handle creating new entry
     */
    public function editAction()
    {
        $request = $this->getRequest();

        $name = $this->getParam('id', null);

        $form = null;

        if ($name !== null) {
            $form = new Setup_Form_FaqItem();
            $form->setName($name);

            if ($request->isPost()) {
                $post = $request->getPost();

                $result = $form->processPost($post, $post);

                switch ($result) {
                    case $form::RESULT_SAVE:
                        $form->populate($post);
                        $form->updateEntry();
                        $form = null;
                        break;

                    case $form::RESULT_CANCEL:
                        $form = null;
                        // fall through to default
                    default:
                        break;
                }
                // TODO Check if valid
                // TODO store changes
                // TODO go back to help page at right position
            } else {
                $form->setName($name);
            }
        }

        if ($form === null) {
            $this->redirectBack();
        } else {
            $this->_helper->renderForm($form);
        }
    }

    protected function redirectBack()
    {
        $help = Application_Translate_Help::getInstance();

        $faqId = $this->getParam('id');

        $url = '/home/index/help';

        if (! empty($faqId)) {
            if ($help->getSeparateViewEnabled()) {
                $url .= "/content/$faqId";
            } else {
                $url .= "#$faqId";
            }
        }

        $this->_helper->Redirector->gotoUrl($url);
    }

    /**
     * Redirect to create or editing of translation keys.
     */
    public function sectionAction()
    {
        $key = $this->getParam('key', null);

        $manager = new Application_Translate_TranslationManager();

        $translation = null;

        try {
            $translation = $manager->getTranslation($key);
        } catch (UnknownTranslationKeyException $ex) {
        }

        if ($translation === null) {
            $this->_helper->Redirector->redirectTo(
                'add',
                null,
                'language',
                'setup',
                [
                    'key'       => $key,
                    'keymodule' => 'help',
                    'back'      => 'help',
                ]
            );
        } else {
            $this->_helper->Redirector->redirectTo(
                'edit',
                null,
                'language',
                'setup',
                [
                    'key'  => $key,
                    'back' => 'help',
                ]
            );
        }
    }

    /**
     * Action for deleting FAQ entry.
     */
    public function deleteAction()
    {
    }

    /**
     * @param string $action
     */
    protected function redirectWithParameters($action = 'index')
    {
        $this->_helper->Redirector->redirectTo(
            $action,
            null,
            'helppage',
            'setup'
        );
    }
}
