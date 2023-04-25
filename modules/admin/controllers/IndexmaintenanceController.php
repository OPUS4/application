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

class Admin_IndexmaintenanceController extends Application_Controller_Action
{
    /** @var Admin_Model_IndexMaintenance */
    private $model;

    public function init()
    {
        parent::init();
        $this->model = new Admin_Model_IndexMaintenance($this->getLogger());

        // TODO features will be enabled in later version
        $this->view->disabledFeatureFulltextExtractionCheck = true; // TODO OPUSVIER-2955
        $this->view->disabledFeatureIndexOptimization       = true; // TODO OPUSVIER-2956

        if ($this->model->getFeatureDisabled()) {
            $this->view->featureDisabled = true;
        } else {
            $this->view->allowConsistencyCheck        = $this->model->allowConsistencyCheck();
            $this->view->allowFulltextExtractionCheck = $this->model->allowFulltextExtractionCheck();
            $this->view->allowIndexOptimization       = $this->model->allowIndexOptimization();
        }
    }

    public function indexAction()
    {
        if (! $this->model->getFeatureDisabled()) {
            $state             = $this->model->getProcessingState();
            $this->view->state = ['consistencycheck' => $state];
            if ($state === 'scheduled' || $state === 'completed') {
                $data = $this->model->readLogFile();
                if ($data !== null) {
                    $this->view->content            = ['consistencycheck' => $data->getContent()];
                    $this->view->contentLastModTime = ['consistencycheck' => $data->getModifiedDate()];
                }
            }
            if ($state === null) {
                $this->view->error = ['consistencycheck' => true];
            }
        }
    }

    public function checkconsistencyAction()
    {
        if (! $this->model->getFeatureDisabled() && $this->getRequest()->isPost()) {
            $jobId = $this->model->createJob();
            if ($jobId !== null) {
                $this->_helper->Redirector->redirectToAndExit(
                    'index',
                    $this->view->translate(
                        'admin_indexmaintenance_jobsubmitted',
                        [$jobId]
                    )
                );
                return;
            }
        }
        $this->_helper->Redirector->redirectToAndExit('index');
    }

    /**
     * TODO implementation needed OPUSVIER-2956
     */
    public function optimizeindexAction()
    {
        // if (! $this->model->getFeatureDisabled() && $this->getRequest()->isPost()) {
            // TODO add a job
        //}
        $this->_helper->Redirector->redirectToAndExit('index');
    }

    /**
     * TODO implementation needed OPUSVIER-2955
     */
    public function checkfulltextsAction()
    {
        // if (! $this->model->getFeatureDisabled() && $this->getRequest()->isPost()) {
            // TODO add a job
        // }
        $this->_helper->Redirector->redirectToAndExit('index');
    }
}
