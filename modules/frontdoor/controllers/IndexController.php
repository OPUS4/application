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
 * @package     Module_Frontdoor
 * @author      Felix Ostrowski <ostrowski@hbz-nrw.de>
 * @author      Michael Lang <lang@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2014-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */
class Frontdoor_IndexController extends Application_Controller_Action
{

    /**
     * TODO should be defined in central model classes
     */
    const SERVER_STATE_DELETED = 'deleted';
    const SERVER_STATE_UNPUBLISHED = 'unpublished';

    /**
     * Displays the metadata of a document.
     * @return void
     */
    public function indexAction()
    {
        $request = $this->getRequest();

        $docId = $this->handleSearchResultNavigation();

        if ($docId === false) {
            return;
        } elseif ($docId == '') {
            // TODO can this be reached?
            $this->printDocumentError("frontdoor_doc_id_missing", 404);
            return;
        }

        $this->view->title = $this->view->translate('frontdoor_title');
        $this->view->docId = $docId;
        $baseUrl = $request->getBaseUrl();

        $document = null;
        try {
            $document = new Opus_Document($docId);
        } catch (Opus_Model_NotFoundException $e) {
            $this->printDocumentError("frontdoor_doc_id_not_found", 404);
            return;
        }

        $documentXml = null;
        try {
            $documentXml = new Application_Util_Document($document);
        } catch (Application_Exception $e) {
            switch ($document->getServerState()) {
                case self::SERVER_STATE_DELETED:
                    $this->printDocumentError("frontdoor_doc_deleted", 410);
                    return;
                case self::SERVER_STATE_UNPUBLISHED:
                    $this->printDocumentError("frontdoor_doc_unpublished", 403);
                    return;
            }
            $this->printDocumentError("frontdoor_doc_access_denied", 403);
            return;
        }

        $documentNode = $documentXml->getNode();

        /* XSLT transformation. */
        $docBuilder = new Frontdoor_Model_DocumentBuilder();
        $xslt = $docBuilder->buildDomDocument($this->view->getScriptPath('index') . DIRECTORY_SEPARATOR . 'index');

        $proc = new XSLTProcessor;
        Application_Xslt::registerViewHelper($proc, [
            'locale',
            'optionEnabled',
            'optionValue',
            'translate',
            'translateLanguage',
            'translateIdentifier',
            'translateWithDefault',
            'formatDate',
            'isDisplayField',
            'fileAccessAllowed',
            'embargoHasPassed',
            'customFileSortingEnabled',
            'languageImageExists',
            'frontdoorStylesheet',
            'shortenText',
            'exportLinks',
            'languageWebForm',
            'mimeTypeAsCssClass',
            'accessAllowed'
        ]);
        $proc->registerPHPFunctions('urlencode');
        $proc->importStyleSheet($xslt);

        $config = $this->getConfig();
        $layoutPath = 'layouts/' . (isset($config, $config->theme) ? $config->theme : '');
        $numOfShortAbstractChars = $this->view->getHelper('shortenText')->getMaxLength();

        $proc->setParameter('', 'baseUrlServer', $this->view->fullUrl());
        $proc->setParameter('', 'baseUrl', $baseUrl);
        $proc->setParameter('', 'layoutPath', $baseUrl . '/' . $layoutPath);
        $proc->setParameter('', 'isMailPossible', $this->isMailPossible($document));
        $proc->setParameter('', 'numOfShortAbstractChars', $numOfShortAbstractChars);
        $proc->setParameter('', 'urnResolverUrl', $config->urn->resolverUrl);

        /* print on demand config */
        $printOnDemandEnabled = false;
        $podConfig = $config->get('printOnDemand', false);
        if ($podConfig !== false) {
            $printOnDemandEnabled = true;
            $proc->setParameter('', 'printOnDemandUrl', $podConfig->get('url', ''));
            $proc->setParameter('', 'printOnDemandButton', $podConfig->get('button', ''));
        }
        $proc->setParameter('', 'printOnDemandEnabled', $printOnDemandEnabled);

        $frontdoorContent = $proc->transformToXML($documentNode);

        /* Setup view. */
        $this->view->frontdoor = $frontdoorContent;
        $this->view->baseUrl = $baseUrl;
        $this->view->doctype(
            '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">'
        );

        $dateModified = $document->getServerDateModified();
        if (! is_null($dateModified)) {
            $this->view->headMeta()
                    ->appendHttpEquiv('Last-Modified', $dateModified->getDateTime()->format(DateTime::RFC1123));
        }
        $this->addMetaTagsForDocument($document);
        $this->view->title = $this->getFrontdoorTitle($document);

        $this->incrementStatisticsCounter($docId);

        $actionbox = new Frontdoor_Form_FrontdoorActionBox();
        $actionbox->prepareRenderingAsView();
        $actionbox->populateFromModel($document);
        $this->view->adminform = $actionbox;
    }

    private function printDocumentError($message, $code)
    {
        $this->view->errorMessage = $message;
        $this->getResponse()->setHttpResponseCode($code);
        $this->render('document-error');
    }

    /**
     *
     * @param Opus_Document $doc
     */
    private function isMailPossible($doc)
    {
        $authors = new Frontdoor_Model_Authors($doc);
        return count($authors->getContactableAuthors()) > 0;
    }

    /**
     *
     * @param Opus_Document $document
     * @return string
     */
    private function getFrontdoorTitle($document)
    {
        $titlesMain = $document->getTitleMain();
        if (count($titlesMain) == 0) {
            return '';
        }

        $docLanguage = $document->getLanguage();
        $docLanguage = is_array($docLanguage) ? $docLanguage : [$docLanguage];

        $firstNonEmptyTitle = '';

        foreach ($titlesMain as $title) {
            $titleValue = trim($title->getValue());
            if (strlen($titleValue) == 0) {
                continue;
            }

            if (in_array($title->getLanguage(), $docLanguage)) {
                return $titleValue;
            }

            if ($firstNonEmptyTitle == '') {
                $firstNonEmptyTitle = $titleValue;
            }
        }

        return $firstNonEmptyTitle;
    }

    private function addMetaTagsForDocument($document)
    {
        $htmlMetaTags = new Frontdoor_Model_HtmlMetaTags($this->getConfig(), $this->view->fullUrl());
        $tags = $htmlMetaTags->createTags($document);
        foreach ($tags as $pair) {
            if (count($pair) > 2) {
                $this->view->headMeta($pair[1], $pair[0], 'name', $pair[2]);
            } else {
                $this->view->headMeta($pair[1], $pair[0]);
            }
        }
    }

    private function incrementStatisticsCounter($docId)
    {
        try {
            $statistics = Opus_Statistic_LocalCounter::getInstance();
            $statistics->countFrontdoor($docId);
        } catch (Exception $e) {
            $this->getLogger()->err("Counting frontdoor statistics failed: " . $e);
        }
    }

    /**
     * maps an old ID from OPUS3 to the new one in OPUS4
     *
     * @deprecated since OPUS 4.0.3: this function will be removed in future releases
     * use Rewrite_IndexController instead
     *
     * @return void
     */
    public function mapopus3Action()
    {
        $docId = $this->getRequest()->getParam('oldId');
        $this->_helper->Redirector->redirectToAndExit(
            'id',
            '',
            'index',
            'rewrite',
            ['type' => 'opus3-id', 'value' => $docId]
        );
    }

    /**
     * Handles parameters for search result navigation.
     *
     * The parameters define a position in the search, like the 6. document. If a docId is provided that document is
     * displayed in any case. However a search for the provided position is performed and compared if the IDs match.
     * If they don't match the search result might have changed and a message is printed.
     *
     * If no docId is provided a redirect to the document found by the search is performed without a message.
     *
     * @return mixed
     * @throws Application_Exception
     */
    protected function handleSearchResultNavigation()
    {
        $request = $this->getRequest();
        $docId = $request->getParam('docId', '');

        if (is_array($docId)) {
            $docId = end($docId);
        }

        $messages = null;

        if ($request->has('searchtype') && $request->has('rows') && $request->has('start')) {
            $listRows = $request->getParam('rows');

            $start = $request->getParam('start');

            $this->view->listRows = $listRows;

            $request->setParam('rows', '1'); // make sure only 1 entry is displayed

            $searchType = $request->getParam('searchtype');

            $searchFactory = new Solrsearch_Model_Search();

            $search = $searchFactory->getSearchPlugin($searchType);

            $query = $search->getQueryUrl($request);

            // TODO fix usage of search code - should be identical to search/export/rss - except just 1 row

            $searcher = new Opus\Search\Util\Searcher();

            $resultList = $searcher->search($query);

            $queryResult = $resultList->getResults();

            if (is_array($queryResult) && ! empty($queryResult) && $queryResult[0] instanceof Opus\Search\Result\Match) {
                $resultDocId = $queryResult[0]->getId();

                if ($request->has('docId')) {
                    if ($resultDocId != $docId) {
                        $messages = ['notice' => $this->view->translate('frontdoor_pagination_list_changed')];
                    }
                } else {
                    $this->redirect($this->view->url(['docId' => $resultDocId]), ['prependBase' => false]);
                    return false;
                }
            }

            $this->view->messages = $messages;

            $this->view->paginate = true;
            $numHits = $resultList->getNumberOfHits();

            if ($request->getParam('searchtype') == 'latest') {
                $this->view->numOfHits = $numHits < $listRows ? $numHits : $listRows;
            } else {
                $this->view->numOfHits = $numHits;
            }

            $this->view->searchPosition = $start;
            $this->view->firstEntry = 0;
            $this->view->lastEntry = $this->view->numOfHits - 1;
            $this->view->previousEntry = ($this->view->searchPosition - 1) < 0 ? 0 : $this->view->searchPosition - 1;
            $this->view->nextEntry = ($this->view->searchPosition + 1) < $this->view->numOfHits - 1 ? $this->view->searchPosition + 1 : $this->view->numOfHits - 1;
        }

        return $docId;
    }
}
