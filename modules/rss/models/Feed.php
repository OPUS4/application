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

/**
 * TODO context spezifische Titel für RSS feed (latest, collections, ...)
 */
class Rss_Model_Feed extends Application_Model_Abstract
{
    /** @var Zend_View_Interface */
    private $view;

    /**
     * @param Zend_View_Interface $view
     */
    public function __construct($view)
    {
        $this->view = $view;
    }

    /**
     * Returns title of RSS feed.
     *
     * The title can be defined in config.ini using the key 'rss.default.feedTitle'.
     * If no title is defined the 'name' (config.ini) of the repository is used.
     *
     * In 'rss.default.feedTitle' three placeholders can be used:
     *
     * %1$s - Name of repository ('name' key in config.ini)
     * %2$s - Host, e.g. opus4web.zib.de
     * %3$s - Base URL for repository without leading '/', e.g. 'opus4-demo'
     * %4$s - Full URL for repository, e.g. 'http://opus4web.zib.de/opus4-demo'
     *
     * @return string
     */
    public function getTitle()
    {
        $config = $this->getConfig();

        $name = Application_Configuration::getInstance()->getName();

        if (isset($config->rss->default->feedTitle)) {
            $feedTitle = $config->rss->default->feedTitle;
        } else {
            $feedTitle = '%4$s';
        }

        $feedTitle = sprintf(
            $feedTitle,
            $name,
            $this->view->getHelper('ServerUrl')->getHost(),
            substr($this->view->baseUrl(), 1),
            $this->view->fullUrl()
        );

        return $feedTitle;
    }

    /**
     * Returns description of RSS feed.
     *
     * The description can be defined in config.ini using key 'rss.default.feedDescription'.
     * The default is 'OPUS documents'.
     *
     * @return string
     */
    public function getDescription()
    {
        $config = $this->getConfig();

        if (isset($config->rss->default->feedDescription)) {
            $feedDescription = $config->rss->default->feedDescription;
        } else {
            $feedDescription = 'OPUS documents';
        }

        return $feedDescription;
    }
}
