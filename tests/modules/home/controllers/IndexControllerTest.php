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
 * along with OPUS; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * @category    TODO
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Basic unit test for inded controller of home module.
 */
class Home_IndexControllerTest extends ControllerTestCase {

    /**
     * Tests routing to and successfull execution of 'index' action.
     */
    public function testIndexAction() {
        $this->dispatch('/home');
        $this->assertResponseCode(200);
        $this->assertModule('home');
        $this->assertController('index');
        $this->assertAction('index');
    }

    /**
     * Tests setting language for session.
     */
    public function testLanguageAction() {
        $this->markTestIncomplete('How can this be tested?');
        $this->dispatch('/home/index/language/language/de');
        $this->assertRedirect();
    }

    /**
     * Test about action.
     */
    public function testAboutAction() {
        $this->dispatch('/home/index/about');
        $this->assertResponseCode(200);
        $this->assertModule('home');
        $this->assertController('index');
        $this->assertAction('about');
    }

    /**
     * Test help action.
     */
    public function testHelpAction() {
        $this->dispatch('/home/index/help');
        $this->assertResponseCode(200);
        $this->assertModule('home');
        $this->assertController('index');
        $this->assertAction('help');
    }

}

?>
