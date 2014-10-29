#!/usr/bin/env php5

<?PHP
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
 * @category    Application
 * @author      Edouard Simon <edouard.simon@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2014, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 *
 */

/*
 * This script sorts the nested set structure of collections according to the
 * field 'sort_order', which is removed as of Opus Version 4.4.3. It must be
 * applied before updating the database from versions prior to 4.4.3 to 4.4.3
 * or higher. Otherwise custom sort orders will be lost.
 */

require_once dirname(__FILE__) . '/../common/bootstrap.php';

$collectionsTable = new Opus_Db_Collections();

// check if sort order is set in any collection
$selectSortedCollections = $collectionsTable->select()->where('sort_order > 0');

$sortedCollections = $collectionsTable->fetchAll($selectSortedCollections);

// nothing to do here
if ($sortedCollections->count() == 0) {
    _log("No sort order found in any collection. Nothing to do here..");
    exit;
}

// sort all collections
$selectRoots = $collectionsTable->select()->where('parent_id IS NULL');
$rootCollections = $collectionsTable->fetchAll($selectRoots)->toArray();

if(!empty($rootCollections)) {

    // do the sorting
    foreach ($rootCollections as $rootCollection) {
        sortNestedSet($collectionsTable, $rootCollection['id']);
    }

    // check if collections are sorted
    foreach ($rootCollections as $rootCollection) {
        if(!checkSortOrder($collectionsTable, $rootCollection['id'])) {
            _log("SORT ORDER NOT FIXED FOR COLLECTION #{$rootCollection['id']} (and maybe more). Exiting..");
            exit(1);
        }
    }
}

// everything's fine if we get here
_log('update collection sorting complete.');

/*
 * used functions below
 */

/**
 * Recursive function sorting Nested Set after sort_order.
 * Starting with root node, every child tree is sorted.
 * @param Opus_Db_Collections $collectionsTable
 * @param int $collectionId ID of root collection
 */
function sortNestedSet(Opus_Db_Collections $collectionsTable, $collectionId) {
    $childrenSelect = $collectionsTable
        ->select()->where("parent_id = ?", $collectionId)
        ->order("sort_order ASC");

    $children = $collectionsTable->fetchAll($childrenSelect)->toArray();

    if (!is_null($children)) {
        $sortedIds = array();

        foreach ($children as $child) {
            $sortedIds[] = $child['id'];
        }

        if (isSortOrderSet($children)) {
            $collectionsTable->applySortOrderOfChildren($collectionId, $sortedIds);
        }

        foreach ($sortedIds as $childId) {
            sortNestedSet($collectionsTable, $childId);
        }
    }
}

/**
 * Recursive function checking the nested set order against the sort_order.
 * Starting with root node, every child tree is checked.
 * @param Opus_Db_Collections $collectionsTable
 * @param int $collectionId ID of root collection
 */

function checkSortOrder(Opus_Db_Collections $collectionsTable, $collectionId) {
    $childrenSelect = $collectionsTable
            ->select()->where("parent_id = ?", $collectionId)
            ->order("sort_order ASC");

    $unsortedChildren = $sortedChildren = $collectionsTable->fetchAll($childrenSelect)->toArray();

    if (!isSortOrderSet($unsortedChildren)) {
        return true;
    }
    
    foreach ($unsortedChildren as $child) {
        $result = checkSortOrder($collectionsTable, $child['id']);
    }

    if ($result) {
        usort($sortedChildren, function($a, $b) {
                    if ($a['left_id'] == $b['left_id']) {
                        return 0;
                    }
                    return ($a['left_id'] < $b['left_id']) ? -1 : 1;
                });

        foreach ($sortedChildren as $pos => $child) {
            if ($unsortedChildren[$pos]['id'] != $child['id'])
                return false;
        }
    }

    return true;
}

/**
 * Helper function returning true if any collection has a value in field sort_order.
 */
function isSortOrderSet($collectionArray) {
    foreach ($collectionArray as $child) {
        if ($child['sort_order'] > 0) {
            return true;
        }
    }
    return false;
}

/**
 * Basic logging to console.
 */
function _log($string) {
    echo "$string" . PHP_EOL;
}
