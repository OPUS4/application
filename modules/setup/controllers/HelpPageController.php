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
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 *
 */
class Setup_HelpPageController extends Controller_Action {

    protected $config;

    public function init() {
        parent::init();
        $this->config = new Zend_Config_Ini(APPLICATION_PATH . '/modules/setup/setup.ini', 'help');
    }

    public function indexAction() {

        try {
            $helpPage = new Setup_Model_HelpPage($this->config);

            $form = new Setup_Form_HelpPage();
            $form->addElement('submit', $this->view->translate('Save'));

            if ($this->_request->isPost()) {
                $postData = $this->_request->getPost('tmxData');
                if ($form->tmxData->isValid($postData)) {
                    $this->view->messages = array();
                    $helpPage->fromArray($postData);
                    $stored = $helpPage->store();
                    if (!$stored) {
                        $form->tmxData->populate($postData);
                        $this->view->messages[] = array('level' => 'failure', 'message' => $this->view->translate('setup_help-page_write-failed'));
                    } else {
                        $this->view->messages[] = array('level' => 'notice', 'message' => $this->view->translate('setup_help-page_write-success'));
                        Zend_Translate::clearCache();
                    }
                } else {
                    $this->view->messages[] = array('level' => 'failure', 'message' => 'Es ist ein Fehler aufgetreten. Bitte überprüfen Sie Ihre Eingaben.');
                }
            } else {
                $formData = $helpPage->toArray();
                $form->tmxData->populate($formData);
            }

            $this->view->form = $form;
            
        } catch (Setup_Model_FileNotReadableException $exc) {
            $this->_redirectTo('error', array('failure' => $this->view->translate('setup_help-page_error_read-access', $exc->getMessage())));
        } catch (Setup_Model_FileNotWriteableException $exc) {
            $this->_redirectTo('error', array('failure' => $this->view->translate('setup_help-page_error_write-access', $exc->getMessage())));
        }
    }

    public function errorAction() {
        $this->view->controller = $this->getRequest()->getControllerName();
    }

}
