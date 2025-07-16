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
 * @copyright   Copyright (c) 2025, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\DocumentInterface;

/**
 * Adds ADD button to MultiSubForm.
 */
class Admin_Form_Document_DefaultMultiSubForm extends Admin_Form_Document_MultiSubForm
{
    // Name von Button zum Hinzufügen eines Unterformulars (z.B. Enrichment).
    public const ELEMENT_ADD = 'Add';

    /**
     * Konstruiert Instanz von Fomular.
     *
     * @param string                                               $subFormClass Name der Klasse für Unterformulare
     * @param string                                               $fieldName Name des Document Feldes, das angezeigt werden soll
     * @param Application_Form_Validate_MultiSubFormInterface|null $validator Object für Validierungen über Unterformulare hinweg
     * @param array|null                                           $options
     */
    public function __construct($subFormClass, $fieldName, $validator = null, $options = null)
    {
        parent::__construct($subFormClass, $fieldName, $validator, $options);
    }

    public function init()
    {
        parent::init();

        $this->initButton();

        $this->setLegend('admin_document_section_' . strtolower($this->fieldName)); // TODO use getter

        if ($this->getColumns() !== null) {
            $this->renderAsTableEnabled = true;
            $this->setDecorators(
                [
                    'FormElements', // Zend decorator
                    'TableHeader',
                    'TableWrapper',
                    [
                        ['fieldsWrapper' => 'HtmlTag'],
                        ['tag' => 'div', 'class' => 'fields-wrapper'],
                    ],
                    [
                        'FieldsetWithButtons',
                        ['legendButtons' => self::ELEMENT_ADD],
                    ],
                    [
                        ['divWrapper' => 'HtmlTag'],
                        ['tag' => 'div', 'class' => 'subform'],
                    ],
                ]
            );
        } else {
            $this->getDecorator('FieldsetWithButtons')->setLegendButtons(self::ELEMENT_ADD);
        }

        $this->getElement(self::ELEMENT_ADD)->setDecorators([])->setDisableLoadDefaultDecorators(true);
    }

    protected function initButton()
    {
        $this->addElement('submit', self::ELEMENT_ADD, [
            'order' => 1000,
            'label' => 'admin_button_add',
        ]);
    }

    /**
     * @param DocumentInterface $document
     */
    public function populateFromModel($document)
    {
        parent::populateFromModel($document);

        $maxIndex = count($this->getSubForms());

        // Sicherstellen, daß Button zum Hinzufügen zuletzt angezeigt wird
        $this->getElement(self::ELEMENT_ADD)->setOrder($maxIndex + 1);
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
            return $this->processPostAdd();
        }

        return parent::processPost($data, $context);
    }

    /**
     * @return string
     */
    protected function processPostAdd()
    {
        $subform = $this->appendSubForm();
        $this->addAnchor($subform);
        return Admin_Form_Document::RESULT_SHOW;
    }
}
