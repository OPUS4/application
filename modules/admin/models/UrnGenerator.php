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
 * @package     Module_Admin
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Admin_Model_UrnGenerator {

    // erforderlicher Parameter für die Generierung von URNs
    private $nid;

    // erforderlicher Parameter für die Generierung von URNs
    private $nss;

    /**
     * Admin_Model_IdentifierUrnGenerator constructor.
     *
     * @throws Application_Exception Exception wird geworfen, wenn ein erforderlicher Konfigurationsparameter nicht vorhanden ist
     */
    public function __construct() {
        $config = Zend_Registry::get('Zend_Config');

        if (!isset($config->urn->nid) || $config->urn->nid == '') {
            throw new Application_Exception('missing configuration setting for urn.nid - is required for URN generation');
        }
        $this->nid = $config->urn->nid;

        if (!isset($config->urn->nss) || $config->urn->nss == '') {
            throw new Application_Exception('missing configuration setting for urn.nss - is required for URN generation');
        }
        $this->nss = $config->urn->nss;
    }

    /**
     * Generiert eine URN für das Dokument mit der übergebenen ID.
     * Hierbei wird NICHT geprüft, ob das Dokument einen Volltext mit dem aktiven Flag visibleInOai besitzt.
     *
     * @param $docId ID des OPUS-Dokuments für das URN generiert werden soll
     * @return string
     */
    public function generateUrnForDocument($docId) {
        $identifierUrn = new Opus_Identifier_Urn($this->nid, $this->nss);
        $urn = $identifierUrn->getUrn($docId);

        $log = Zend_Registry::get('Zend_Log');
        $log->debug('URN generation result for document ' . $docId . ' is ' . $urn);

        return $urn;
    }
}
