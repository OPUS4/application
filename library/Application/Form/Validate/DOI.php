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
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Application_Form_Validate_DOI extends Zend_Validate_Abstract {

    const NOT_UNIQUE = 'notUnique';

    const NOT_VALID = 'notValid';

    /**
     * Translation keys for validation messages.
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_UNIQUE => 'admin_validation_error_localdoi_not_unique',
        self::NOT_VALID => 'admin_validation_error_localdoi_invalid',
    );

    public function isValid($value, $context = null) {
        $currentDocId = $context[Admin_Form_Document_IdentifierSpecific::ELEMENT_DOC_ID];

        $doi = new Opus_Identifier();
        $doi->setType('doi');
        $doi->setValue($value);

        if (!$doi->isLocalDoi()) {
            return true; // keine Prüfung für nicht lokale-DOIs: nicht-lokale DOIs können ohne Prüfung gespeichert werden
        }

        if (!$doi->isDoiUnique($currentDocId)) {
            $this->_error(self::NOT_UNIQUE);
            return false; // Formular kann nicht gespeichert werden, weil eine lokale DOI eingegeben wurde, die bereits existiert
        }

        if (!$doi->isValidDoi()) {
            $this->_error(self::NOT_VALID);
            return false; // lokale DOI enthält unerlaubte Zeichen
        }

        return true; // DOI kann gespeichert werden

    }

}
