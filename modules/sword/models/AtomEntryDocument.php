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
 * @package     Module_Sword
 * @author      Sascha Szott
 * @copyright   Copyright (c) 2016
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Sword_Model_AtomEntryDocument {
    
    private $entries = array();
    
    private $fullUrl;
    
    public function setEntries($entries) {
        $this->entries = $entries;
    }
    
    public function setResponse($request, $response, $fullUrl, $userName) {
        $response->setHttpResponseCode(201);
        if (count($this->entries) == 1) {
            // Location Header nur beim Import von einem Dokument, 
            // um SWORD-Compliance sicherzustellen
            $doc = $this->entries[0];
            $response->setHeader('Location', $fullUrl . '/frontdoor/index/index/docId/' . $doc->getId());
        }     
        $response->setHeader('Content-Type', 'application/atom+xml; charset=UTF-8');
        $this->fullUrl = $fullUrl;
        
        if (!empty($this->entries)) {
            $config = Zend_Registry::get('Zend_Config');
            $prettyPrinting = $config->prettyXml;
            if ($prettyPrinting == 'true') {            
                $dom = new DOMDocument;
                $dom->preserveWhiteSpace = false;
                $dom->formatOutput = true;     
                $xml = $this->getXml($request, $userName);
                $dom->loadXML($xml);
                $response->setBody($dom->saveXml());        
            }
            else {
                $xml = $this->getXml($request, $userName);
                $response->setBody($xml);
            }            
        }        
    }    
    
    private function buildAtomEntryDocPart($doc, $root, $userName) {
        $root->addChild('id', $doc->getId());
        $root->addChild('updated', $doc->getServerDateCreated());
        
        $title = $doc->getTitleMain();
        if (!is_null($title) && !empty($title)) {
            $root->addChild('title', $title[0]->getValue());
        }
        
        $author = $root->addChild('author');
        $author->addChild('name', $userName);

        $abstract = $doc->getTitleAbstract();
        if (!is_null($abstract) && !empty($abstract)) {
            $summary = $root->addChild('summary', $abstract[0]->getValue());
            $summary->addAttribute('type', 'text');
        }

        $content = $root->addChild('content');
        $content->addAttribute('type', 'text/html');
        $content->addAttribute('src', $this->fullUrl . '/frontdoor/index/index/docId/' . $doc->getId());        
    }
    
    private function handleSingleEntry($userName, $request) {
        $root = new SimpleXMLElement('<entry xmlns="http://www.w3.org/2005/Atom" xmlns:sword="http://purl.org/net/sword/"></entry>');
        $doc = $this->entries[0];
        $this->buildAtomEntryDocPart($doc, $root, $userName);
        $this->addSwordElements($root, $request);
        return $root;
    }
    
    /**
     * Das ist eine OPUS-spezifische Erweiterung des SWORD-Standards.
     * Daher verwenden wir hier einen separaten Namespace.
     */
    private function handleMultipleEntries($userName, $request) {
        $root = new SimpleXMLElement('<opus:entries xmlns="http://www.w3.org/2005/Atom" xmlns:opus="http://www.opus-repository.org" xmlns:sword="http://purl.org/net/sword/"></opus:entries>');
        foreach ($this->entries as $doc) {
            $entryRoot = $root->addChild('entry', null, 'http://www.w3.org/2005/Atom');
            $this->buildAtomEntryDocPart($doc, $entryRoot, $userName);
            $this->addSwordElements($entryRoot, $request);
        }
        return $root;
    }
    
    private function addSwordElements($rootElement, $request) {
        $config = Zend_Registry::get('Zend_Config');
        $generator = $config->sword->generator;        
        $rootElement->addChild('generator', $generator);
        
        // should we sanitize the value of $userAgent before setting HTTP response header?
        $userAgent = $request->getHeader('User-Agent');
        if (is_null($userAgent) || $userAgent === false) {
            $userAgent = 'n/a';
        }
        $swordNamespaceURI = 'http://purl.org/net/sword/';        
        $rootElement->addChild('sword:userAgent', $userAgent, $swordNamespaceURI);
        
        $treatment = $config->sword->treatment;
        $rootElement->addChild('sword:treatment', $treatment, $swordNamespaceURI);
        
        $packaging = $config->sword->collection->default->acceptPackaging;
        $rootElement->addChild('sword:packaging', $packaging, $swordNamespaceURI);
        
        // features that are currently not supported by OPUS
        $rootElement->addChild('sword:verboseDescription', '', $swordNamespaceURI);
        $rootElement->addChild('sword:noOp', 'false', $swordNamespaceURI);        
    }
    
    private function getXml($request, $userName) {
        if (count($this->entries) > 1) {
            $rootElement = $this->handleMultipleEntries($userName, $request);
        }
        else {
            $rootElement = $this->handleSingleEntry($userName, $request);
        }
        return $rootElement->asXML();
    }

}
