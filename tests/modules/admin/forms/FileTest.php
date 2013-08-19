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
 * @category    Application Unit Test
 * @package     Admin_Form
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Admin_Form_FileTest extends ControllerTestCase {

    public function testConstructForm() {
        $form = new Admin_Form_File();
    }

    public function testPopulateFromModel() {
        $form = new Admin_Form_File();

        $file = new Opus_File(126); // hängt an Testdokument 146

        $form->populateFromModel($file);

        $this->assertEquals('test.pdf', $form->getElement('PathName')->getValue());
        $this->assertEquals(8817, $form->getElement('FileSize')->getValue());
        $this->assertEquals('deu', $form->getElement('Language')->getValue());
        $this->assertEquals('foo-pdf', $form->getElement('Label')->getValue());
        $this->assertEquals('foo-pdf file', $form->getElement('Comment')->getValue());

        $this->assertEquals(array('frontdoor', 'oai'), $form->getElement('VisibleIn')->getValue());

        $this->assertEquals(array('administrator', 'guest', 'reviewer'), $form->getElement('Roles')->getValue());

        // TODO hashes
    }

    public function testUpdateModel() {
    }

}