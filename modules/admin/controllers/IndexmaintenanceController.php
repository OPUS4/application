<?php
/**
 *
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
 * @package     Module_Admin
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Admin_IndexmaintenanceController extends Application_Controller_Action {

    /**
     * @var Admin_Model_IndexMaintenance
     */
    private $_model;

    public function init() {
        parent::init();
        $this->_model = new Admin_Model_IndexMaintenance($this->getLogger());


        // TODO features will be enabled in later version
        $this->view->disabledFeatureFulltextExtractionCheck = true; // TODO OPUSVIER-2955
        $this->view->disabledFeatureIndexOptimization = true; // TODO OPUSVIER-2956


        if ($this->_model->getFeatureDisabled()) {
            $this->view->featureDisabled = true;
        }
        else {
            $this->view->allowConsistencyCheck = $this->_model->allowConsistencyCheck();
            $this->view->allowFulltextExtractionCheck = $this->_model->allowFulltextExtractionCheck();
            $this->view->allowIndexOptimization = $this->_model->allowIndexOptimization();
        }
    }

    public function indexAction() {
        if (!$this->_model->getFeatureDisabled()) {
            $state = $this->_model->getProcessingState();
            $this->view->state = array('consistencycheck' => $state);
            if ($state == 'scheduled' || $state == 'completed') {
                $data = $this->_model->readLogFile();
                if (!is_null($data)) {
                    $this->view->content = array('consistencycheck' => $data->getContent());
                    $this->view->contentLastModTime = array('consistencycheck' => $data->getModifiedDate());
                }
            }
            if (is_null($state)) {
                $this->view->error = array('consistencycheck' => true);
            }
        }
    }

    public function checkconsistencyAction() {
        if (!$this->_model->getFeatureDisabled() && $this->getRequest()->isPost()) {
            $jobId = $this->_model->createJob();
            if (!is_null($jobId)) {
                return $this->_helper->Redirector->redirectToAndExit(
                    'index', $this->view->translate(
                        'admin_indexmaintenance_jobsumitted',
                        $jobId
                    )
                );
            }
        }
        return $this->_helper->Redirector->redirectToAndExit('index');
    }

    /**
     *
     * TODO implementation needed OPUSVIER-2956
     */
    public function optimizeindexAction() {
        if (!$this->_model->getFeatureDisabled() && $this->getRequest()->isPost()) {
            // add a job
        }
        return $this->_helper->Redirector->redirectToAndExit('index');
    }

    /**
     *
     * TODO implementation needed OPUSVIER-2955
     */
    public function checkfulltextsAction() {
        if (!$this->_model->getFeatureDisabled() && $this->getRequest()->isPost()) {
            // add a job
        }
        return $this->_helper->Redirector->redirectToAndExit('index');
    }

}
