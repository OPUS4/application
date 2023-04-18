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
 * TODO move to search package
 */
class Application_Util_Searchtypes
{
    public const SIMPLE_SEARCH     = 'simple';
    public const ADVANCED_SEARCH   = 'advanced';
    public const AUTHOR_SEARCH     = 'authorsearch';
    public const COLLECTION_SEARCH = 'collection';
    public const LATEST_SEARCH     = 'latest';
    public const ALL_SEARCH        = 'all';
    public const SERIES_SEARCH     = 'series';
    public const ID_SEARCH         = 'id';

    /**
     * @param string $searchtype
     * @return bool
     */
    public static function isSupported($searchtype)
    {
        $supportedTypes = [
            self::SIMPLE_SEARCH,
            self::ADVANCED_SEARCH,
            self::AUTHOR_SEARCH,
            self::COLLECTION_SEARCH,
            self::LATEST_SEARCH,
            self::ALL_SEARCH,
            self::SERIES_SEARCH,
            self::ID_SEARCH,
        ];
        return in_array($searchtype, $supportedTypes);
    }

    /**
     * @param string $searchType
     * @return Solrsearch_Model_Search_Abstract
     *
     * TODO eliminate switch and use configuration array instead
     */
    public static function getSearchPlugin($searchType)
    {
        switch ($searchType) {
            case self::SERIES_SEARCH:
                $pluginClass = Solrsearch_Model_Search_Series::class;
                break;
            case self::COLLECTION_SEARCH:
                $pluginClass = Solrsearch_Model_Search_Collection::class;
                break;
            case self::LATEST_SEARCH:
                $pluginClass = Solrsearch_Model_Search_Latest::class;
                break;
            case self::ADVANCED_SEARCH:
            case self::AUTHOR_SEARCH:
                $pluginClass = Solrsearch_Model_Search_Advanced::class;
                break;
            case self::ALL_SEARCH:
                $pluginClass = Solrsearch_Model_Search_All::class;
                break;
            default:
                $pluginClass = Solrsearch_Model_Search_Basic::class;
        }

        $plugin = new $pluginClass();
        $plugin->setSearchType($searchType);

        return $plugin;
    }
}
