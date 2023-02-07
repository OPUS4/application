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

require_once 'CronTestCase.php';

use Opus\Common\Licence;
use Opus\Common\Repository;

class UpdateDocumentCacheTest extends CronTestCase
{
    /** @var string */
    protected $additionalResources = 'database';

    public function testUpdateOnLicenceChange()
    {
        $document = $this->createTestDocument();
        $docId    = $document->store();

        $documentCache = Repository::getInstance()->getDocumentXmlCache();

        $docXmlCache = $documentCache->getData($docId, '1');
        $domDoc      = new DOMDocument();
        $domDoc->loadXML($docXmlCache);
        $licences = $domDoc->getElementsByTagName('Licence');
        $this->assertTrue($licences->length === 0, 'Expected no Licence element in dom.');

        $licence = Licence::new();
        $licence->setNameLong('TestLicence');
        $licence->setLinkLicence('http://example.org/licence');
        $licenceId = $licence->store();
        $document->setServerState('published');
        $document->setLicence($licence);
        $document->store();

        $licence = Licence::get($licenceId);
        $licence->setNameLong('TestLicenceAltered');
        $licence->store();

        $this->assertFalse($documentCache->hasCacheEntry($docId, '1'), 'Expected empty document xml cache');

        $this->executeScript('cron-update-document-cache.php');
        $docXmlCacheAfter = $documentCache->getData($docId, '1');
        $domDocAfter      = new DOMDocument();
        $domDocAfter->loadXML($docXmlCacheAfter);
        $licencesAfter = $domDocAfter->getElementsByTagName('Licence');
        $this->assertTrue($licencesAfter->length === 1, 'Expected one Licence element in dom.');
        $licences = $document->getLicence();
        $licences[0]->delete();
    }
}
