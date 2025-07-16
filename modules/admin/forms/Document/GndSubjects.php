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
    public const ELEMENT_SUBJECTS     = 'Subjects';
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
        $this->setSubjectType('swd');

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

        $this->addElement('textarea', self::ELEMENT_SUBJECTS);
        $this->addElement('submit', self::ELEMENT_ADD_SUBJECTS, [
            'label' => 'admin_button_add',
        ]);

        $this->valuesSubForm = new Admin_Form_Document_SubjectMultiSubForm(
            $this->getSubjectType(),
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
     * @param string $type
     * @return $this
     */
    public function setSubjectType($type)
    {
        $this->subjectType = $type;
        return $this;
    }

    /**
     * @param DocumentInterface $document
     */
    public function populateFromModel($document)
    {
        $this->valuesSubForm->populateFromModel($document);
    }

    /**
     * @param array                  $post
     * @param DocumentInterface|null $document
     */
    public function constructFromPost($post, $document = null)
    {
        // TODO is this the best way $post['Values'] - Shouldn't base class take care of it?
        if (isset($post['Values'])) {
            $this->valuesSubForm->constructFromPost($post['Values'], $document);
        }
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
        } elseif (
            array_key_exists(self::ELEMENT_ADD_SUBJECTS, $data)
            && array_key_exists(self::ELEMENT_SUBJECTS, $data)
        ) {
            $this->addMultipleSubjectsFromString($data[self::ELEMENT_SUBJECTS]);
            return Admin_Form_Document::RESULT_SHOW;
        }

        return parent::processPost($data, $context);
    }

    /**
     * @param string $value
     */
    public function addMultipleSubjectsFromString($value)
    {
        if (strlen(trim($value)) > 0) {
            $subjects = $this->explodeSubjectsString($value);

            $existingSubjects = [];

            $models = $this->valuesSubForm->getSubFormModels();
            foreach ($models as $model) {
                $existingSubjects[] = $model->getValue();
            }

            foreach ($subjects as $subject) {
                $subject = trim($subject);
                if (strlen($subject) > 0 && ! in_array($subject, $existingSubjects)) {
                    $subform = $this->valuesSubForm->appendSubForm();
                    $subform->getElement('Value')->setValue($subject);
                }
            }

            // Jump to new subject elements
            $subform->addDecorator(
                ['currentAnchor' => 'HtmlTag'],
                ['tag' => 'a', 'placement' => 'prepend', 'name' => 'current']
            );

            // Clear input textarea
            $this->getElement(self::ELEMENT_SUBJECTS)->setValue(null);
        }
    }

    /**
     * Breaks string into separate subjects at commas or line breaks.
     *
     * TODO handle strings with uneven number of quotes properly
     *
     * @param string $text
     * @return string[]
     */
    public function explodeSubjectsString($text)
    {
        $regex    = '/,(?=(?:[^"]*"[^"]*")*[^"]*$)/';
        $text     = preg_replace($regex, PHP_EOL, $text);
        $subjects = preg_split('/(\r\n|\n|\r)/', $text, -1, PREG_SPLIT_NO_EMPTY);
        return array_map(function ($value) {
            return trim($value, ' "');
        }, $subjects);
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
