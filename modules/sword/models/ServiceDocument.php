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
 * @copyright   Copyright (c) 2016, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Config;
use Opus\Import\Sword\ImportCollection;

class Sword_Model_ServiceDocument
{
    public const SWORD_VERSION = '1.3';

    public const SWORD_LEVEL = '1';

    public const SWORD_SUPPORT_VERBOSE_MODE = 'false';

    public const SWORD_SUPPORT_NOOP_MODE = 'false';

    /** @var DOMDocument */
    private $document;

    /** @var Zend_Config */
    private $config;

    /** @var string */
    private $fullUrl;

    /**
     * @param string $fullUrl
     */
    public function __construct($fullUrl)
    {
        $this->config  = Config::get();
        $this->fullUrl = $fullUrl;
        $this->initServiceDocument();
    }

    /**
     * @return DOMDocument
     */
    public function getDocument()
    {
        return $this->document;
    }

    private function initServiceDocument()
    {
        $this->document = new DOMDocument();
        $rootElement    = $this->document->createElementNS('http://www.w3.org/2007/app', 'service');
        $rootElement->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns', 'http://www.w3.org/2007/app');
        $rootElement->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:atom', 'http://www.w3.org/2005/Atom');
        $rootElement->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:sword', 'http://purl.org/net/sword/');
        $rootElement->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:dcterms', 'http://purl.org/dc/terms/');

        $this->addSwordElement('version', self::SWORD_VERSION, $rootElement);
        $this->addSwordElement('level', self::SWORD_LEVEL, $rootElement);
        $this->addSwordElement('verbose', self::SWORD_SUPPORT_VERBOSE_MODE, $rootElement);
        $this->addSwordElement('noOp', self::SWORD_SUPPORT_NOOP_MODE, $rootElement);

        $maxUploadSize = new Application_Configuration_MaxUploadSize();
        $this->addSwordElement('maxUploadSize', $maxUploadSize->getMaxUploadSizeInKB(), $rootElement);

        $workspaceNode = $this->document->createElement('workspace');
        $rootElement->appendChild($workspaceNode);

        $applicationName = $this->config->name;
        $node            = $this->document->createElementNS('http://www.w3.org/2005/Atom', 'atom:title', $applicationName);

        $workspaceNode->appendChild($node);

        $collectionNode = $this->document->createElement('collection');
        $this->setImportCollection($collectionNode);
        $workspaceNode->appendChild($collectionNode);

        // das Element app:accept definiert die zulässigen MIME-Types der Packages
        // OPUS soll voerst Packages im Format TAR und ZIP unterstützen
        // ein Upload von XML-Dokumenten (metadata only) ist nicht vorgesehen
        $node = $this->document->createElement('accept', 'application/zip');
        $collectionNode->appendChild($node);
        $node = $this->document->createElement('accept', 'application/tar');
        $collectionNode->appendChild($node);

        $collectionPolicy = $this->config->sword->collection->default->collectionPolicy;
        $node             = $this->document->createElementNS('http://purl.org/net/sword/', 'sword:collectionPolicy', $collectionPolicy);
        $collectionNode->appendChild($node);

        $node = $this->document->createElementNS('http://purl.org/net/sword/', 'sword:mediation', 'false');
        $collectionNode->appendChild($node);

        $treatment = $this->config->sword->collection->default->treatment;
        $node      = $this->document->createElementNS('http://purl.org/net/sword/', 'sword:treatment', $treatment);
        $collectionNode->appendChild($node);

        $acceptPackaging = $this->config->sword->collection->default->acceptPackaging;
        $node            = $this->document->createElementNS('http://purl.org/net/sword/', 'sword:acceptPackaging', $acceptPackaging);
        $node->setAttribute('q', '1.0');
        $collectionNode->appendChild($node);

        $abstract = $this->config->sword->collection->default->abstract;
        $node     = $this->document->createElementNS('http://purl.org/dc/terms/', 'dcterms:abstract', $abstract);
        $collectionNode->appendChild($node);

        $this->document->appendChild($rootElement);
    }

    /**
     * @param string     $name
     * @param string     $value
     * @param DOMElement $rootElement
     * @throws DOMException
     */
    private function addSwordElement($name, $value, $rootElement)
    {
        $node = $this->document->createElementNS('http://purl.org/net/sword/', 'sword:' . $name, $value);
        $rootElement->appendChild($node);
    }

    /**
     * @param DOMElement $collectionNode
     * @throws DOMException
     */
    private function setImportCollection($collectionNode)
    {
        $importCollection = new ImportCollection();
        if ($importCollection->exists()) {
            $node = $this->document->createElementNS('http://www.w3.org/2005/Atom', 'atom:title', $importCollection->getName());
            $collectionNode->appendChild($node);
            $href = $this->fullUrl . '/sword/index/index/' . $importCollection->getRoleName() . '/' . $importCollection->getNumber();
            $collectionNode->setAttribute('href', $href);
        }
    }
}
