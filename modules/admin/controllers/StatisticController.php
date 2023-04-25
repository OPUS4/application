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

use Opus\Common\Date;

class Admin_StatisticController extends Application_Controller_Action
{
    /** @var Admin_Model_Statistics */
    private $statisticsModel;

    public function init()
    {
        parent::init();
        $this->statisticsModel = new Admin_Model_Statistics();
    }

    public function indexAction()
    {
        $this->view->title = 'admin_title_statistic';

        $years = $this->statisticsModel->getYears();

        $highest = max($years);

        $selectYear = new Zend_Form_Element_Select(
            'selectedYear',
            ["multiOptions" => $years, "value" => $highest]
        );

        $selectYear->setRequired(true)
            ->setLabel($this->view->translate('Select_Year_Label'));

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel($this->view->translate('Submit_Button_Label'));

        $form = new Zend_Form();
        $form->setAction($this->view->url(["controller" => "statistic", "action" => "show"]));
        $form->addElements([$selectYear, $submit]);

        $this->view->form = $form;
    }

    public function showAction()
    {
        $selectedYear = $this->getRequest()->getParam('selectedYear', null);

        if ($selectedYear === null || ! in_array($selectedYear, $this->statisticsModel->getYears())) {
            $this->_helper->Redirector->redirectToAndExit('index');
            return;
        }

        $this->view->languageSelectorDisabled = true;

        $date = new Date();
        $date->setYear($selectedYear)->setMonth(12)->setDay(31);
        $this->view->dateThreshold = $this->getHelper('Dates')->getDateString($date);

        $this->view->selectedYear = $selectedYear;
        $this->view->sumDocsUntil = $this->statisticsModel->getNumDocsUntil($selectedYear);

        $monthStat = $this->statisticsModel->getMonthStatistics($selectedYear);

        $this->view->totalNumber = array_sum($monthStat);
        $this->view->title       = $this->view->translate('Statistic_Controller') . ' ' . $selectedYear;
        $this->view->monthStat   = $monthStat;

        $this->view->typeStat = $this->statisticsModel->getTypeStatistics($selectedYear);
        $this->view->instStat = $this->statisticsModel->getInstituteStatistics($selectedYear);

        $this->breadcrumbs->setLabelFor('admin_statistic_show', $selectedYear);
    }
}
