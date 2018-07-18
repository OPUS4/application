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
 * @author      Michael Lang <lang@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 *
 * @covers Admin_DoctypeController
 */
class Admin_DoctypeControllerTest extends ControllerTestCase {

    /**
     * Ruft die Dokumenttyp-Validierungsseite auf und prüft ob diese korrekt angezeigt wird.
     */
    public function testDoctypePage() {
        $this->useGerman();
        $this->dispatch('/admin/doctype/index');
        $this->assertResponseCode(200);
        $this->assertQuery('//a[@href="doctype/show/doctype/demo_invalid"]');
        $this->assertQueryContentContains('//div',
            'Die Validierung der rot-markierten Dokumententypen ist fehlgeschlagen.');
        $this->assertQueryContentContains('//th', 'Artikel');
        $this->assertQueryContentContains('//td', 'article');
        $this->assertQueryContentContains('//td', 'aktiv');

        $this->assertQueryContentContains('//td.invisible', 'book'); // Book ist deaktiviert
    }

    /**
     * Ruft die Fehlerseite für einzelne Dokumenttypen auf und prüft ob diese korrekt angezeigt wird.
     */
    public function testDoctypeErrorMessagePage() {
        $this->useEnglish();
        $this->dispatch('/admin/doctype/show/doctype/demo_invalid');
        $this->assertResponseCode(200);
        $this->assertQueryContentContains('//h2', 'Error Message of Document Type demo_invalid:');
    }

    /**
     * Prüft, ob die Breadcrumbs auf dieser Seite korrekt angezeigt werden.
     */
    public function testBreadcrumbs() {
        $this->dispatch('/admin/doctype/show/doctype/demo_invalid');
        $this->assertQueryContentContains('//div[class="breadcrumbsContainer"]', 'demo_invalid');
    }

    /**
     * Wenn nach der Fehlermeldung eines nicht existenten Dokumententyps gesucht wird, soll ein Redirect zur Übersicht
     * stattfinden und eine Fehlermeldung ausgegeben werden.
     */
    public function testInvalidDocumentTypeRedirect() {
        $this->useEnglish();
        $this->dispatch('/admin/doctype/show/doctype/yoyo');
        $this->assertRedirectTo('/admin/doctype');
        $this->verifyFlashMessage('admin_doctype_invalid');
    }

    public function testMissingDocumentTypeRedirect() {
        $this->useEnglish();
        $this->dispatch('/admin/doctype/show');
        $this->assertRedirectTo('/admin/doctype');
        $this->verifyFlashMessage('admin_doctype_invalid');
    }

}
 