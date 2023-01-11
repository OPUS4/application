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

use Opus\Common\CollectionRole;
use Opus\Common\Document;

class Application_View_Helper_FulltextLogoTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['view', 'translation'];

    /** @var Application_View_Helper_FulltextLogo */
    private $helper;

    public function setUp(): void
    {
        parent::setUp();

        $this->helper = new Application_View_Helper_FulltextLogo();
        $this->helper->setView($this->getView());

        $this->useEnglish();
    }

    public function testFulltextLogo()
    {
        $doc = $this->createTestDocument();
        $doc = Document::get($doc->store());

        $this->assertEquals('<div class="fulltext-logo"></div>', $this->helper->fulltextLogo($doc));
    }

    /**
     * TODO normally 'fulltext' and 'openaccess' should always occur together
     */
    public function testFulltextLogoOpenAccess()
    {
        $doc = $this->createTestDocument();

        $openAccessRole = CollectionRole::fetchByName('open_access');
        $openAccess     = $openAccessRole->getCollectionByOaiSubset('open_access');
        $doc->addCollection($openAccess);

        $doc = Document::get($doc->store());

        $this->assertEquals('<div class="fulltext-logo openaccess" title="Open Access fulltext available"></div>', $this->helper->fulltextLogo($doc));
    }

    public function testFulltextLogoFulltext()
    {
        $doc = $this->createTestDocument();

        $file = $this->createOpusTestFile('article.pdf');
        $doc->addFile($file);

        $doc = Document::get($doc->store());

        $this->assertEquals('<div class="fulltext-logo fulltext" title="Fulltext available"></div>', $this->helper->fulltextLogo($doc));
    }

    public function testFulltextLogoOpenAccessFulltext()
    {
        $doc = $this->createTestDocument();

        $openAccessRole = CollectionRole::fetchByName('open_access');
        $openAccess     = $openAccessRole->getCollectionByOaiSubset('open_access');
        $doc->addCollection($openAccess);

        $file = $this->createOpusTestFile('article.pdf');
        $doc->addFile($file);

        $doc = Document::get($doc->store());

        $this->assertEquals('<div class="fulltext-logo fulltext openaccess" title="Open Access fulltext available"></div>', $this->helper->fulltextLogo($doc));
    }
}
