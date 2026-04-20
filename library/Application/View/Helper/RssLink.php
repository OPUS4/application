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

use Opus\Common\ConfigTrait;
use Opus\Common\Security\Realm;

class Application_View_Helper_RssLink extends Application_View_Helper_Abstract
{
    use ConfigTrait;

    public function rssLink(string|array|null $options = null): string
    {
        if (! $this->isShowRssLinks() || ! $this->isRssAllowed()) {
            return '';
        }

        $view = $this->view;

        if (is_string($options)) {
            $rssUrl = $options;
        } else {
            $basicOptions = [
                'module'     => 'rss',
                'controller' => 'index',
                'action'     => 'index',
            ];

            if (is_array($options)) {
                $rssUrl = $view->url(array_merge($basicOptions, $options), null, true);
            } else {
                $rssUrl = $view->url($basicOptions, null, true);
            }
        }

        $imagePath = $view->layoutPath() . '/img/feed_small.png';
        $alt       = $view->translate('rss_icon');
        $title     = $view->translate('rss_title');

        $output  = "<a href=\"{$rssUrl}\" class=\"rss\" type=\"application/rss+xml\">" . PHP_EOL;
        $output .= "  <img src=\"{$imagePath}\" width=\"12\" height=\"12\" alt=\"{$alt}\" title=\"{$title}\" />" . PHP_EOL;
        $output .= "</a>";

        return $output;
    }

    protected function isShowRssLinks(): bool
    {
        $config = $this->getConfig();
        return isset($config->rss->showLinks) && filter_var($config->rss->showLinks, FILTER_VALIDATE_BOOLEAN);
    }

    protected function isRssAllowed(): bool
    {
        $realm = Realm::getInstance();
        return $realm->checkModule('rss');
    }
}
