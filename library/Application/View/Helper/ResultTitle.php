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
 * @copyright   Copyright (c) 2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\DocumentInterface;

/**
 * Helper for printing the title of a OPUS document in search results.
 *
 * TODO use $result->getAsset('title') ??? Avoid using Document (?)
 */
class Application_View_Helper_ResultTitle extends Application_View_Helper_Document_HelperAbstract
{
    /**
     * Prints escaped main title of document.
     *
     * @param DocumentInterface|null $document
     * @return null|string
     */
    public function resultTitle($document = null)
    {
        if ($document === null) {
            $document = $this->getDocument();
        }

        $frontdoorUrl = $this->getFrontdoorUrl($document);

        $title = $this->view->documentTitle($document);

        $output = "<a href=\"$frontdoorUrl\"";

        if ($title === null) {
            $title   = $this->view->translate('results_missingtitle');
            $output .= ' class="missing_title"';
        }

        $output .= ">$title</a>";

        return $output;
    }

    /**
     * TODO get rid of this here - there are already DocumentUrl and FrontdoorUrl
     *
     * @param DocumentInterface|null $document
     * @return string
     */
    public function getFrontdoorUrl($document)
    {
        if (isset($this->view->start)) {
            $start = $this->view->start + $this->view->partialCounter - 1;
        } else {
            $start = null;
        }

        // TODO hack - can this be avoided?
        $searchType = $this->view->searchType;
        if ($searchType === null) {
            $searchType = Zend_Controller_Front::getInstance()->getRequest()->getParam('searchtype');
        }

        return $this->view->url([
            'module'     => 'frontdoor',
            'controller' => 'index',
            'action'     => 'index',
            'docId'      => $document->getId(),
            'start'      => $start,
            'rows'       => $this->view->rows,
            'searchtype' => $searchType,
        ]);
    }
}
