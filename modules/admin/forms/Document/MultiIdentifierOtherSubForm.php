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
 * @package     Module_Admin
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Admin_Form_Document_MultiIdentifierOtherSubForm extends Admin_Form_Document_MultiSubForm {

    public function init() {
        parent::init();
        $this->setLegend('admin_document_section_identifier_other');
    }

    public function getFieldValues($document) {
        $value = parent::getFieldValues($document);
        if (!is_null($value)) {
            $value = $this->filterIdentifier($value);
        }
        return $value;
    }

    /**
     * Identifier vom Typ DOI und URN werden separat behandelt und müssen daher bei der allgemeinen
     * Behandlung der Identifier ausgeschlossen werden, da sie sonst doppelt angezeigt werden
     *
     * @param $identifiers
     * @return array
     */
    private function filterIdentifier($identifiers) {
        $result = array();
        foreach ($identifiers as $identifier) {
            if ($identifier->getType() == 'doi' || $identifier->getType() == 'urn') {
                continue;
            }
            $result[] = $identifier;
        }
        return $result;
    }

    /**
     * Spezialbehandlung für Identifier erforderlich (da Spezialbehandlung für DOIs und URNs)
     * wir dürfen hier nicht die setIdentifier-Methode direkt verwenden, sonst löschen wir DOIs/URNs
     * die mit dem Dokument verknüpft sind
     *
     * @param Opus_Document $document
     */
    public function updateModel($document) {
        $values = $this->getSubFormModels($document);
        $identifiers = $document->getIdentifier();

        $result = array();
        foreach ($identifiers as $identifier) {
            $identifierType = $identifier->getType();
            if ($identifierType == 'doi' || $identifierType == 'urn') {
                $result[] = $identifier;
            }
        }
        $result = array_merge($result, $values);
        $document->setIdentifier($result);
    }
}