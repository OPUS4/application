<?php
/*
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
 * along with OPUS; >if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 */

/**
 * Unit Tests for Admin_Model_OpusInfo.
 * 
 * @category    Application Unit Tests
 * @package     Admin_Model
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Admin_Model_OpusInfoTest extends ControllerTestCase {
    
    private $deleteVersionFile = false;
    
    private $version = null;
    
    private $versionFile = null; 

    public function setUp() {
        parent::setUp();
        
        $this->versionFile = APPLICATION_PATH . '/VERSION.txt';
        
        $this->version = 'UNKNOWN';
        
        if(!is_file($this->versionFile)) {
            // Create VERSION.txt for testing
            file_put_contents($this->versionFile, $this->version);
            $this->deleteVersionFile = true;
        } 
        else {
            $this->version = trim(file_get_contents($this->versionFile));
        }        
    }
    
    public function tearDown() {
        if($this->deleteVersionFile) {
            unlink ($this->versionFile);
        }
    }
    
    public function testGetVersion() {
        $info = new Admin_Model_OpusInfo();
        
        $version = $info->getVersion();
        
        $this->assertEquals('UNKNOWN', $version);
    }
    
    public function testGetInfo() {
        $info = new Admin_Model_OpusInfo();
        
        $data = $info->getInfo();
        
        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('admin_info_version', $data);
        $this->assertEquals('UNKNOWN', $data['admin_info_version']);
    }    
    
}
