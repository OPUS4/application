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

use Opus\Common\DocumentInterface;

/**
 * Formular fuer Metadaten eines Dokuments.
 */
class Admin_Form_Document extends Admin_Form_AbstractDocumentSubForm
{
    /**
     * Ergebnis wenn keine weiteren Aktionen ausgeführt werden müssen.
     *
     * Unterformulare, die einen POST erfolgreich abgearbeitet haben, zum Beispiel ein Unterformular entfernt oder
     * hinzugefügt haben melden dieses Signal, um zu zeigen, daß das Formular wieder ausgegeben werden kann.
     */
    public const RESULT_SHOW = 'show';

    /**
     * Ergebnis von Unterformular, wenn die angezeigte Seite gewechselt werden soll.
     *
     * Unterformulare, die Aufgrund des POST möchten, daß zu einer anderen Seite gewechselt wird schicken, dieses
     * Ergebnis zusammen mit den notwendigen Informationen für den Seitenwechsel.
     */
    public const RESULT_SWITCH_TO = 'switch';

    /**
     * POST Ergebnis für Klick auf Speichern-Button.
     *
     * Es gibt zwei Buttons in den Unterformularen 'ActionBox' und 'Actions'. Bei beiden liefert processPost dieses
     * Ergebnis zurück, wenn auf 'Speichern' geklickt wurde.
     */
    public const RESULT_SAVE = 'save';

    /**
     * POST Ergebnis für Klick auf Abbrechen-Button.
     *
     * Es gibt zwei Buttons in den Unterformularen 'ActionBox' und 'Actions'. Bei beiden liefert processPost dieses
     * Ergebnis zurück, wenn auf 'Abbrechen' geklickt wurde.
     */
    public const RESULT_CANCEL = 'cancel';

    /**
     * POST Ergebnis für das Abspeichern und weiter editieren des selben Dokuments.
     *
     * TODO Button wird zur Zeit nicht angezeigt (Designentscheidung)
     */
    public const RESULT_SAVE_AND_CONTINUE = 'saveAndContinue';

    /**
     * Globale Nachricht für das Formular.
     *
     * Wird in der ActionBox angezeigt und wird zum Beispiel bei Validierungsfehlern für eine allgemeine Nachricht
     * eingesetzt.
     *
     * @var string
     */
    private $message;

    /** @var DocumentInterface */
    private $document;

    /**
     * Konstruiert das Metadaten-Formular aus verschiedenen Unterformularen und den Aktion Buttons.
     */
    public function init()
    {
        parent::init();

        $this->setDecorators([
            'FormElements',
            [
                ['wrapperDivClose' => 'HtmlTag'],
                ['tag' => 'div', 'closeOnly' => 'true', 'placement' => 'append'],
            ],
        ]);

        $this->addSubForm(new Admin_Form_ActionBox($this), 'ActionBox');

        $subform = new Admin_Form_InfoBox();
        $subform->addDecorator(
            ['wrapperDivOpen' => 'HtmlTag'],
            ['tag' => 'div', 'placement' => 'prepend', 'class' => 'wrapper', 'openOnly' => 'true']
        );
        $this->addSubForm($subform, 'InfoBox');

        $this->addSubForm(new Admin_Form_Document_General(), 'General');

        $this->addSubForm(new Admin_Form_Document_Persons(), 'Persons');

        // Bibliographische Beschreibung
        $this->addSubForm(new Admin_Form_Document_Titles(), 'Titles');
        $this->addSubForm(new Admin_Form_Document_Bibliographic(), 'Bibliographic');
        $this->addSubForm(
            new Admin_Form_Document_DefaultMultiSubForm(
                'Admin_Form_Document_Series',
                'Series',
                new Application_Form_Validate_MultiSubForm_RepeatedValues(
                    'SeriesId',
                    'admin_document_error_repeated_series'
                ),
                [
                    'columns' => [
                        [],
                        ['label' => 'Opus_Model_Dependent_Link_DocumentSeries_Number'],
                        ['label' => 'Opus_Model_Dependent_Link_DocumentSeries_SortOrder'],
                    ],
                ]
            ),
            'Series'
        );

        $this->addSubForm(
            new Admin_Form_Document_MultiEnrichmentSubForm(
                'Admin_Form_Document_Enrichment',
                'Enrichment',
                null,
                [
                    'columns' => [
                        ['label' => 'KeyName'],
                        ['label' => 'Value'],
                    ],
                ]
            ),
            'Enrichments'
        );

        $this->addSubForm(new Admin_Form_Document_Collections(), 'Collections');

        // Inhaltliche Erschließung
        $subform = new Admin_Form_Document_Section();
        $subform->setLegend('admin_document_section_content');
        $subform->addSubForm(
            new Admin_Form_Document_DefaultMultiSubForm(
                'Admin_Form_Document_Abstract',
                'TitleAbstract',
                new Application_Form_Validate_MultiSubForm_RepeatedValues(
                    'Language',
                    'admin_document_error_MoreThanOneTitleInLanguage'
                )
            ),
            'Abstracts'
        );
        $subform->addSubForm(new Admin_Form_Document_Subjects(), 'Subjects');
        $this->addSubForm($subform, 'Content');

        // Weiteres Allgemeines
        $this->addSubForm(new Admin_Form_Document_Identifiers(), 'IdentifiersAll');
        $this->addSubForm(new Admin_Form_Document_Licences(), 'Licences');
        $this->addSubForm(new Admin_Form_Document_DefaultMultiSubForm('Admin_Form_Document_Patent', 'Patent'), 'Patents');
        $this->addSubForm(new Admin_Form_Document_DefaultMultiSubForm('Admin_Form_Document_Note', 'Note'), 'Notes');

        $this->addSubForm(new Admin_Form_Document_Actions(), 'Actions');
    }

    /**
     * Populates form from model values.
     *
     * @param DocumentInterface $document
     */
    public function populateFromModel($document)
    {
        $this->document = $document;

        $subforms = $this->getSubForms();

        foreach ($subforms as $form) {
            $form->populateFromModel($document);
        }
    }

    /**
     * Konstruiert Formular mit Unterformularen basierend auf POST Daten.
     *
     * @param array                  $data
     * @param DocumentInterface|null $document
     * @return self
     */
    public static function getInstanceFromPost($data, $document = null)
    {
        $form = new Admin_Form_Document();

        $subforms = $form->getSubForms();

        foreach ($subforms as $name => $subform) {
            if (array_key_exists($name, $data)) {
                $subform->constructFromPost($data[$name], $document);
            } else {
                // ActionBox und InfoBox haben keine Element die im POST enthalten wären, müssen aber nach POST wieder
                // neu initialisiert werden
                $subform->constructFromPost([], $document);
            }
        }

        return $form;
    }

    /**
     * Verarbeitet POST Request vom Formular.
     *
     * @param array $data
     * @param array $context
     * @return array|null
     */
    public function processPost($data, $context)
    {
        // POST Daten an Unterformulare weiterreichen
        $subforms = $this->getSubForms();

        foreach ($subforms as $name => $form) {
            if (array_key_exists($name, $data)) {
                // TODO process return value (exit from loop if success)
                $result = $form->processPost($data[$name], $data);

                if ($result !== null) {
                    return $result;
                }
            }
        }

        return null;
    }

    /**
     * Setzt das Editieren eines Documents nach dem Hinzufügen einer Person/Collection auf einer anderen Seite fort.
     *
     * @param Zend_Controller_Request_Http $request
     * @param Zend_Session_Namespace|null  $session
     */
    public function continueEdit($request, $session = null)
    {
        $subforms = $this->getSubForms();

        foreach ($subforms as $subform) {
            $subform->continueEdit($request, $session);
        }
    }

    /**
     * Validiert POST Daten.
     *
     * Die überschriebene Function führt einmal die normale Validierung aus und ruft dann eine zweite Funktion auf,
     * die sich mit Validierungen befasst, die mehrere Unterformulare betreffen können. Beispiele sind:
     *
     * - ein TitleMain in Document-Language muss vorhanden sein (Document_General und Document_TitleMain)
     *
     * @param array      $data
     * @param array|null $context
     * @return bool
     */
    public function isValid($data, $context = null)
    {
        $result = parent::isValid($data, $context);

        return ($result & $this->isDependenciesValid($data, $data)) === 1;
    }

    /**
     * Lädt die Dekoratoren.
     *
     * Der 'Fieldset' Decorator wird entfernt, damit das gesamte Formular nicht auch noch eine extra Überschrift
     * bekommt.
     */
    public function loadDefaultDecorators()
    {
        parent::loadDefaultDecorators();

        $this->removeDecorator('Fieldset');
    }

    /**
     * Setzt die globale Nachricht für das Formular.
     *
     * @param string $message Nachricht
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * Liefert die globale Nachricht für das Formular.
     *
     * @return null|string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Bereitet Formular fuer Anzeige als View vor.
     *
     * Fuegt Unterformular fuer Dateien hinzu. Dateien sind nicht Teil des Metadaten-Formulars, werden aber in der
     * Metadaten-Übersicht mit aufgelistet.
     */
    public function prepareRenderingAsView()
    {
        parent::prepareRenderingAsView();

        if ($this->document !== null) {
            if (count($this->document->getFile()) > 0) {
                $subform = new Admin_Form_Document_Files();
                $subform->populateFromModel($this->document);
                $this->addSubForm($subform, 'Files');
            }
        }
    }
}
