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

class Admin_StatisticController extends Zend_Controller_Action {

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
        $documents = new Opus_Db_Documents();
        $postData = $this->_request->getPost();
        // get month overview from database

        for ($i = 1; $i<13; $i++) {
            $select = $documents->select()->from('documents', array('c' => 'count(*)'))
                ->where('YEAR(server_date_published) = ?', $postData['selectedYear'])
                ->where('MONTH(server_date_published) = ?', $i);
            $monthStat[$i] = $documents->fetchRow($select)->c;
        }

        $this->view->title = $this->view->translate('Statistic_Controller');
        $this->view->monthStat = $monthStat;


        // get document type overview from database
        $select = $documents->getAdapter()->query("SELECT t.type as ty, count(d.id) as c
          FROM (SELECT DISTINCT type FROM documents) t
          LEFT OUTER JOIN
          (SELECT id, type FROM documents WHERE YEAR(server_date_published) = ?) d
          ON t.type = d.type
          GROUP BY t.type", $postData['selectedYear']);
        $result = $select->fetchAll();
        foreach($result as $row) {
            $typeStat[$row['ty']] = $row['c'];
        }
        $this->view->typeStat = $typeStat;


        // TODO: add institution statistics
    }

}