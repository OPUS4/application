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

use Opus\Common\CollectionRole;
use Opus\Common\Repository;
use Opus\Db\Documents;

class Admin_Model_Statistics
{
    /** @var Documents */
    private $documents;

    public function __construct()
    {
        $this->documents = new Documents();
    }

    /**
     * Helper-function (builds up the result array for the statistic-functions).
     *
     * @param Zend_Db_Statement_Interface $select
     * @param string                      $name
     * @return array
     */
    private function fillResultArray($select, $name)
    {
        $statistics = [];
        $result     = $select->fetchAll();
        foreach ($result as $row) {
            if ($row[$name] !== '') {
                if ($name === 'mon' || ($name !== 'mon' && $row['c'])) {
                    // only in month stats rows with zero documents should be depicted.
                    $statistics[$row[$name]] = $row['c'];
                }
            }
        }
        return $statistics;
    }

    /**
     * Builds month statistics (returns sum of published documents sorted by month).
     *
     * @param string|int $selectedYear TODO should be just int
     * @return array
     */
    public function getMonthStatistics($selectedYear)
    {
        // TODO: use tokens to reduce redundancy of inserting year twice
        $select = $this->documents->getAdapter()->query(
            "SELECT months.m as mon, count(d.id) as c
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
            [$selectedYear, $selectedYear]
        );

        $monthStat = $this->fillResultArray($select, 'mon');

        for ($i = 1; $i < 13; $i++) {
            if (isset($monthStat[$i]) === false) {
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
     *
     * @param string|int $selectedYear TODO should not be string, just int
     * @return array
     */
    public function getTypeStatistics($selectedYear)
    {
        // get document type overview from database
        $select = $this->documents->getAdapter()->query(
            "SELECT t.type as ty, count(d.id) as c
          FROM (SELECT DISTINCT type FROM documents) t
          LEFT OUTER JOIN
          (SELECT id, type FROM documents WHERE YEAR(server_date_published) = ? AND server_state = 'published') d
          ON t.type = d.type
          GROUP BY t.type",
            $selectedYear
        );
        return $this->fillResultArray($select, 'ty');
    }

    /**
     * Builds institute statistics.
     *
     * Returns sum of published documents sorted by institutes.
     *
     * @param string|int $selectedYear TODO should not be string, just int
     * @return array
     */
    public function getInstituteStatistics($selectedYear)
    {
        $role     = CollectionRole::fetchByName('institutes');
        $instStat = [];
        if (isset($role)) {
            $query = "SELECT c.name name, COUNT(DISTINCT(d.id)) entries
                 FROM documents d
                 LEFT JOIN link_documents_collections ldc ON d.id=ldc.document_id
                 LEFT JOIN collections c ON ldc.collection_id=c.id
                 WHERE c.role_id=? AND YEAR(server_date_published)=? AND server_state='published'
                group by name";
            $db    = Zend_Db_Table::getDefaultAdapter();
            $res   = $db->query($query, [$role->getId(), $selectedYear])->fetchAll();

            foreach ($res as $result) {
                $instStat[$result['name']] = $result['entries'];
            }
        }
        return $instStat;
    }

    /**
     * Returns all years in which documents were published.
     * TODO show that there are published documents without publication date?
     *
     * @return array
     */
    public function getYears()
    {
        $documents = new Documents();
        $select    = $documents->select()->from('documents', ['year' => 'YEAR(server_date_published)'])
            ->where('server_state = ?', 'published')
            ->where('server_date_published IS NOT NULL')
            ->distinct()
            ->order('year');
        $result    = $documents->fetchAll($select);
        foreach ($result as $row) {
            $years[$row->year] = $row->year;
        }
        return $years;
    }

    /**
     * Returns sum of all documents published before the $thresholdYear.
     *
     * @param int $thresholdYear
     * @return int
     */
    public function getNumDocsUntil($thresholdYear)
    {
        $finder = Repository::getInstance()->getDocumentFinder();
        $finder->setServerState('published');
        $finder->setServerDatePublishedBefore($thresholdYear + 1);
        return $finder->getCount();
    }
}
