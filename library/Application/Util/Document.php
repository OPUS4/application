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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\DocumentInterface;
use Opus\Common\Repository;
use Opus\Common\Security\Realm;
use Opus\Model\Xml;
use Opus\Model\Xml\Version1;

class Application_Util_Document
{
    /** @var DocumentInterface */
    private $document;

    /**
     * @param DocumentInterface $document
     * @throws Application_Exception
     */
    public function __construct($document)
    {
        $this->document = $document;
        if (! $this->checkPermission()) {
            throw new Application_Exception('document access for id ' . $this->document->getId() . ' not allowed');
        }
    }

    /**
     * @return bool
     */
    private function checkPermission()
    {
        if ($this->document->getServerState() === 'published') {
            return true;
        }
        $accessControl = Zend_Controller_Action_HelperBroker::getStaticHelper('accessControl');
        return Realm::getInstance()->checkDocument($this->document->getId())
                || $accessControl->accessAllowed('documents');
    }

    /**
     * @param bool $useCache
     * @return DOMNode Document node
     */
    public function getNode($useCache = true)
    {
        $cache = Repository::getInstance()->getDocumentXmlCache();

        $xmlModel = new Xml();
        $xmlModel->setModel($this->document);
        $xmlModel->excludeEmptyFields(); // needed for preventing handling errors
        $xmlModel->setStrategy(new Version1());
        if ($useCache) {
              $xmlModel->setXmlCache($cache);
        }
        $result = $xmlModel->getDomDocument();
        return $result->getElementsByTagName('Opus_Document')->item(0);
    }
}
