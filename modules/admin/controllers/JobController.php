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

use Opus\Common\Job;

/**
 * Controller für die Anzeige von Informationen über Background-Jobs.
 */
class Admin_JobController extends Application_Controller_Action
{
    public function indexAction()
    {
        $config = $this->getConfig();

        if (isset($config->runjobs->asynchronous) && filter_var($config->runjobs->asynchronous, FILTER_VALIDATE_BOOLEAN)) {
            $this->view->asyncjobs           = true;
            $this->view->failedJobCount      = Job::getCountPerLabel(Job::STATE_FAILED);
            $this->view->unprocessedJobCount = Job::getCountPerLabel(Job::STATE_UNDEFINED);
        } else {
            $this->view->asyncjobs = false;
        }
    }

    public function menuAction()
    {
        $this->view->title = $this->view->translate('admin_title_job');
    }

    /**
     * TODO review functionality and create ticket
     */
    public function workerMonitorAction()
    {
        $config = $this->getConfig();
        $this->_helper->layout()->disableLayout();
        if (isset($config->runjobs->asynchronous) && filter_var($config->runjobs->asynchronous, FILTER_VALIDATE_BOOLEAN)) {
            $this->view->failedJobCount = Job::getCount(Job::STATE_FAILED);
        } else {
            $this->view->failedJobCount = 0;
        }
    }

    public function detailAction()
    {
        $this->view->state = $this->_request->getParam('state');
        $this->view->label = $this->_request->getParam('label');

        if (empty($this->view->state) || empty($this->view->label)) {
            throw new Application_Exception('Invalid arguments');
        }

        $this->view->jobs = Job::getByLabels([$this->view->label], null, $this->view->state);
    }
}
