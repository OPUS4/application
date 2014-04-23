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
 * @package     Module_Export
 * @author      Michael Lang <lang@zib.de>
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */


class Export_Model_XmlExport extends Application_Model_Abstract {

    /*
     * prepares xml for solr search results
     */
    public function prepareXml($xml, $proc, $request) {
        try {
            $searcher = new Opus_SolrSearch_Searcher();
            $resultList = $searcher->search($this->buildQuery($request));
            $this->handleResults($resultList->getResults(), $resultList->getNumberOfHits(), $xml, $proc);
        }
        catch (Opus_SolrSearch_Exception $e) {
            $this->getLogger()->err(__METHOD__ . ' : ' . $e);
            throw new Application_SearchException($e, true);
        }
    }

    /*
     * prepares xml for frontdoor documents
     */
    public function prepareXmlForFrontdoor($xml, $proc, $request) {
        $docId = $request->getParam('docId', '');
        if ($docId == '') {
            $this->printDocumentError("frontdoor_doc_id_missing_in_url", 404);
            return;
        }
        try {
            $document = new Opus_Document($docId);
            $results = null;
            $results[0] = $document;
            $this->handleResults($results, 1, $xml, $proc);
        }
        catch (Opus_Model_NotFoundException $e) {
            $this->printDocumentError("frontdoor_doc_id_not_found_in_db", 404);
            return;
        }
    }

    /**
     *
     * @param array $results An array of Opus_SolrSearch_Result objects.
     */
    private function handleResults($results, $numOfHits, $xml, $proc) {
        $proc->setParameter('', 'timestamp', str_replace('+00:00', 'Z', Zend_Date::now()->setTimeZone('UTC')->getIso()));
        $proc->setParameter('', 'docCount', count($results));
        $proc->setParameter('', 'queryhits', $numOfHits);
        $xml->appendChild($xml->createElement('Documents'));

        $resultIds = array();
        foreach ($results as $result) {
            $resultIds[] = $result->getId();
        }
        if(!empty($resultIds)) {
            $documentCacheTable = new Opus_Db_DocumentXmlCache();
            $docXmlCache = $documentCacheTable->fetchAll($documentCacheTable->select()->where('document_id IN (?)', $resultIds));//->find($this->document->getId(), '1')->current()->xml_data;

            $processedIds = array();

            foreach($docXmlCache as $row) {
                $fragment = new DomDocument();
                $fragment->loadXML($row->xml_data);
                $domNode = $xml->importNode($fragment->getElementsByTagName('Opus_Document')->item(0), true);
                $xml->documentElement->appendChild($domNode);
                $processedIds[] = $row->document_id;
            }

            // create and append cache for documents without cache
            $unprocessedIds = array_diff($resultIds, $processedIds);

            if(!empty($unprocessedIds)) {
                foreach($unprocessedIds as $docId) {
                    $document = new Opus_Document($docId);
                    $documentXml = new Util_Document($document);
                    $domNode = $xml->importNode($documentXml->getNode(), true);
                    $xml->documentElement->appendChild($domNode);
                }
            }
        }
    }

    /*
     * sets up the xml query
     */
    private function buildQuery($request) {
        $queryBuilder = new Util_QueryBuilder($this->getLogger(), true);
        $queryBuilderInput = array();
        try {
            $queryBuilderInput = $queryBuilder->createQueryBuilderInputFromRequest($request);
        }
        catch (Util_QueryBuilderException $e) {
            $this->getLogger()->err(__METHOD__ . ' : ' . $e->getMessage());
            throw new Application_Exception($e->getMessage());
        }

        return $queryBuilder->createSearchQuery($queryBuilderInput);
    }

    /*
     * maps query for publist action
     */
    public function mapQuery($roleParam, $numberParam) {
        if (is_null(Opus_CollectionRole::fetchByName($roleParam))) {
            throw new Application_Exception('specified role does not exist');
        }

        $role = Opus_CollectionRole::fetchByName($roleParam);
        if ($role->getVisible() != '1') {
            throw new Application_Exception('specified role is invisible');
        }

        if (count(Opus_Collection::fetchCollectionsByRoleNumber($role->getId(), $numberParam)) == 0) {
            throw new Application_Exception('specified number does not exist for specified role');
        }

        $collection = null;
        foreach (Opus_Collection::fetchCollectionsByRoleNumber($role->getId(), $numberParam) as $coll) {
            if ($coll->getVisible() == '1' && is_null($collection)) {
                $collection = $coll;
            }
        }

        if (is_null($collection)) {
            throw new Application_Exception('specified collection is invisible');
        }

        return $collection;
    }

    /*
     * searches for available stylesheets and builds the path of the selected stylesheet
     */
    public function buildStylesheetPath($stylesheet, $path) {
        if (!is_null($stylesheet)) {

            $stylesheetsAvailable = array();
            $dir = new DirectoryIterator($path);
            foreach ($dir as $file) {
                if ($file->isFile() && $file->getFilename() != '.' && $file->getFilename() != '..' && $file->isReadable()) {
                    array_push($stylesheetsAvailable, $file->getBasename('.xslt'));
                }
            }
            $pos = array_search($stylesheet, $stylesheetsAvailable);
            if ($pos !== FALSE) {
                return $path . DIRECTORY_SEPARATOR .  $stylesheetsAvailable[$pos] . '.xslt';
            }
            throw new Application_Exception('given stylesheet does not exist or is not readable');
        }
        $pos = strrpos($path, '/');
        $scriptPath = substr($path, 0, ++$pos);
        return $scriptPath . 'stylesheets' . DIRECTORY_SEPARATOR . 'raw.xslt';
    }
}