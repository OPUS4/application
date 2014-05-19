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
 * @copyright   Copyright (c) 2014, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 *
 */

class Frontdoor_IndexController extends Controller_Action {

    const SERVER_STATE_DELETED = 'deleted';
    const SERVER_STATE_UNPUBLISHED = 'unpublished';

    // functions
    const TRANSLATE_FUNCTION = 'Frontdoor_IndexController::translate';
    const TRANSLATE_DEFAULT_FUNCTION = 'Frontdoor_IndexController::translateWithDefault';
    const FILE_ACCESS_FUNCTION = 'Frontdoor_IndexController::checkIfUserHasFileAccess';
    const FORMAT_DATE_FUNCTION = 'Frontdoor_IndexController::formatDate';
    const EMBARGO_ACCESS_FUNCTION = 'Frontdoor_IndexController::checkIfFileEmbargoHasPassed';
    const SORT_ORDER_FUNCTION = 'Frontdoor_IndexController::useSortOrder';
    const CHECK_LANGUAGE_FILE_FUNCTION = 'Frontdoor_IndexController::checkLanguageFile';

    private $viewHelper;

    public function init() {
        parent::init();
        $this->viewHelper = new View_Helper_BaseUrl();
    }
    /**
     * Displays the metadata of a document.
     * @return void
     */
    public function indexAction() {
        // call export index-action, if parameter is set
        if (!is_null($this->getRequest()->getParam('export'))) {

            $params = $this->getRequest()->getParams();
            // export module ignores pagination parameters
            unset($params['rows']);
            unset($params['start']);

            return $this->_redirectToAndExit('index', null, 'index', 'export', $params);
        }

        $this->view->title = $this->view->translate('frontdoor_title');
        $request = $this->getRequest();
        $docId = $request->getParam('docId', '');
        $this->view->docId = $docId;
        $baseUrl = $request->getBaseUrl();

        if ($docId == '') {
            $this->printDocumentError("frontdoor_doc_id_missing", 404);
            return;
        }

        $document = null;
        try {
            $document = new Opus_Document($docId);
        }
        catch (Opus_Model_NotFoundException $e) {
            $this->printDocumentError("frontdoor_doc_id_not_found", 404);
            return;
        }

        $documentXml = null;
        try {
            $documentXml = new Util_Document($document);
        }
        catch (Application_Exception $e) {
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
        $xslt = new DomDocument;
        $xsltFileName = $this->view->getScriptPath('index') . DIRECTORY_SEPARATOR . 'index';
        if (file_exists($xsltFileName . '_custom.xslt')) {
            $xslt->load($xsltFileName . '_custom.xslt');
        } else {
            $xslt->load($xsltFileName . '.xslt');
        }
        $proc = new XSLTProcessor;
        $proc->registerPHPFunctions(self::TRANSLATE_FUNCTION);
        $proc->registerPHPFunctions(self::TRANSLATE_DEFAULT_FUNCTION);
        $proc->registerPHPFunctions(self::FILE_ACCESS_FUNCTION);
        $proc->registerPHPFunctions(self::FORMAT_DATE_FUNCTION);
        $proc->registerPHPFunctions(self::EMBARGO_ACCESS_FUNCTION);
        $proc->registerPHPFunctions(self::SORT_ORDER_FUNCTION);
        $proc->registerPHPFunctions(self::CHECK_LANGUAGE_FILE_FUNCTION);
        $proc->registerPHPFunctions('urlencode');
        $proc->importStyleSheet($xslt);

        $config = Zend_Registry::getInstance()->get('Zend_Config');
        $layoutPath = 'layouts/' . (isset($config, $config->theme) ? $config->theme : '');
        $numOfShortAbstractChars = isset($config, $config->frontdoor->numOfShortAbstractChars) ? $config->frontdoor->numOfShortAbstractChars : '0';

        $proc->setParameter('', 'baseUrlServer', $this->viewHelper->fullUrl($this->view));
        $proc->setParameter('', 'baseUrl', $baseUrl);
        $proc->setParameter('', 'layoutPath', $baseUrl . '/' . $layoutPath);
        $proc->setParameter('', 'isMailPossible', $this->isMailPossible($document));
        $proc->setParameter('', 'numOfShortAbstractChars', $numOfShortAbstractChars);
        
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
        $this->view->doctype('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN"  "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">');

        $dateModified = $document->getServerDateModified();
        if (!is_null($dateModified)) {
            $this->view->headMeta()
                    ->appendHttpEquiv('Last-Modified', $dateModified->getDateTime()->format(DateTime::RFC1123));
        }
        $this->addMetaTagsForDocument($document);
        $this->view->title = $this->getFrontdoorTitle($document);

        if (file_exists($this->view->layoutPath() . '/js/LaTeXMathML.js')) {
            $this->view->headScript()->appendFile($this->view->layoutPath() . '/js/LaTeXMathML.js', 'text/javascript');
        }

        $this->incrementStatisticsCounter($docId);
        
        $actionbox = new Admin_Form_ActionBox();
        $actionbox->prepareRenderingAsView();
        $actionbox->populateFromModel($document);
        $this->view->adminform = $actionbox;
    }

    private function printDocumentError($message, $code) {
        $this->view->errorMessage = $message;
        $this->getResponse()->setHttpResponseCode($code);
        $this->render('document-error');
    }

    /**
     *
     * @param Opus_Document $doc
     */
    private function isMailPossible($doc) {
        $authors = new Frontdoor_Model_Authors($doc);
        return count($authors->getContactableAuthors()) > 0;
    }


    /**
     * Static function to be called from XSLT script to check file permission.
     *
     * @param string|int $file_id
     * @return boolean
     */
    public static function checkIfUserHasFileAccess($file_id = null) {
        if (is_null($file_id)) {
            return false;
        }

        $realm = Opus_Security_Realm::getInstance();
        return $realm->checkFile($file_id);
    }

    public static function checkIfFileEmbargoHasPassed($docId) {
        $doc = new Opus_Document($docId);
        return $doc->hasEmbargoPassed();
    }

    /**
     * Checks existence of language sign for services.xsl
     * @param $filename
     * @return bool
     */
    public static function checkLanguageFile($language) {
        if (file_exists(APPLICATION_PATH . '/public/layouts/opus4/img/flag/' . $language . '.png')) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * If the sortorder-value of any attached file is set, this function returns the files in the correct sortorder;
     * otherwise files are returned in attached order.
     */
    public static function useSortOrder($docId) {
        $doc = new Opus_Document($docId);
        $files = $doc->getFile();
        if (sizeof($files > 1)) {
            foreach ($files as $file) {
                if (!is_null($file->getSortOrder())) {
                    return true;
                }
            }
            return false;
        }
        else {
            return false;
        }
    }

    /**
     *
     * @param Opus_Document $document
     * @return string
     */
    private function getFrontdoorTitle($document) {
        $titlesMain = $document->getTitleMain();
        if (count($titlesMain) == 0) {
            return '';
        }

        $docLanguage = $document->getLanguage();
        $docLanguage = is_array($docLanguage) ? $docLanguage : array($docLanguage);

        $firstNonEmptyTitle = '';

        foreach ($titlesMain AS $title) {
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

    private function addMetaTagsForDocument($document) {
        foreach ($this->createMetaTagsForDocument($document) AS $pair) {
            $this->view->headMeta($pair[1], $pair[0]);
        }
    }

    private function createMetaTagsForDocument($document) {
        $config = Zend_Registry::getInstance()->get('Zend_Config');
        $serverUrl = $this->view->serverUrl();
        $baseUrlFiles = $serverUrl . (isset($config, $config->deliver->url->prefix) ? $config->deliver->url->prefix : '/documents');

        $metas = array();

        foreach ($document->getPersonAuthor() AS $author) {
            $lastname = trim($author->getLastName());
            if (empty($lastname)) {
                continue;
            }
            $name = $lastname;

            $firstname = trim($author->getFirstName());
            if (!empty($firstname)) {
                $name .= ", " . $firstname;
            }

            $metas[] = array('DC.Creator', $name);
            $metas[] = array('author', $name);
            $metas[] = array('citation_author', $name);
        }

        foreach ($document->getTitleMain() AS $title) {
            $titleValue = trim( $title->getValue() );
            if (empty($titleValue)) {
                continue;
            }
            $metas[] = array('DC.title', $titleValue);
            $metas[] = array('title', $titleValue);
            $metas[] = array('citation_title', $titleValue);
        }

        foreach ($document->getTitleAbstract() AS $abstract) {
            $abstractValue = trim( $abstract->getValue() );
            if (empty($abstractValue)) {
                continue;
            }
            $metas[] = array('DC.Description', $abstractValue);
            $metas[] = array('description', $abstractValue);
        }

        $subjectsArray = array();
        foreach ($document->getSubject() AS $subject) {
            $subjectValue = trim($subject->getValue());
            if (empty($subjectValue)) {
                continue;
            }
            $metas[] = array('DC.subject', $subjectValue);
            $subjectsArray[] = $subjectValue;
        }
        if (count($subjectsArray) > 0) {
            $subjectsArray = array_unique($subjectsArray);
            $metas[] = array('keywords', implode(", ", $subjectsArray));
        }

        foreach ($document->getIdentifierEu() as $identifier) {
            $identifierValue = trim($identifier->getValue());
            if (empty($identifierValue)) {
                continue;
            }
            $metas[] = array('EU.Identifier', $identifierValue);
        }

        foreach ($document->getIdentifierUrn() AS $identifier) {
            $identifierValue = trim($identifier->getValue());
            if (empty($identifierValue)) {
                continue;
            }
            $metas[] = array('DC.Identifier', $identifierValue);
        }
        $metas[] = array('DC.Identifier', $this->viewHelper->fullUrl($this->view) . '/frontdoor/index/index/docId/'. $document->getId());

        foreach ($document->getFile() AS $file) {
            if (!$file->exists() or ($file->getVisibleInFrontdoor() !== '1') ) {
                continue;
            }
            $metas[] = array('DC.Identifier', "$baseUrlFiles/" . $document->getId() . "/" . $file->getPathName());

            if ($file->getMimeType() == 'application/pdf') {
                $metas[] = array('citation_pdf_url', "$baseUrlFiles/" . $document->getId() . "/" . $file->getPathName());
            }
            else if ($file->getMimeType() == 'application/postscript') {
                $metas[] = array('citation_ps_url', "$baseUrlFiles/" . $document->getId() . "/" . $file->getPathName());
            }
        }

        $datePublished = $document->getPublishedDate();
        if (!is_null($datePublished)) {

            $dateString = $datePublished->getZendDate()->get('yyyy-MM-dd');

            $metas[] = array("citation_date", $dateString);
            $metas[] = array("DC.Date", $dateString);
        } else {
            $yearPublished = $document->getPublishedYear();
            if(!is_null($yearPublished)) {

                $metas[] = array("citation_date", $yearPublished);
                $metas[] = array("DC.Date", $yearPublished);
            }
        }

        return $metas;
    }

    private function incrementStatisticsCounter($docId) {
        try {
            $statistics = Opus_Statistic_LocalCounter::getInstance();
            $statistics->countFrontdoor($docId);
        }
        catch (Exception $e) {
            $this->_logger->err("Counting frontdoor statistics failed: " . $e);
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
    public function mapopus3Action() {
        $docId = $this->getRequest()->getParam('oldId');
        $this->_redirectToAndExit('id', '', 'index', 'rewrite', array('type' => 'opus3-id', 'value' => $docId));
    }

    /**
     * Gateway function to Zend's translation facilities.
     *
     * @param  string  $key The key of the string to translate.
     * @return string  The translated string.
     */
    static public function translate($key) {
        $registry = Zend_Registry::getInstance();
        $translate = $registry->get('Zend_Translate');
        return $translate->_($key);
    }

    /**
     * Gateway function to Zend's translation facilities.  Falls back to default
     * if no translation exists.
     *
     * @param  string  $key     The key of the string to translate.
     * @param  string  $default The default value of no translation exists
     * @return string  The translated string *or* the default value
     */
    static public function translateWithDefault($key, $default = '') {
        $translate = Zend_Registry::get('Zend_Translate');
        /* @var $translate Zend_Translate_Adapter */
        if ($translate->isTranslated($key)) {
            return $translate->_($key);
        }
        return $default;
    }

    static public function formatDate($day, $month, $year) {
        $date = new DateTime();
        $date->setDate($year, $month, $day);
        $session = new Zend_Session_Namespace();
        // TODO aktuell werden nur zwei Sprachen unterstützt
        $formatPattern = ($session->language == 'de') ? 'd.m.Y' : 'Y/m/d';
        return date_format($date, $formatPattern);
    }

}