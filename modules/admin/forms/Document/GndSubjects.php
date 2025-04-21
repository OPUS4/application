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

use Opus\Common\DocumentInterface;

/**
 * Unterformular fuer GND Subjects im Metadaten-Formular.
 */
class Admin_Form_Document_GndSubjects extends Admin_Form_AbstractDocumentSubForm
{
    public const ELEMENT_ADD          = 'Add';
    public const ELEMENT_ADD_SUBJECTS = 'AddSubjects';
    public const SUBFORM_VALUES       = 'Values';

    /** @var string Der Schlagworttyp für den dieses Unterformular verwendet wird. */
    private $subjectType; // TODO necessary here?

    /** @var Admin_Form_Document_MultiSubForm */
    private $valuesSubForm;

    /**
     * Konstruiert ein Unterformular für GND Schlagwörter.
     *
     * @param null|mixed $options
     */
    public function __construct($options = null)
    {
        $this->subjectType = 'swd';

        parent::__construct($options);
    }

    /**
     * Initialisiert die Formularelemente.
     *
     * Setzt die Legende für das Unterformular.
     */
    public function init()
    {
        parent::init();

        $this->setLegend('admin_document_section_subject' . $this->subjectType);

        $this->addElement('submit', self::ELEMENT_ADD, [
            'order' => 1000,
            'label' => 'admin_button_add',
        ]);

        $this->valuesSubForm = new Admin_Form_Document_SubjectMultiSubForm(
            'swd',
            [
                'columns' => [
                    [],
                    ['label' => 'Opus_Subject_Value'],
                    ['label' => 'ExternalKey'],
                ],
            ]
        );

        $this->addSubForm($this->valuesSubForm, self::SUBFORM_VALUES);

        $this->getDecorator('FieldsetWithButtons')->setLegendButtons(self::ELEMENT_ADD);
        $this->getElement(self::ELEMENT_ADD)->setDecorators([])->setDisableLoadDefaultDecorators(true);
    }

    /**
     * Liefert den Schlagworttyp für das Formular zurück.
     *
     * @return string Schlagworttyp
     */
    public function getSubjectType()
    {
        return $this->subjectType;
    }

    /**
     * @param DocumentInterface $document
     */
    public function populateFromModel($document)
    {
        $this->valuesSubForm->populateFromModel($document);
    }

    /**
     * @param array $post
     * @param DocumentInterface $document
     */
    public function constructFromPost($post, $document = null)
    {
        // TODO is this the best way $post['Values'] - Shouldn't base class take care of it?
        $this->valuesSubForm->constructFromPost($post['Values'], $document);
    }

    /**
     * @param DocumentInterface $document
     */
    public function updateModel($document)
    {
        // hier darf nichts passieren
    }

    /**
     * Liefert die Schlagwoerter mit dem richtigen Typ.
     *
     * @param DocumentInterface $document
     * @return array
     */
    public function getFieldValues($document)
    {
        return $this->valuesSubForm->getFieldValues($document);
    }

    /**
     * @param array $data
     * @param array $context
     * @return string|null
     */
    public function processPost($data, $context)
    {
        // Prüfen ob "Hinzufügen" geklickt wurde
        if (array_key_exists(self::ELEMENT_ADD, $data)) {
            $subform = $this->valuesSubForm->appendSubForm();
            $subform->addDecorator(
                ['currentAnchor' => 'HtmlTag'],
                ['tag' => 'a', 'placement' => 'prepend', 'name' => 'current']
            );
            return Admin_Form_Document::RESULT_SHOW;
        }

        return parent::processPost($data, $context);
    }

    /**
     * @param DocumentInterface|null $document
     * @return array
     */
    public function getSubFormModels($document = null)
    {
        return $this->valuesSubForm->getSubFormModels($document);
    }
}
