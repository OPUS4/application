<?php
/**
 * Created by IntelliJ IDEA.
 * User: michael
 * Date: 3/12/14
 * Time: 12:09 PM
 * To change this template use File | Settings | File Templates.
 */

class Admin_Model_Statistics {

    private $documents = null;

    public function __construct($controller = null) {

        $this->documents = new Opus_Db_Documents();
    }

    public function getSelectedYear() {

        return $this->selectedYear;
    }

    private function fillResultArray($select, $name) {
        $statistics = array();
        $result = $select->fetchAll();
        foreach($result as $row) {
            $statistics[$row[$name]] = $row['c'];
        }
        return $statistics;
    }

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

    public function getInstituteStatistics($selectedYear) {

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
            return $instStat;
        }
        return null;
    }

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

    public function getNumDocsUntil($thresholdYear) {
        $result = 0;
        foreach ($this->getYears() as $year) {
            if ($year <= $thresholdYear) {
                $result += array_sum($this->getMonthStatistics($year));
            }
        }
        return $result;
    }

}