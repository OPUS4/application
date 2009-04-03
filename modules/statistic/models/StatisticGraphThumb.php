<?php
include ("jpgraph/jpgraph.php");
include ("jpgraph/jpgraph_bar.php");

//TODO change comment
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
 * @package     Module_statistic
 * @author      Birgit Dressler (b.dressler@sulb.uni-saarland.de)
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class StatisticGraphThumb {

    protected $data = null;
    protected $width = 35;
    protected $height = 27;

    public function __construct($data) {
        $this->data = $data;
    }

        public function setSize($width, $height) {
        $this->width = $width;
        $this->height = $height;
    }

    public function drawGraph() {
        // generate graphic
        $graph = new Graph($this->width, $this->height, "auto");
        $graph->SetScale("textlin");

        $graph->img->SetMargin(0,0,0,1);

        // generate bars
        $bplot = new BarPlot($this->data);
        $graph->Add($bplot);


        // format bars
        $bplot->SetFillColor('gray');

        //TODO relative path to layout directory!?
        $graph->SetBackgroundImage('/home/developer/workspace/bsz_opus_application/public/layouts/default/img/statistics_bg.jpg', BGIMG_FILLPLOT, 'jpg');
        $bplot->SetFillGradient("gray","darkgray",GRAD_HOR);
        $graph->yaxis->HideTicks(true,true);
        $graph->xaxis->HideLabels(true);

        // show graphic
        $graph->Stroke();

    }
}