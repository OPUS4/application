<?php
include_once("jpgraph/jpgraph.php");
include_once("jpgraph/jpgraph_bar.php");

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
 * @package     Module_Statistic
 * @author      Birgit Dressler (b.dressler@sulb.uni-saarland.de)
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Statistic_Model_StatisticGraph
{

    protected $_dataPdf = null;
    protected $_dataFrontdoor = null;
    protected $_xaxis = 'x axis';
    protected $_yaxis = 'y axis';
    protected $_frontdoorLabel = 'frontdoor';
    protected $_filesLabel = 'files';
    protected $_title;

    protected $_width = 330;
    protected $_height = 200;

    public function __construct($title = 'Statistic Graph', $dataPdf = null, $dataFrontdoor = null)
    {
        $this->_title = $title;
        $this->_dataPdf = $dataPdf;
        $this->_dataFrontdoor = $dataFrontdoor;
    }

    public function setXAxisTitle($title)
    {
        $this->_xaxis = $title;
    }

    public function setYAxisTitle($title)
    {
        $this->_yaxis = $title;
    }

    public function setTitle($title)
    {
        $this->_title = $title;
    }

    public function setSize($width, $height)
    {
        $this->_width = $width;
        $this->_height = $height;
    }

    public function setLegendFrontdoorLabel($frontdoor)
    {
        $this->_frontdoorLabel = $frontdoor;
    }

    public function setLegendFilesLabel($files)
    {
        $this->_filesLabel = $files;
    }

    public function drawGraph()
    {
        // generate graphic
        $graph = new Graph($this->_width, $this->_height, "auto");
        $graph->SetScale("textlin");

        // add shadow
        $graph->SetShadow();

        // change border
        $graph->img->SetMargin(40, 30, 20, 40);
        $graph->legend->Pos(0.05, 0.05, "right", "top");
        // generate bars
        $bplot = new BarPlot(array_values($this->_dataPdf));
        $bplot->SetLegend($this->_filesLabel);
        $bplotFrontdoor = new BarPlot(array_values($this->_dataFrontdoor));
        $bplotFrontdoor->SetLegend($this->_frontdoorLabel);
        $gbplot  = new GroupBarPlot([$bplot ,$bplotFrontdoor]);
        $graph->Add($gbplot);

        // format bars
        $bplot->SetFillColor('orange');
        $bplot->SetShadow();
        $bplot->SetFillGradient("orange", "yellow", GRAD_HOR);
        $bplot->value->Show();
        $bplot->value->SetFormat('%d');
        $bplot->value->SetFont(FF_FONT1, FS_BOLD);

        //$bplot->value->SetAngle(45);
        $bplot->value->SetColor("darkblue", "darkred");

        $bplotFrontdoor->SetFillColor('blue');
        $bplotFrontdoor->SetShadow();
        $bplotFrontdoor->SetFillGradient("blue", "lightblue", GRAD_HOR);
        $bplotFrontdoor->value->Show();
        $bplotFrontdoor->value->SetFormat('%d');
        $bplotFrontdoor->value->SetFont(FF_FONT1, FS_BOLD);

        //$bplot2->value->SetAngle(45);
        $bplotFrontdoor->value->SetColor("darkgreen", "darkred");

        // format graphic
        $graph->title->Set($this->_title);
        $graph->xaxis->title->Set($this->_xaxis);
        $graph->yaxis->title->Set($this->_yaxis);
        $graph->xaxis->SetTickLabels(array_keys($this->_dataPdf));

        $graph->title->SetFont(FF_FONT1, FS_BOLD);
        $graph->yaxis->title->SetFont(FF_FONT1, FS_BOLD);
        $graph->xaxis->title->SetFont(FF_FONT1, FS_BOLD);

        $graph->yaxis->scale->SetGrace(35);

        // show graphic
        $graph->Stroke();
    }
}
