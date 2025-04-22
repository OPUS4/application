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
 * Unterformular fuer Haupttitel eines Dokuments.
 *
 * Die Basisklasse wurde erweitert um dafür zu sorgen, dass der Titel in der Dokumentensprache zuerst angezeigt wird.
 * Außerdem wird zusätzlich bei der Validierung geprüft, ob ein Titel in der Dokumentsprache existiert.
 */
class Admin_Form_Document_TitlesMain extends Admin_Form_Document_DefaultMultiSubForm
{
    /**
     * Konstruiert Unterformular fuer die Haupttitel eines Dokuments.
     */
    public function __construct()
    {
        parent::__construct(
            'Admin_Form_Document_Title',
            'TitleMain',
            new Application_Form_Validate_MultiSubForm_RepeatedLanguages()
        );
    }

    public function init()
    {
        parent::init();
        $this->setDecorators(
            [
                'FormElements',
                [['fieldsWrapper' => 'HtmlTag'], ['tag' => 'div', 'class' => 'fields-wrapper']],
                [
                    'FormErrors',
                    [
                        'placement'            => 'prepend',
                        'ignoreSubForms'       => true,
                        'onlyCustomFormErrors' => true,
                        'markupListStart'      => '<div class="form-errors">',
                        'markupListItemStart'  => '',
                        'markupListItemEnd'    => '',
                        'markupListEnd'        => '</div>',
                    ],
                ],
                ['FieldsetWithButtons', ['legendButtons' => self::ELEMENT_ADD]],
                [['divWrapper' => 'HtmlTag'], ['tag' => 'div', 'class' => 'subform']],
            ]
        );
    }

    /**
     * Prüft Abhängigkeiten zu anderen Unterformularen.
     *
     * Es wird geprüft, ob ein Titel in der Sprache des Dokuments vorhanden ist. Das ist technisch notwendig für die
     * Indizierung und für die Anzeige an vielen Stellen.
     *
     * @param array $data
     * @param array $globalContext Daten für das gesamte Metadaten-Formular
     * @return bool true - wenn keine Abhängigkeiten verletzt wurden
     */
    public function isDependenciesValid($data, $globalContext)
    {
        $result = parent::isDependenciesValid($data, $globalContext);

        $language = $globalContext['General']['Language']; // TODO kann das dynamisch ermittelt werden

        $validator = new Application_Form_Validate_ValuePresentInSubforms('Language');

        if (! $validator->isValid($language, $data)) {
            $translator = $this->getTranslator();
            $this->addErrorMessage(
                vsprintf(
                    $translator->translate('admin_document_error_NoTitleInDocumentLanguage'),
                    [$translator->translate($language)]
                )
            );

            $result = false;
        }
        return $result;
    }

    /**
     * Liefert Array mit Haupttiteln des Dokuments.
     *
     * Sorgt dafuer, dass der Titel in der Dokumentensprache zuerst im Array steht.
     *
     * @param DocumentInterface $document
     * @return array
     */
    public function getFieldValues($document)
    {
        $values = parent::getFieldValues($document);

        $doclang = $document->getLanguage();

        $sortedValues = [];

        foreach ($values as $index => $value) {
            if ($value->getLanguage() === $doclang) {
                $sortedValues[] = $value;
                unset($values[$index]);
                break;
            }
        }

        return array_merge($sortedValues, $values);
    }
}
