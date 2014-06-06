<?php
/*
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
 * @category    Application Unit Test
 * @author      Michael Lang <lang@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Admin_Model_Statistics {

    private $documents = null;

    public function __construct() {
        $this->documents = new Opus_Db_Documents();
    }

    /**
     * Helper-function (builds up the result array for the statistic-functions).
     */
    private function fillResultArray($select, $name) {
        $statistics = array();
        $result = $select->fetchAll();
        foreach($result as $row) {
            $statistics[$row[$name]] = $row['c'];
        }
        return $statistics;
    }

    /**
     * Builds month statistics (returns sum of published documents sorted by month).
     */
    public function getMonthStatistics($selectedYear) {
        // TODO: use tokens to reduce redundancy of inserting year twice
        $select = $this->documents->getAdapter()->query("SELECT months.m as mon, count(d.id) as c
            FROM
                (SELECT id, MONTH(`server_date_published`) as m
                    FROM `documents`
                    WHERE YEAR(`server_date_published`) = ? AND server_state = 'published' )
                d,
                (SELECT DISTINCT MONTH(`server_date_published`) as m
                    FROM `documents`
                    WHERE YEAR(`server_date_published`) = ? AND server_state = 'published' )
                months
            WHERE months.m = d.m
            GROUP BY months.m",
            array($selectedYear, $selectedYear));

        $monthStat = $this->fillResultArray($select, 'mon');

        for($i = 1; $i<13; $i++) {
            if (isset($monthStat[$i]) === FALSE) {
                $monthStat[$i] = 0;
            }
        }
        ksort($monthStat);
        return $monthStat;
    }

    /**
     * Builds type statistics.
     *
     * Returns sum of published documents sorted by document types.
     */
    public function getTypeStatistics($selectedYear) {
        // get document type overview from database
        $select = $this->documents->getAdapter()->query("SELECT t.type as ty, count(d.id) as c
          FROM (SELECT DISTINCT type FROM documents) t
          LEFT OUTER JOIN
          (SELECT id, type FROM documents WHERE YEAR(server_date_published) = ? AND server_state = 'published') d
          ON t.type = d.type
          GROUP BY t.type", $selectedYear);
        return $this->fillResultArray($select, 'ty');
    }

    /**
     * Builds institute statistics.
     *
     * Returns sum of published documents sorted by institutes.
     */
    public function getInstituteStatistics($selectedYear) {
        $instStat = null;
        // institution statistics
        //$institutes = new Opus_OrganisationalUnits;
        $role = Opus_CollectionRole::fetchByName('institutes');
        if (isset($role)) {
            $colls = Opus_Collection::fetchCollectionsByRoleId($role->getId());
            //$institutes = Opus_CollectionRole::fetchByName('institutes');
            $instStat = array();
            $db = Zend_Registry::get('db_adapter');
            //foreach ($institutes->getSubCollection() as $institut) {
            foreach ($colls as $institut) {
                //$institut = $c->getName();
                /*
                $query = "SELECT COUNT(d.id) AS entries FROM link_documents_collections_1 AS l JOIN documents AS d ON d.id =
                    l.documents_id WHERE l.collections_id IN (SELECT collections_id FROM collections_structure_1 WHERE
                    `left` >= (SELECT `left` FROM collections_structure_1 WHERE collections_id = ?) AND `right` <=
                    (SELECT `right` FROM collections_structure_1 WHERE collections_id = ?)AND
                    YEAR(d.server_date_published) = ?)";
                 *
                 */
                $query = "SELECT COUNT(d.id) AS entries FROM link_documents_collections AS l JOIN documents AS d
                    ON d.id = l.document_id WHERE l.collection_id IN (SELECT id FROM collections WHERE `left_id` >=
                    (SELECT `left_id` FROM collections WHERE id = ?) AND `right_id` <=
                    (SELECT `right_id` FROM collections WHERE id = ?)AND
                    YEAR(d.server_date_published) = ? and server_state = 'published' )";
                $res = $db->query($query, array($institut->getId(), $institut->getId(), $selectedYear))->fetchAll();
                $instStat[$institut->getDisplayName()] = $res[0]['entries'];
            }
        }
        return $instStat;
    }

    /**
     * Returns all years in which documents were published.
     */
    public function getYears() {
        $documents = new Opus_Db_Documents();
        $select = $documents->select()->from('documents', array('year' => 'YEAR(server_date_published)'))
            ->distinct()
            ->order('year');
        $result = $documents->fetchAll($select);
        foreach($result as $row) {
            $years[$row->year] = $row->year;
        }
        return $years;
    }

    /**
     * Returns sum of all documents published before the $thresholdYear.
     */
    public function getNumDocsUntil($thresholdYear) {
        $finder = new Opus_DocumentFinder();
        $finder->setServerState('published');
        $finder->setServerDatePublishedBefore($thresholdYear+1);
        return $finder->count();
    }

}