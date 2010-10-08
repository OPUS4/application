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

class Remotecontrol_Model_DownloadList {

    /**
     * Return a csv representation of all documents that are associated to
     * the collection identfied by the given role and number (or name).
     *
     * @return string CSV output.
     * @throws Remotecontrol_Model_Exception Thrown if the database does not contain
     * a collection with the given properties or in case an error occurred while
     * getting all associated documents from the Solr index.
     */
    public function getCvsFile($role, $number, $name) {
        $log = Zend_Registry::get('Zend_Log');
        if (is_null($role) || (is_null($number) && is_null($name))) {
            $log->debug('role, number or name parameter is empty - could not process request.');
            throw new Remotecontrol_Model_Exception();
        }
        if (!is_null($number) && !is_null($name)) {
            $log->debug('both number and name are given - could not process request.');
            throw new Remotecontrol_Model_Exception();
        }

        $errorMsgPrefix = null;
        $collections = array();
        try {
            $model = new Remotecontrol_Model_ManageRole($role);
            if (is_null($name)) {
                $collections = $model->findCollectionByNumber($number);
                $errorMsgPrefix = "Number '" . $number;
            }
            else {
                $collections = $model->findCollectionByName($name);
                $errorMsgPrefix = "Name '" . $name;
            }
        }
        catch (Remotecontrol_Model_Exception $e) {
            $log->debug($e->getMessage());
            throw $e;
        }
        if (count($collections) === 0) {
            $message =  $errorMsgPrefix . "' does not exist for collection role " . $role;
            $log->debug($message);
            throw new Remotecontrol_Model_Exception($message);
        }
        if (count($collections) > 1) {
            $message = $errorMsgPrefix . "' is not unique for collection role " . $role;
            $log->debug($message);
            throw new Remotecontrol_Model_Exception($message, Remotecontrol_Model_Exception::NAME_IS_NOT_UNIQUE);
        }
        $resultList = array();
        try {
            $resultList = $this->getListItems($collections[0]->getId());
            $log->debug(count($resultList) . ' documents found.');
        }
        catch (Opus_SolrSearch_Exception $e) {
            $log->debug($e->getMessage());
            throw new Remotecontrol_Model_Exception($e->getMessage());
        }
        return $this->prepareCsv($resultList);
    }

    /**
     * Returns all index documents that are associated to the given collection id.
     *
     * @param int $collectionId
     * @return array An array of Opus_SolrSearch_Result objects.
     * @throws Opus_SolrSearch_Exception
     */
    private function getListItems($collectionId) {
        $query = new Opus_SolrSearch_Query(Opus_SolrSearch_Query::SIMPLE);
        $query->setCatchAll('*:*');
        $query->addFilterQuery('collection_ids:' . $collectionId);
        $query->setRows(Opus_SolrSearch_Query::MAX_ROWS);
        $solrsearch = new Opus_SolrSearch_Searcher();
        return $solrsearch->search($query)->getResults();
    }

    /**
     *
     * @param array $items
     * @return string A csv representation of the given items.
     */
    private function prepareCsv($items) {
        $csv = '';
        foreach ($items as $item) {
            $csv .= $item->getId() . " , '" . $item->getTitle() . "' , '" . $item->getYear() . "'\n";
        }
        return $csv;
    }
}
?>