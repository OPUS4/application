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
 * @package     Controller
 * @author      Edouard Simon (edouard.simon@zib.de)
 * @copyright   Copyright (c) 2008-2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 *
 */
abstract class Application_Controller_SetupAbstract extends Application_Controller_Action {

    abstract protected function getModel();

    abstract protected function getForm();

    public function editAction() {

        try {
            $model = $this->getModel();

            $dataForm = $this->getForm();

            $form = new Zend_Form();
            $form->addSubForm($dataForm, 'data');
            $form->addElement('submit', $this->view->translate('Save'));

            if ($this->_request->isPost()) {
                $postData = $this->_request->getPost('data');
                if ($dataForm->isValid($postData)) {
                    $this->view->messages = array();
                    $model->fromArray($postData);
                    $stored = $model->store();
                    if (!$stored) {
                        $dataForm->populate($postData);
                        $this->view->messages[] = array('level' => 'failure',
                            'message' => $this->view->translate('setup_message_write-failed'));
                    }
                    else {
                        $this->view->messages[] = array('level' => 'notice',
                            'message' => $this->view->translate('setup_message_write-success'));
                        Zend_Translate::clearCache();
                    }
                }
                else {
                    $this->view->messages[] = array('level' => 'failure',
                        'message' => 'Es ist ein Fehler aufgetreten. Bitte überprüfen Sie Ihre Eingaben.');
                }
            }
            else {
                $formData = $model->toArray();
                $dataForm->populate($formData);
            }

            $this->view->form = $form;
        }
        catch (Setup_Model_FileNotReadableException $exc) {
            $this->_helper->Redirector->redirectTo(
                'error',
                array('failure' => $this->view->translate('setup_message_error_read-access', $exc->getMessage()))
            );
        }
        catch (Setup_Model_FileNotWriteableException $exc) {
            $this->_helper->Redirector->redirectTo(
                'error',
                array('failure' => $this->view->translate('setup_message_error_write-access', $exc->getMessage()))
            );
        }
        catch (Setup_Model_FileNotFoundException $exc) {
            $this->_helper->Redirector->redirectTo(
                'error',
                array('failure' => $this->view->translate('setup_message_error_filenotfound', $exc->getMessage()))
            );
        }
        $this->render('edit', null, true);
    }

    public function errorAction() {
        $this->view->backLink = $this->view->url(
            array('controller' => $this->getRequest()->getControllerName(),
            'action' => 'index')
        );
        $this->render('error', null, true);
    }

}
