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
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Unterformular fuer die Titel eines Dokuments.
 *
 * Die verschiedenen Typen von Titeln werden in separaten Unterformularen angezeigt. Bei den Haupttiteln wird der Titel
 * in der Dokumentensprache zuerst angezeigt.
 *
 * Es darf nur einen Titel in der Dokumentensprache geben.
 *
 * Der Typ eines Titels kann später nicht mehr geändert werden. Die Felder fuer die verschiedenen Titeltypen setzen
 * den Wert vom Feld 'Type' eines Titels automatisch.
 */
class Admin_Form_Document_Titles extends Admin_Form_Document_Section
{
    /**
     * Initialisiert das Formular und erzeugt die Unterformulare für die Titeltypen.
     */
    public function init()
    {
        parent::init();

        $this->setLegend('admin_document_section_titles');

        $this->addSubForm(new Admin_Form_Document_TitlesMain(), 'Main');
        $this->addSubForm(
            new Admin_Form_Document_DefaultMultiSubForm(
                'Admin_Form_Document_Title',
                'TitleAdditional',
                new Application_Form_Validate_MultiSubForm_RepeatedLanguages()
            ),
            'Additional'
        );
        $this->addSubForm(
            new Admin_Form_Document_DefaultMultiSubForm(
                'Admin_Form_Document_Title',
                'TitleParent',
                new Application_Form_Validate_MultiSubForm_RepeatedLanguages()
            ),
            'Parent'
        );
        $this->addSubForm(
            new Admin_Form_Document_DefaultMultiSubForm(
                'Admin_Form_Document_Title',
                'TitleSub',
                new Application_Form_Validate_MultiSubForm_RepeatedLanguages()
            ),
            'Sub'
        );
    }
}
