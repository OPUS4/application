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
 * @copyright   Copyright (c) 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Unit tests for view helper for rendering export links.
 */
class Application_View_Helper_ExportLinksTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'all';

    public function testToStringForSearch()
    {
        $exportLink = new Application_View_Helper_ExportLinks();

        $this->assertEquals(
            '<ul>'
            . '<li><a href="/export/index/bibtex" title="Export BibTeX" class="export bibtex">BibTeX</a></li>'
            . '<li><a href="/export/index/csv" title="Export CSV" class="export csv">CSV</a></li>'
            . '<li><a href="/export/index/ris" title="Export RIS" class="export ris">RIS</a></li>'
            . '<li><a href="/export/index/index/export/xml/stylesheet/example" title="Export XML" class="export xml">XML</a></li>'
            . '</ul>',
            $exportLink->toString(null, 'search')
        );
    }

    public function testToStringForFrontdoor()
    {
        // Restricted format are only setup during request processing (OPUS4/application#516)
        $this->dispatch('/home');

        $exportLink = new Application_View_Helper_ExportLinks();

        $this->assertEquals(
            '<ul>'
            . '<li><a href="/citationExport/index/download/output/bibtex" title="Export BibTeX" class="export bibtex">BibTeX</a></li>'
            . '<li><a href="/export/index/datacite" title="Export DataCite-XML" class="export datacite">DataCite</a></li>'
            . '<li><a href="/export/index/marc21/searchtype/id" title="Export MARC21-XML" class="export marc21-xml">MARC21-XML</a></li>'
            . '<li><a href="/citationExport/index/download/output/ris" title="Export RIS" class="export ris">RIS</a></li>'
            . '<li><a href="/export/index/index/export/xml/searchtype/id/stylesheet/example" title="Export XML" class="export xml">XML</a></li>'
            . '</ul>',
            $exportLink->toString(null, 'frontdoor')
        );
    }

    public function testRenderLink()
    {
        $page = new Zend_Navigation_Page_Mvc([
            'name'        => 'bibtex',
            'description' => 'Export BibTeX',
            'module'      => 'citationExport',
            'controller'  => 'index',
            'action'      => 'download',
            'params'      => [
                'output' => 'bibtex',
            ],
            'frontdoor'   => true,
        ]);

        $page->setParam('docId', 150);

        $exportLink = new Application_View_Helper_ExportLinks();

        $this->assertEquals(
            '<a href="/citationExport/index/download/output/bibtex/docId/150" title="Export BibTeX" class="export bibtex">bibtex</a>',
            $exportLink->renderLink($page)
        );
    }
}
