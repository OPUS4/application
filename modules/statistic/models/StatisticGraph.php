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

class Statistic_Model_StatisticGraph
{
    /** @var array|null */
    protected $dataPdf;

    /** @var array|null */
    protected $dataFrontdoor;

    /** @var string */
    protected $xaxis = 'x axis';

    /** @var string */
    protected $yaxis = 'y axis';

    /** @var string */
    protected $frontdoorLabel = 'frontdoor';

    /** @var string */
    protected $filesLabel = 'files';

    /** @var string  */
    protected $title;

    /** @var int */
    protected $width = 330;

    /** @var int */
    protected $height = 200;

    /**
     * @param string     $title
     * @param array|null $dataPdf
     * @param array|null $dataFrontdoor
     */
    public function __construct($title = 'Statistic Graph', $dataPdf = null, $dataFrontdoor = null)
    {
        $this->title         = $title;
        $this->dataPdf       = $dataPdf;
        $this->dataFrontdoor = $dataFrontdoor;
    }

    /**
     * @param string $title
     */
    public function setXAxisTitle($title)
    {
        $this->xaxis = $title;
    }

    /**
     * @param string $title
     */
    public function setYAxisTitle($title)
    {
        $this->yaxis = $title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @param int $width
     * @param int $height
     */
    public function setSize($width, $height)
    {
        $this->width  = $width;
        $this->height = $height;
    }

    /**
     * @param string $frontdoor
     */
    public function setLegendFrontdoorLabel($frontdoor)
    {
        $this->frontdoorLabel = $frontdoor;
    }

    /**
     * @param string $files
     */
    public function setLegendFilesLabel($files)
    {
        $this->filesLabel = $files;
    }

    public function drawGraph()
    {
        // generate graphic
        $graph = new Graph($this->width, $this->height, "auto");
        $graph->SetScale("textlin");

        // add shadow
        $graph->SetShadow();

        // change border
        $graph->img->SetMargin(40, 30, 20, 40);
        $graph->legend->Pos(0.05, 0.05, "right", "top");
        // generate bars
        $bplot = new BarPlot(array_values($this->dataPdf));
        $bplot->SetLegend($this->filesLabel);
        $bplotFrontdoor = new BarPlot(array_values($this->dataFrontdoor));
        $bplotFrontdoor->SetLegend($this->frontdoorLabel);
        $gbplot = new GroupBarPlot([$bplot, $bplotFrontdoor]);
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
        $graph->title->Set($this->title);
        $graph->xaxis->title->Set($this->xaxis);
        $graph->yaxis->title->Set($this->yaxis);
        $graph->xaxis->SetTickLabels(array_keys($this->dataPdf));

        $graph->title->SetFont(FF_FONT1, FS_BOLD);
        $graph->yaxis->title->SetFont(FF_FONT1, FS_BOLD);
        $graph->xaxis->title->SetFont(FF_FONT1, FS_BOLD);

        $graph->yaxis->scale->SetGrace(35);

        // show graphic
        $graph->Stroke();
    }
}
