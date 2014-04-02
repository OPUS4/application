<?php
/**
 * Created by IntelliJ IDEA.
 * User: michael
 * Date: 3/17/14
 * Time: 11:45 AM
 * To change this template use File | Settings | File Templates.
 */

class Export_Model_XMLExport {

    private $log;

    public function __construct() {

        $this->log = Zend_Registry::get('Zend_Log');
    }

    public function prepareXml($xml, $proc, $request) {
        try {
            $searcher = new Opus_SolrSearch_Searcher();
            $resultList = $searcher->search($this->buildQuery($request));
            $this->handleResults($resultList->getResults(), $resultList->getNumberOfHits(), $xml, $proc);
        }
        catch (Opus_SolrSearch_Exception $e) {
            $this->log->err(__METHOD__ . ' : ' . $e);
            throw new Application_SearchException($e, true);
        }
    }

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

    private function buildQuery($request) {
        $queryBuilder = new Util_QueryBuilder($this->log, true);
        $queryBuilderInput = array();
        try {
            $queryBuilderInput = $queryBuilder->createQueryBuilderInputFromRequest($request);
        }
        catch (Util_QueryBuilderException $e) {
            $this->log->err(__METHOD__ . ' : ' . $e->getMessage());
            throw new Application_Exception($e->getMessage());
        }

        return $queryBuilder->createSearchQuery($queryBuilderInput);
    }

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

}