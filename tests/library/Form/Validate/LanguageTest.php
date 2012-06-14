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
 * @category    Application Unit Tests
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 *
 */
class Form_Validate_LanguageTest extends ControllerTestCase {

    public function testIsLanguageValidFalse() {
        $doc = new Opus_Document(146);

        $validator = new Form_Validate_Language(array('doc' => $doc,
            'fieldName' => 'TitleMain'));
        $this->assertFalse($validator->isValid('deu'), 'deu');
        $this->assertFalse($validator->isValid('eng'), 'eng');
    }

    public function testIsLanguageValidTrue() {
        $doc = new Opus_Document(146);

        $validator = new Form_Validate_Language(array('doc' => $doc,
            'fieldName' => 'TitleMain'));
        $this->assertTrue($validator->isValid('rus'), 'rus');
        $this->assertTrue($validator->isValid('fra'), 'fra');
    }

    public function testIsLanguageValidBadValue() {
        $doc = new Opus_Document(146);

        $validator = new Form_Validate_Language(array('doc' => $doc,
            'fieldName' => 'TitleMain'));
        $this->assertFalse($validator->isValid('123'));
        $this->assertTrue(in_array(Form_Validate_Language::NOT_VALID, $validator->getErrors()));
    }

    public function testIsLanguageValidDisabledLanguage() {
        $doc = new Opus_Document(146);

        $validator = new Form_Validate_Language(array('doc' => $doc,
            'fieldName' => 'TitleMain'));
        $this->assertFalse($validator->isValid('ita'));
        $this->assertTrue(in_array(Form_Validate_Language::NOT_ENABLED, $validator->getErrors()));
    }

    public function testIsLanguageValidTrueWithFieldFromContext() {
        $doc = new Opus_Document(146);

        $validator = new Form_Validate_Language(array('doc' => $doc));
        $this->assertTrue($validator->isValid('rus', array('Type' => 'main')));
        $this->assertTrue($validator->isValid('fra', array('Type' => 'main')));
    }

    public function testIsLanguageValidFalseWithFieldFromContext() {
        $doc = new Opus_Document(146);

        $validator = new Form_Validate_Language(array('doc' => $doc));
        $this->assertFalse($validator->isValid('deu', array('Type' => 'main')));
        $this->assertFalse($validator->isValid('eng', array('Type' => 'main')));
    }

}

