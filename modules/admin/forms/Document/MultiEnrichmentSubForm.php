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

/**
 * Form for editing enrichments.
 *
 * This form filters enrichments, so that some values cannot be edited directly.
 *
 * TODO rename to Enrichments
 * TODO generic mechanism for excluding enrichments from editing
 * TODO use custom elements/subforms for different enrichment types
 */
class Admin_Form_Document_MultiEnrichmentSubForm extends Admin_Form_Document_MultiSubForm {

    public function getFieldValues($document) {
        $value = parent::getFieldValues($document);
        if (!is_null($value)) {
            $value = $this->filterEnrichments($value);
        }
        return $value;
    }

    /**
     * Besondere Behandlung der beiden AutoCreate-Enrichments für DOIs und URNs
     * diese Enrichments sollen indirekt über Checkboxen im Abschnitt DOI / URN verwaltet werden und nicht bei den
     * herkömmlichen Enrichments angezeigt werden (somit werden auch konfligierende Eintragungen zwischen Enrichment-
     * Wert und Checkbox-Zustand vermieden)
     *
     * @param $enrichments
     * @return array
     */
    private function filterEnrichments($enrichments) {
        $result = array();
        foreach ($enrichments as $enrichment) {
            $keyName = $enrichment->getKeyName();
            if ($keyName == 'opus.doi.autoCreate' || $keyName == 'opus.urn.autoCreate') {
                continue;
            }
            $result[] = $enrichment;
        }
        return $result;
    }
}

