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

/**
 * Abstrakte Klasse fuer Unterformulare fuer Model Klassen.
 *
 * Diese Klassen aktualisieren Document nicht direkt, sondern geben das von ihnen angezeigte Model an das
 * übergeordnete Formular weiter. Dadurch kann Admin_Form_Document_MultiSubForm zum Beispiel die Modelle aller Patente
 * im Formular einsammeln und dann die Funktion setPatent verwenden, um das Feld in Document zu setzen.
 *
 * Die updateModel Funktionen in diesen Klassen erwarten nicht Document als Parameter, sondern das entsprechende
 * Model wie zum Beispiel Identifier oder Title.
 */
abstract class Admin_Form_AbstractModelSubForm extends Admin_Form_AbstractDocumentSubForm
{
    public function init()
    {
        parent::init();

        $this->setDecorators([
            'FormElements',
        ]);
    }

    /**
     * Liefert angezeigtes Model oder eine neue Instanz für gerade hinzugefügte Modelle.
     *
     * Wird zum Beispiel vom Formular ein existierender Identifier Eintrag angezeigt, sollte diese Funktion das Model
     * für den in der Datenbank gespeicherten Identifier zurück liefern. Ist der Identifier im Formular hinzugefügt
     * worden muss eine new Model Instanz zurück gegeben werden bei der der Wert vom ID-Feld noch null ist.
     *
     * @return mixed
     */
    abstract public function getModel();
}
