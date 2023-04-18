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

use Opus\Statistic\LocalCounter;

class Statistic_GraphController extends Application_Controller_Action
{
    /**
     * @param string $title
     * @param array  $dataPdf
     * @param array  $dataFrontdoor
     */
    protected function buildGraph($title, $dataPdf, $dataFrontdoor)
    {
    }

    public function indexAction()
    {
        $this->_forward('year');
    }

    /**
     * Generate PNG file that shows graph with year overview
     */
    public function yearAction()
    {
        $this->disableViewRendering();

        $id = $this->getRequest()->getParam('id');
        if (isset($id) === false) {
            //TODO: create own exception

            throw new Exception("Parameter id must be set.");
        }
        $dataPdf       = LocalCounter::getInstance()->readYears($id);
        $dataFrontdoor = LocalCounter::getInstance()->readYears($id, 'frontdoor');
        $years         = array_merge(array_keys($dataFrontdoor), array_keys($dataPdf));
        if (count($years) === 0) {
            $years = [date('Y')];
        }
        foreach ($years as $year) {
            if (isset($dataPdf[$year]) === false) {
                $dataPdf[$year] = 0;
            }
            if (isset($dataFrontdoor[$year]) === false) {
                $dataFrontdoor[$year] = 0;
            }
        }
        ksort($dataPdf);
        ksort($dataFrontdoor);

        $graph = new Statistic_Model_StatisticGraph(
            $this->view->translate('graph_year_title'),
            $dataPdf,
            $dataFrontdoor
        );
        $graph->setXAxisTitle($this->view->translate('graph_year_xaxis'));
        $graph->setYAxisTitle($this->view->translate('graph_yaxis'));
        $graph->setLegendFilesLabel($this->view->translate('graph_legend_files'));
        $graph->setLegendFrontdoorLabel($this->view->translate('graph_legend_frontdoor'));

        $graph->drawGraph();
    }

    /**
     * Generate PNG file that shows graph with month overview
     */
    public function monthAction()
    {
        $this->disableViewRendering();

        $id = $this->getRequest()->getParam('id');
        if (isset($id) === false) {
            //TODO: create own exception
            throw new Exception("Parameter id must be set.");
        }
        $dataPdf       = LocalCounter::getInstance()->readMonths($id);
        $dataFrontdoor = LocalCounter::getInstance()->readMonths($id, 'frontdoor');

        for ($i = 1; $i < 13; $i++) {
            if (isset($dataPdf[$i]) === false) {
                $dataPdf[$i] = 0;
            }
            if (isset($dataFrontdoor[$i]) === false) {
                $dataFrontdoor[$i] = 0;
            }
        }

        ksort($dataPdf);
        ksort($dataFrontdoor);

        $graph = new Statistic_Model_StatisticGraph(
            $this->view->translate('graph_month_title'),
            $dataPdf,
            $dataFrontdoor
        );
        $graph->setXAxisTitle($this->view->translate('graph_month_xaxis'));
        $graph->setYAxisTitle($this->view->translate('graph_yaxis'));
        $graph->setLegendFilesLabel($this->view->translate('graph_legend_files'));
        $graph->setLegendFrontdoorLabel($this->view->translate('graph_legend_frontdoor'));

        $graph->drawGraph();
    }

    /**
     * Generate PNG file that shows graph for thumbnail
     */
    public function thumbAction()
    {
        $this->disableViewRendering();

        $id = $this->getRequest()->getParam('id');
        if (isset($id) === false) {
            //TODO: create own exception
            throw new Exception("Parameter id must be set.");
        }
        //send layout path to view so that icons can be shown in different layouts
        //TODO maybe there is a more elegant way to do this!?
        $layoutPath = $this->view->layout()->getLayoutPath();

        $graph = new Statistic_Model_StatisticGraphThumb([90, 150, 30], $layoutPath . '/img/statistics_bg.jpg');
        $graph->drawGraph();
    }
}
