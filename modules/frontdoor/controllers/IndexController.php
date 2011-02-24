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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 *
 */

class Frontdoor_IndexController extends Controller_Action {

    /**
     * Display the metadata of a document.
     *
     * @return void
     */
    public function indexAction() {

        $this->view->title = $this->view->translate('frontdoor_title');
        $request = $this->getRequest();
        $docId = $request->getParam('docId');
        $this->view->docId = $docId;
        $baseUrl = $request->getBaseUrl();

        try {
            $document = new Opus_Document($docId);

            $type = $document->getType();

            $xmlModel = new Opus_Model_Xml;
            $xmlModel->setModel($document);
            $xmlModel->excludeEmptyFields(); // needed for preventing handling errors
            $xmlModel->setStrategy(new Opus_Model_Xml_Version1);
            // FIXME: Xml_Cache contains empty fields
            //$xmlModel->setXmlCache(new Opus_Model_Xml_Cache);

            $xml = $xmlModel->getDomDocument()->getElementsByTagName('Opus_Document')->item(0);

            $xslt = new DomDocument;
            $template = $this->setUpXSLTStylesheet($type);
            $xslt->load($this->view->getScriptPath('index') . '/' . $template);
            $proc = new XSLTProcessor;
            $proc->registerPHPFunctions('Frontdoor_IndexController::translate');
            $proc->importStyleSheet($xslt);

            $this->view->baseUrl = $baseUrl;
            $this->view->doctype('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN"  "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">');

            $dateModified = $document->getServerDateModified();
            if (!is_null($dateModified)) {
                $this->view->headMeta()
                    ->appendHttpEquiv('Last-Modified', $dateModified->getZendDate()->get(Zend_Date::RFC_1123));
            }
            // $this->addMetaTagsForDocument($document);
            $this->setFrontdoorTitleToDocumentTitle($document);

            $config = Zend_Registry::getInstance()->get('Zend_Config');
            $deliver_url_prefix = isset($config->deliver->url->prefix) ? $config->deliver->url->prefix : '/documents';
            $layoutPath = 'layouts/'.(isset($config->theme) ? $config->theme : '');

            $proc->setParameter('', 'baseUrl', $baseUrl);
            $proc->setParameter('', 'deliverUrlPrefix', "$deliver_url_prefix");
            $proc->setParameter('', 'layoutPath', $baseUrl.'/'.$layoutPath);
            $proc->setParameter('', 'isMailPossible', ($this->isMailPossible($document) ? true : false));
            $this->view->frontdoor = $proc->transformToXML($xml);

            $this->incrementStatisticsCounter($docId);
        }
        catch (Zend_Db_Table_Rowset_Exception $e) {
            if ($e->getMessage() === 'No row could be found at position 0') {
                    $this->view->frontdoor = sprintf($this->view->translate('frontdoor_doc_id_not_found'), $docId);
            }
        }
    }

    private function setFrontdoorTitleToDocumentTitle($document) {
        $docLanguage = $document->getLanguage();
        $docLanguage = is_array($docLanguage) ? $docLanguage : array($docLanguage);

        $titleStringMain = "";
        $titleStringAlt = "";

        foreach ($document->getTitleMain() AS $title) {
            $titleValue = trim($title->getValue());
            if (empty($titleValue)) {
                continue;
            }

            if (in_array($title->getValue(), $docLanguage)) {
                $titleStringMain = $titleValue;
            }
            else {
                $titleStringAlt = $titleValue;
            }
        }

        if (!empty($titleStringMain)) {
            $this->view->title = $titleStringMain;
        }
        elseif (!empty($titleStringAlt)) {
            $this->view->title = $titleStringAlt;
        }
    }

    private function addMetaTagsForDocument($document) {
        foreach ($this->createMetaTagsForDocument($document) AS $pair) {
            $this->view->headMeta($pair[1], $pair[0]);
        }
    }

    private function createMetaTagsForDocument($document) {
        $config = Zend_Registry::getInstance()->get('Zend_Config');
        $serverUrl = $this->view->serverUrl();
        $baseUrlServer = $serverUrl . $this->getRequest()->getBaseUrl();
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

        foreach ($document->getIdentifierUrn() AS $identifier) {
            $identifierValue = trim($identifier->getValue());
            if (empty($identifierValue)) {
                continue;
            }
            $metas[] = array('DC.Identifier', $identifierValue);
        }
        $metas[] = array('DC.Identifier', $baseUrlServer . '/frontdoor/index/index/docId/'. $document->getId());

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
            // $date = new Opus_Date();
            $dateString = $datePublished->getZendDate()->get('yyyy-MM-dd');

            $metas[] = array("citation_date", $dateString);
            $metas[] = array("DC.Date", $dateString);
        }

        return $metas;
    }

    private function setUpXSLTStylesheet($type) {
        $template = null;
        if (file_exists($this->view->getScriptPath('index') . '/' . $type . '.xslt'))
            $template = $type . '.xslt';
        else
            $template = 'index.xslt';
        return $template;
    }

    private function incrementStatisticsCounter($docId) {
        $statistics = Opus_Statistic_LocalCounter::getInstance();
        $statistics->countFrontdoor($docId);
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

    private function isMailPossible($document) {
        foreach ($document->getPersonAuthor() as $author) {
            $mail = $author->getEmail();
            if ($author->getAllowEmailContact() && !empty($mail)) {
                return true;
            }
        }

        return false;
    }

}
