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

use Opus\Search\Result\ResultMatch;

class Application_View_Helper_ResultAuthorsTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database', 'view'];

    /** @var Application_View_Helper_ResultAuthors */
    protected $helper;

    public function setUp(): void
    {
        parent::setUp();

        $this->helper = new Application_View_Helper_ResultAuthors();
        $view         = $this->getView();
        $this->helper->setView($view);
    }

    public function testRendering()
    {
        $document = $this->createTestDocument();
        $docId    = $document->store();

        $result = new ResultMatch($docId);
        $result->setAsset('author', ['Muster']);
        $this->helper->view->result = $result;

        $output = $this->helper->resultAuthors();

        $this->assertEquals('<a href="/index/index/author/Muster">Muster</a>', $output);
    }

    public function testRenderingRemovesComma()
    {
        $document = $this->createTestDocument();
        $docId    = $document->store();

        $result = new ResultMatch($docId);
        $result->setAsset('author', ['Muster , ']);
        $this->helper->view->result = $result;

        $output = $this->helper->resultAuthors();

        $this->assertEquals('<a href="/index/index/author/Muster">Muster</a>', $output);
    }

    public function testRenderingMultipleAuthors()
    {
        $document = $this->createTestDocument();
        $docId    = $document->store();

        $result = new ResultMatch($docId);
        $result->setAsset('author', ['Muster, John', 'Doe', 'Smith, John']);
        $this->helper->view->result = $result;

        $output = $this->helper->resultAuthors();

        $this->assertEquals('<a href="/index/index/author/Muster%2C+John">Muster, John</a>'
             . ' ; <a href="/index/index/author/Doe">Doe</a>'
             . ' ; <a href="/index/index/author/Smith%2C+John">Smith, John</a>', $output);
    }

    public function testUseCustomSeparator()
    {
        $document = $this->createTestDocument();
        $docId    = $document->store();

        $result = new ResultMatch($docId);
        $result->setAsset('author', ['Muster, John', 'Smith, John']);
        $this->helper->view->result = $result;

        $this->helper->setSeparator(' # ');

        $output = $this->helper->resultAuthors();

        $this->assertEquals(
            '<a href="/index/index/author/Muster%2C+John">Muster, John</a>'
            . ' # <a href="/index/index/author/Smith%2C+John">Smith, John</a>',
            $output
        );
    }
}
