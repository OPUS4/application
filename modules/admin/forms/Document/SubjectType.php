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
 * @package     Module_Admin
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Unterformular fuer Subjects eines bestimmten Typs im Metadaten-Formular.
 *
 * Diese Klasse überschreibt ein paar Funktion von Admin_Form_Document_MultiSubForm um Unterformulare vom richtigen Typ
 * zu verwenden und die richtigen Werte aus dem Modell zu holen.
 *
 * @category    Application
 * @package     Module_Admin
 */
class Admin_Form_Document_SubjectType extends Admin_Form_Document_MultiSubForm
{

    /**
     * Der Schlagworttyp für den dieses Unterformular verwendet wird.
     * @var string
     */
    private $_subjectType;

    /**
     * Konstruiert ein Unterformular für Schlagwörter eines bestimmten Typs.
     * @param string $type Schlagworttyp (z.B. 'swd', 'psyndex' usw.)
     * @param mixed $options
     */
    public function __construct($type, $options = null)
    {
        $this->_subjectType = $type;

        $validator = null;

        switch ($type) {
            case 'swd':
                $validator = new Application_Form_Validate_MultiSubForm_RepeatedValues(
                    'Value',
                    'admin_document_error_repeated_subject'
                );
                break;
            default:
                $validator = new Application_Form_Validate_MultiSubForm_RepeatedValues(
                    'Value',
                    'admin_document_error_repeated_subject',
                    'Language'
                );
                break;
        }

        parent::__construct(null, 'Subject', $validator, $options);
    }

    /**
     * Initialisiert die Formularelemente.
     *
     * Setzt die Legende für das Unterformular.
     */
    public function init()
    {
        parent::init();

        $this->setLegend('admin_document_section_subject' . $this->_subjectType);
    }

    /**
     * Liefert den Schlagworttyp für das Formular zurück.
     * @return string Schlagworttyp
     */
    public function getSubjectType()
    {
        return $this->_subjectType;
    }

    /**
     * Ueberschreibt Funktion damit hier nichts passiert.
     *
     * In der Klasse Admin_Form_Document_MultiSubForm wird in dieser Funktion das Dokument aktualisiert, was aber bei
     * Schlagwoertern nicht passieren soll, da die Werte aus mehreren MultiSubForm-Formularen zusammengesammelt werden
     * muessen.
     *
     * @param Opus_Document $document
     */
    public function updateModel($document)
    {
        // hier darf nichts passieren
    }

    /**
     * Erzeugt neues Unterformular Instanz fuer den entsprechenden Schlagworttyp.
     * @return \Admin_Form_Document_Subject
     */
    public function createNewSubFormInstance()
    {
        if ($this->_subjectType == 'swd') {
            return new Admin_Form_Document_Subject('swd', 'deu');
        } else {
            return new Admin_Form_Document_Subject($this->_subjectType);
        }
    }

    /**
     * Liefert die Schlagwoerter mit dem richtigen Typ.
     *
     * @param Opus_Document $document
     * @return array
     */
    public function getFieldValues($document)
    {
        $values = parent::getFieldValues($document);

        $subjects = [];

        foreach ($values as $value) {
            if ($value->getType() == $this->_subjectType) {
                $subjects[] = $value;
            }
        }

        return $subjects;
    }
}
