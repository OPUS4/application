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
 * @category    Application
 * @package     Module_Admin
 * @author      Tobias Leidinger (tobias.leidinger@gmail.com
 * @author      Felix Ostrowski <ostrowski@hbz-nrw.de>
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Main entry point for this module.
 *
 * @category    Application
 * @package     Module_Admin
 */

class Admin_StatisticController extends Controller_Action {

    /**
     * TODO
     *
     * @return void
     */
    public function indexAction() {
        $this->view->title = $this->view->translate('Statistic_Controller');

        $documents = new Opus_Db_Documents();
        $select = $documents->select()->from('documents', array('year' => 'YEAR(server_date_published)'))
        ->distinct()
        ->order('year');
        $result = $documents->fetchAll($select);
        foreach($result as $row) {
            $years[$row->year] = $row->year;
        }
        /*$years = array_values($result->toArray());
         print_r($result->toArray());
         print("<br>");
         print_r(array_values($result->toArray()));*/
        //print_r($years);

        //$selectYear = new Zend_Form_Element_Text('selectedYear');
        $highest = max($years);

        $selectYear = new Zend_Form_Element_Select('selectedYear', array("multiOptions" => $years, "value" => $highest));

        //$selectYear = new Zend_Form_Element_Select();

        $selectYear->setRequired(true)
        ->setLabel($this->view->translate('Select_Year_Label'));

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel($this->view->translate('Submit_Button_Label'));

        $form = new Zend_Form();
        $action_url = $this->view->url(array("controller" => "statistic", "action" => "show"));
        $form->setAction($action_url);
        $form->addElements(array($selectYear, $submit));

        $this->view->form = $form;
    }

    public function showAction() {
        $this->view->languageSelectorDisabled = true;

        $documents = new Opus_Db_Documents();
        $postData = $this->_request->getPost();
        // get month overview from database


        /* iteration with 12 db select queries, replaced by join with subqueries
         *
         * for ($i = 1; $i<13; $i++) {
         *
         * $select = $documents->select()->from('documents', array('c' => 'count(*)'))
         * ->where('YEAR(server_date_published) = ?', $postData['selectedYear'])
         * ->where('MONTH(server_date_published) = ?', $i);
         * $monthStat[$i] = $documents->fetchRow($select)->c;
         * }
         */


        // TODO: use tokens to reduce redundancy of inserting year twice
        $select = $documents->getAdapter()->query("SELECT months.m as mon, count(d.id) as c
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
        array($postData['selectedYear'], $postData['selectedYear']));

        $result = $select->fetchAll();
        foreach($result as $row) {
            $monthStat[$row['mon']] = $row['c'];
        }

        for($i = 1; $i<13; $i++) {
            if (isset($monthStat[$i]) === FALSE) {
                $monthStat[$i] = 0;
            }
        }
        ksort($monthStat);

        $this->view->totalNumber = array_sum($monthStat);
        $this->view->title = $this->view->translate('Statistic_Controller') . ' (' . $postData['selectedYear'] . ')';
        $this->view->monthStat = $monthStat;


        // get document type overview from database
        $select = $documents->getAdapter()->query("SELECT t.type as ty, count(d.id) as c
          FROM (SELECT DISTINCT type FROM documents) t
          LEFT OUTER JOIN
          (SELECT id, type FROM documents WHERE YEAR(server_date_published) = ? AND server_state = 'published') d
          ON t.type = d.type
          GROUP BY t.type", $postData['selectedYear']);
        $result = $select->fetchAll();
        foreach($result as $row) {
            $typeStat[$row['ty']] = $row['c'];
        }

        $this->view->typeStat = $typeStat;


        // institution statistics
        //$institutes = new Opus_OrganisationalUnits;
        $role = Opus_CollectionRole::fetchByName('institutes');
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
            $query = "SELECT COUNT(d.id) AS entries FROM link_documents_collections AS l JOIN documents AS d ON d.id =
                l.document_id WHERE l.collection_id IN (SELECT id FROM collections WHERE `left_id` >=
                (SELECT `left_id` FROM collections WHERE id = ?) AND `right_id` <=
                (SELECT `right_id` FROM collections WHERE id = ?)AND
                YEAR(d.server_date_published) = ? and server_state = 'published' )";
            $res = $db->query($query, array($institut->getId(), $institut->getId(), $postData['selectedYear']))->fetchAll();
            $instStat[$institut->getDisplayName()] = $res[0]['entries'];
        }
        $this->view->instStat = $instStat;

    }

}
