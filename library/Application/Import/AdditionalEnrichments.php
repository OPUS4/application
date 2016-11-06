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
 * @package     Import
 * @author      Sascha Szott
 * @copyright   Copyright (c) 2016
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Application_Import_AdditionalEnrichments {
    
    const OPUS_IMPORT_USER = 'opus.import.user';
    
    const OPUS_IMPORT_DATE = 'opus.import.date';
    
    const OPUS_IMPORT_FILE = 'opus.import.file';
    
    const OPUS_IMPORT_CHECKSUM = 'opus.import.checksum';
    
    private $enrichmentMap;
    
    public function checkKeysExist() {
        return $this->keyExist(self::OPUS_IMPORT_USER) 
                && $this->keyExist(self::OPUS_IMPORT_DATE) 
                && $this->keyExist(self::OPUS_IMPORT_FILE) 
                && $this->keyExist(self::OPUS_IMPORT_CHECKSUM);
    }
    
    private function keyExist($key) {
        $enrichmentkey = Opus_EnrichmentKey::fetchByName($key);
        return !is_null($enrichmentkey);
    }
        
    public function addEnrichment($key, $value) {
        $this->enrichmentMap[$key] = $value;
    }
    
    public function getEnrichments() {
        return $this->enrichmentMap;
    }
    
    public function addUser($value) {
        $this->addEnrichment(self::OPUS_IMPORT_USER, trim($value));
    }
    
    public function addDate($value) {
        $this->addEnrichment(self::OPUS_IMPORT_DATE, trim($value));
    }
    
    public function addFile($value) {
        $this->addEnrichment(self::OPUS_IMPORT_FILE, trim($value));
    }
    
    public function addChecksum($value) {
        $this->addEnrichment(self::OPUS_IMPORT_CHECKSUM, trim($value));
    }
    
    public function getChecksum() {
        if (!array_key_exists(self::OPUS_IMPORT_CHECKSUM, $this->enrichmentMap)) {
            return null;            
        }
        return $this->enrichmentMap[self::OPUS_IMPORT_CHECKSUM];
    }
}
