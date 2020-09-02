<?php
/*
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
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2011-2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Generating site links suitable for search engine indexing.
 *
 * @category    Application
 * @package     Module_Crawlers
 */
class Crawlers_SitelinksController extends Application_Controller_Action
{

    /**
     * Disable access control for this controller.
     */
    protected function checkAccessModulePermissions()
    {
    }

    /**
     * Lists all years in which documents were published.
     */
    public function indexAction()
    {
        $f = new Opus_DocumentFinder();
        $f->setServerState('published');

        $this->view->years = $f->groupedServerYearPublished();

        sort($this->view->years);

        $this->view->ids = null;
    }

    /**
     * Lists all documents for a year.
     */
    public function listAction()
    {
        $this->indexAction();

        $year = trim($this->_getParam('year'));

        if (preg_match('/^\d{4}$/', $year) > 0) {
            $f = new Opus_DocumentFinder();
            $f->setServerState('published');
            $f->setServerDatePublishedRange($year, $year + 1);
            $this->view->ids = $f->ids();

            if (count($this->view->ids) > 0) {
                $this->view->listYear = $year;
                $this->view->title = $this->view->translate('crawlers_sitelinks_list', $year);
            } else {
                $this->view->ids = null;
                $this->view->title = $this->view->translate('crawlers_sitelinks_index');
            }
        }

        return $this->render('index');
    }
}
