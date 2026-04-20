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
 * @copyright   Copyright (c) 2026, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\UserRole;

/**
 * @covers Application_View_Helper_RssLink
 */
class Application_View_Helper_RssLinkTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['view', 'database'];

    /** @var Application_View_Helper_RssLink */
    private $helper;

    public function setUp(): void
    {
        parent::setUp();

        $this->helper = new Application_View_Helper_RssLink();
        $this->helper->setView($this->getView());
    }

    public function testRssLinkDefault()
    {
        $output = $this->helper->rssLink();

        $expected = <<<EOT
<a href="/rss" class="rss" type="application/rss+xml">
  <img src="/layouts/opus4/img/feed_small.png" width="12" height="12" alt="rss_icon" title="rss_title" />
</a>
EOT;

        $this->assertEquals($expected, $output);
    }

    public function testRssLinkWithArray()
    {
        $output = $this->helper->rssLink(['searchtype' => 'collection', 'id' => 147]);

        $expected = <<<EOT
<a href="/rss/index/index/searchtype/collection/id/147" class="rss" type="application/rss+xml">
  <img src="/layouts/opus4/img/feed_small.png" width="12" height="12" alt="rss_icon" title="rss_title" />
</a>
EOT;

        $this->assertEquals($expected, $output);
    }

    public function testRssLinkWithString()
    {
        $output = $this->helper->rssLink('/rss/index/index/searchtype/latest');

        $expected = <<<EOT
<a href="/rss/index/index/searchtype/latest" class="rss" type="application/rss+xml">
  <img src="/layouts/opus4/img/feed_small.png" width="12" height="12" alt="rss_icon" title="rss_title" />
</a>
EOT;

        $this->assertEquals($expected, $output);
    }

    public function testRssLinkDisabled()
    {
        $this->adjustConfiguration([
            'rss' => ['showLinks' => false],
        ]);

        $output = $this->helper->rssLink();

        $this->assertEquals('', $output);
    }

    public function testRssLinkNoAccess()
    {
        $this->enableSecurity();

        $guest = UserRole::fetchByName('guest');
        $guest->removeAccessModule('rss');
        $guest->store();

        $output = $this->helper->rssLink();

        $guest->appendAccessModule('rss');
        $guest->store();

        $this->assertEquals('', $output);
    }
}
