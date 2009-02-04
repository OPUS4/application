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
 * @package     Module_Licence
 * @author      Wolfgang Filter (wolfgang.filter@ub.uni-stuttgart.de)
 * @author      Felix Ostrowski <ostrowski@hbz-nrw.de>
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Main entry point for this module.
 *
 * @category    Application
 * @package     Module_Licence
 */
class Admin_LicenceController extends Zend_Controller_Action {

    /**
     * Redirector - defined for code completion
     *
     * @var Zend_Controller_Action_Helper_Redirector
     */
    protected $_redirector = null;

    /**
     * Do some initialization on startup of every action
     *
     * @return void
     */
    public function init()
    {
        $this->_redirector = $this->_helper->getHelper('Redirector');
    }

    /**
     * Display licence creation form.
     *
     * @return void
     *
     */
    public function indexAction() {
        $form_builder = new Opus_Form_Builder();
        $licence = new Opus_Model_Licence();
        $licenceForm = $form_builder->build($licence);
        $action_url = $this->view->url(array("controller" => "licence", "action" => "create"));
        $licenceForm->setAction($action_url);
        $this->view->title = 'Licence';
        $this->view->form = $licenceForm;
    }

    /**
     * Add licence.
     *
     * @return void
     */
    public function createAction() {
        if ($this->_request->isPost() === true) {
            $data = $this->_request->getPost();
            $form_builder = new Opus_Form_Builder();
            $form = $form_builder->buildFromPost($data);
            if ($form->isValid($data) === true) {
                // retrieve values from form and save them into model
                $licence = $form_builder->getModelFromForm($form);
                $form_builder->setFromPost($licence, $form->getValues());
                $licence->store();
                $this->view->licence_data = $licence->toArray();
            }
        } else {
            $this->_redirector->gotoSimple('index');
        }
    }

}
