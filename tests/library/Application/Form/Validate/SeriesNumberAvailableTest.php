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

class Application_Form_Validate_SeriesNumberAvailableTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database', 'translation'];

    public function testIsValidTrue()
    {
        $validator = new Application_Form_Validate_SeriesNumberAvailable();

        $context = [
            'SeriesId' => '1',
        ];

        $this->assertTrue($validator->isValid('10/10', $context));
    }

    public function testIsValidFalse()
    {
        $validator = new Application_Form_Validate_SeriesNumberAvailable();

        $context = [
            'SeriesId' => '1', // mit Dokument 146 (number = '5/5') verknuepft
        ];

        $this->assertFalse($validator->isValid('5/5', $context));
    }

    /**
     * Wenn bereits existierende Nummer, die des aktuellen Dokuments ist, dann soll die
     * Validierung erfolgreich sein. Andernfalls würde es zu Fehlern beim Abspeichern eines jeden
     * Dokuments kommen, wenn die Number gleich geblieben ist.
     */
    public function testIsValidTrueForThisDocument()
    {
        $validator = new Application_Form_Validate_SeriesNumberAvailable();

        $context = [
            'Id'       => 146,
            'SeriesId' => '1', // mit Dokument 146 (number = '5/5') verknuepft
        ];

        $this->assertTrue($validator->isValid('5/5', $context));
    }

    public function testMessagesTranslated()
    {
        $translator = Application_Translate::getInstance();

        $this->assertTrue($translator->isTranslated('admin_series_error_number_exists'));
    }

    public function testIsValidTrueForMissingSeriesId()
    {
        $validator = new Application_Form_Validate_SeriesNumberAvailable();

        $context = [];

        $this->assertTrue($validator->isValid('5/5', $context));
    }

    public function testIsValidTrueForUnknownSeriesId()
    {
        $validator = new Application_Form_Validate_SeriesNumberAvailable();

        $context = [
            'SeriesId' => 300,
        ];

        $this->assertTrue($validator->isValid('5/5', $context));
    }

    public function testIsValidTrueForBadSeriesId()
    {
        $validator = new Application_Form_Validate_SeriesNumberAvailable();

        $context = [
            'SeriesId' => 'bla',
        ];

        $this->assertTrue($validator->isValid('5/5', $context));
    }
}
