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
 * @category    Application
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Checks if a number already exists in a series.
 *
 * TODO Basisklasse mit setLogger verwenden
 */
class Application_Form_Validate_SeriesNumberAvailable extends Zend_Validate_Abstract
{

    /**
     * Constant for number is not available anymore message.
     */
    const NOT_AVAILABLE = 'notAvailable';

    /**
     * Error messages.
     */
    protected $_messageTemplates = [
        self::NOT_AVAILABLE => 'admin_series_error_number_exists'
    ];

    /**
     * Prüft, ob eine Nummer für eine Schriftenreihe bereits vergeben ist.
     *
     * Wenn die Nummer bereits vergeben ist, wird geprüft, ob es sich um das aktuelle Dokument handelt. In diesem Fall
     * ist die Validierung ebenfalls erfolgreich.
     *
     * Wenn die Series nicht gefunden werden kann soll die Validierung einfach ignoriert werden, da nicht festgestellt
     * werden kann, ob es eine Kollision gibt. Eine fehlende Series-ID im Formular muss woanders geprüft und gemeldet
     * werden.
     */
    public function isValid($value, $context = null)
    {
        $value = (string) $value;
        $this->_setValue($value);

        if (array_key_exists(Admin_Form_Document_Series::ELEMENT_SERIES_ID, $context)) {
            $seriesId = $context[Admin_Form_Document_Series::ELEMENT_SERIES_ID];
        } else {
            $seriesId = null;
        }

        if (strlen(trim($seriesId)) == 0 && is_numeric($seriesId)) {
            Zend_Registry::get('Zend_Log')->err(__METHOD__ . ' Context without \'SeriesId\'.');
            return true; // should be captured somewhere else
        }

        try {
            $series = new Opus_Series($seriesId);
        } catch (Opus_Model_NotFoundException $omnfe) {
            Zend_Registry::get('Zend_Log')->err(__METHOD__ . $omnfe->getMessage());
            return true;
        }

        if (! $series->isNumberAvailable($value)) {
            if (array_key_exists(Admin_Form_Document_Series::ELEMENT_DOC_ID, $context)) {
                $currentDocId = $context[Admin_Form_Document_Series::ELEMENT_DOC_ID];
                $otherDocId = $series->getDocumentIdForNumber($value);

                if ($currentDocId == $otherDocId) {
                    return true;
                }
            }

            $this->_error(self::NOT_AVAILABLE);
            return false;
        }

        return true;
    }
}
