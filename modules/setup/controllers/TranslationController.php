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
 * TODO rename controller to InfopagesController (or something better)
 */
class Setup_TranslationController extends Application_Controller_Action
{
    /** @var string[] */
    private $validPages = [
        'home'    => 'Setup_Form_HomePage',
        'contact' => 'Setup_Form_ContactPage',
        'imprint' => 'Setup_Form_ImprintPage',
    ];

    public function init()
    {
        parent::init();
        $this->getHelper('MainMenu')->setActive('admin');
        $this->view->headLink()->appendStylesheet($this->view->layoutPath() . '/css/setup.css');
    }

    public function indexAction()
    {
        $this->view->pageNames = array_keys($this->validPages);
    }

    public function editAction()
    {
        $page = $this->getParam('page');

        if (in_array($page, array_keys($this->validPages))) {
            $formClass = $this->validPages[$page];
            $form      = new $formClass();

            $request = $this->getRequest();

            if ($request->isPost()) {
                $data = $request->getPost();
                $form->populate($data);
                switch ($form->processPost($data, $data)) {
                    case Application_Form_Translations::RESULT_SAVE:
                        $form->updateTranslations();
                        $this->_helper->Redirector->redirectTo('index', 'setup_message_write_success');
                        break;
                    case Application_Form_Translations::RESULT_CANCEL:
                        $this->_helper->Redirector->redirectTo('index');
                        break;
                    default:
                        break;
                }
            } else {
                $this->_helper->viewRenderer->setNoRender(true);
                echo $form;
            }
        }
        // TODO redirect in in_array returns false (ELSE) with error message
    }
}
