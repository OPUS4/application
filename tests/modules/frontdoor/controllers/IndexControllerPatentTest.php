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
 * @package     Tests
 * @author      Michael Lang <lang@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */


class Frontdoor_IndexControllerPatentTest extends ControllerTestCase {

    public function testPatentInformationGerman() {
        $this->useGerman();

        $this->dispatch("/frontdoor/index/index/docId/146");
        $this->assertQueryContentContains('//th', 'Patentnummer:');
        $this->assertQueryContentContains('//tr', '1234');
        $this->assertQueryContentContains('//th', 'Land der Patentanmeldung:');
        $this->assertQueryContentContains('//tr', 'DDR');
        $this->assertQueryContentContains('//th', 'Jahr der Patentanmeldung:');
        $this->assertQueryContentContains('//tr', '1970');
        $this->assertQueryContentContains('//th', 'Patentanmeldung:');
        $this->assertQueryContentContains('//tr', 'The foo machine.');
        $this->assertQueryContentContains('//th', 'Datum der Patenterteilung:');
        $this->assertQueryContentContains('//tr', '01.01.1970');
    }
    
    public function testPatentInformationEnglish() {
        $this->useEnglish();

        $this->dispatch("/frontdoor/index/index/docId/146");

        $this->assertQueryContentContains('//th', 'Patent Number:');
        $this->assertQueryContentContains('//tr', '1234');
        $this->assertQueryContentContains('//th', 'Country of Patent Application:');
        $this->assertQueryContentContains('//tr', 'DDR');
        $this->assertQueryContentContains('//th', 'Patent Application Year:');
        $this->assertQueryContentContains('//tr', '1970');
        $this->assertQueryContentContains('//th', 'Patent Application:');
        $this->assertQueryContentContains('//tr', 'The foo machine.');
        $this->assertQueryContentContains('//th', 'Patent Grant Date:');
        $this->assertQueryContentContains('//tr', '1970/01/01');
    }

    public function testPatentInformationMultiple() {
        $this->markTestSkipped("Document 200 ist für Löschtests, daher fehlt das zweite Patent unter Umständen.");
        $this->useEnglish();

        $this->dispatch("/frontdoor/index/index/docId/200");

        $this->assertQueryContentContains('//th', 'Patent Number:');
        $this->assertQueryContentContains('//tr', '1234');
        $this->assertQueryContentContains('//tr', '4321');
        $this->assertQueryContentContains('//th', 'Country of Patent Application:');
        $this->assertQueryContentContains('//tr', 'DDR');
        $this->assertQueryContentContains('//tr', 'BRD');
        $this->assertQueryContentContains('//th', 'Patent Application Year:');
        $this->assertQueryContentContains('//tr', '1970');
        $this->assertQueryContentContains('//tr', '1972');
        $this->assertQueryContentContains('//th', 'Patent Application:');
        $this->assertQueryContentContains('//tr', 'The foo machine.');
        $this->assertQueryContentContains('//tr', 'The bar machine.');
        $this->assertQueryContentContains('//th', 'Patent Grant Date:');
        $this->assertQueryContentContains('//tr', '1970/01/01');
        $this->assertQueryContentContains('//tr', '1972/01/01');
    }

    public function testRegression3118() {
        $this->useEnglish();
        $this->enableSecurity();
        $this->loginUser('admin', 'adminadmin');

        $this->dispatch('/frontdoor/index/index/docId/146');

        $this->assertNotQueryContentContains('//dl[@id="Document-ServerState"]//li[@class="active"]', 'Publish document');
        $this->assertQueryContentContains('//dl[@id="Document-ServerState"]//li[@class="active"]', 'Published');
    }
    
    /**
     * Regression Tests for OPUSVIER-2813
     */

    public function testDateFormatGerman() {
        $this->useGerman();

        $this->dispatch("/frontdoor/index/index/docId/91");
        $this->assertQueryContentContains('//th', 'Datum der Abschlussprüfung');
        $this->assertQueryContentContains('//tr', '26.02.2010');
        $this->assertQueryContentContains('//th', 'Datum der Freischaltung');
        $this->assertQueryContentContains('//tr', '05.03.2010');
    }

    public function testDateFormatEnglish() {
        $this->useEnglish();
        
        $this->dispatch("/frontdoor/index/index/docId/91");
        $this->assertQueryContentContains('//th', 'Date of final exam');
        $this->assertQueryContentContains('//tr', '2010/02/26');
        $this->assertQueryContentContains('//th', 'Release Date');
        $this->assertQueryContentContains('//tr', '2010/03/05');
    }

}
 