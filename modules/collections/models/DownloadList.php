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
 * @package     Module_Collection
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Collections_Model_DownloadList {

    /**
     *
     * @return string csv output
     * @throws Collections_Model_Exception
     */
    public function getCvsFile($role, $number) {
        $log = Zend_Registry::get('Zend_Log');
        $collections = array();
        try {
            $model = new Collections_Model_ManageRole($role);
            $collections = $model->findCollectionByNumber($number);            
        }
        catch (Collections_Model_Exception $e) {
            $log($e->getMessage());
            throw $e;
        }
        if (count($collections) === 0) {
            $message = 'Number ' . $number . ' does not exist for collection role ' . $role;
            $log->debug($message);
            throw new Collections_Model_Exception($message);
        }
        $resultList = array();
        try {
            $resultList = $this->getListItems($collections);
        }
        catch (Opus_SolrSearch_Exception $e) {
            $log->debug($e->getMessage());
            throw new Collections_Model_Exception($e->getMessage());
        }
        return $this->prepareCsv($resultList);
    }

    private function getListItems($collections) {
        $query = new Opus_SolrSearch_Query(Opus_SolrSearch_Query::SIMPLE);
        $query->setCatchAll('*:*');
        foreach ($collections as $collection) {
            $query->addFilterQuery('collection_ids:' . $collection->getId());
        }
        $query->setRows(Opus_SolrSearch_Query::MAX_ROWS);
        $solrsearch = new Opus_SolrSearch_Searcher();
        return $solrsearch->search($query)->getResults();
    }

    private function prepareCsv($items) {
        $csv = '';
        foreach ($items as $item) {
            $csv .= $item->getId() . " , '" . $item->getTitle() . "' , '" . $item->getYear() . "'\n";
        }
        return $csv;
    }
}
?>