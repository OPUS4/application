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

class Application_View_Helper_BreadcrumbsTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['view', 'mainMenu', 'navigation', 'translation'];

    /** @var Zend_Navigation_Page */
    private $page;

    /** @var Application_View_Helper_Breadcrumbs */
    private $breadcrumbs;

    /** @var Zend_View */
    private $view;

    public function setUp(): void
    {
        parent::setUp();

        $this->view = $this->getView();

        $this->breadcrumbs = $this->view->breadcrumbs();

        $navigation = $this->view->navigation()->getContainer();
        $this->page = $navigation->findOneByLabel('admin_title_documents');
    }

    public function testHelpLinkPresent()
    {
        $this->page->helpUrl = 'http://opus4.kobv.de';

        $this->dispatch('/admin/documents');
        $this->assertResponseCode(200);
        $this->assertQuery('//a[@class="admin-help"]');
    }

    public function testHelpLinkNotPresent()
    {
        $this->page->helpUrl = null;

        $this->dispatch('/admin/documents');
        $this->assertResponseCode(200);
        $this->assertNotQuery('//a[@class="admin-help"]');
    }

    public function testSetReplacement()
    {
        $this->breadcrumbs->setReplacement('Breadcrumbs Text');

        $this->assertEquals(
            '<div class="breadcrumbsContainer"><div class="wrapper">Breadcrumbs Text</div></div>',
            $this->breadcrumbs->renderStraight()
        );
    }

    public function testRenderStraight()
    {
        $this->dispatch('/admin');
        $this->assertEquals(
            '<div class="breadcrumbsContainer"><div class="wrapper">Administration</div></div>',
            $this->breadcrumbs->renderStraight()
        );
    }

    public function testSetSuffix()
    {
        $this->dispatch('/admin');
        $this->breadcrumbs->setSuffix('(Extra Stuff)');
        $this->assertEquals(
            '<div class="breadcrumbsContainer">'
            . '<div class="wrapper">Administration &gt; (Extra Stuff)</div></div>',
            $this->breadcrumbs->renderStraight()
        );
    }

    public function testSetSuffixWithoutSeparator()
    {
        $this->dispatch('/admin');
        $this->breadcrumbs->setSuffix(' (Extra Stuff)');
        $this->breadcrumbs->setSuffixSeparatorDisabled(true);
        $this->assertEquals(
            '<div class="breadcrumbsContainer">'
            . '<div class="wrapper">Administration (Extra Stuff)</div></div>',
            $this->breadcrumbs->renderStraight()
        );
    }
}
