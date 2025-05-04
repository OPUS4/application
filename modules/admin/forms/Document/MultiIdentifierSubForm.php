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
 * @copyright   Copyright (c) 2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\DocumentInterface;
use Opus\Common\Enrichment;
use Opus\Common\Identifier;
use Opus\Doi\DoiException;
use Opus\Doi\DoiManager;

class Admin_Form_Document_MultiIdentifierSubForm extends Admin_Form_Document_DefaultMultiSubForm
{
    /**
     * Name des Buttons zum Entfernen eines Unterformulars (z.B. Identifier).
     */
    public const ELEMENT_REMOVE = 'Remove';

    /**
     * Checkbox, die den Status der automatischen Generierung des Identifiers bei der Veröffentlichung
     * des Dokuments anzeigt.
     */
    public const ELEMENT_CHK_AUTO = 'Auto';

    /**
     * Name des Buttons für die sofortige Generierung eines Identifiers
     */
    public const ELEMENT_GENERATE = 'Generate';

    /**
     * Typ des Identifiers
     *
     * @var string
     */
    private $type;

    /**
     * Kurzbezeichnung des Identifier-Typs (doi | urn)
     *
     * @var string
     */
    private $typeShort;

    /**
     * Konstruiert Instanz von Formular.
     *
     * @param string $subFormClass Name der Klasse für Unterformulare
     */
    public function __construct($subFormClass)
    {
        // Typ aus Klassennamen ableiten (Suffix nach dem letzten Unterstrich)
        $this->type      = substr($subFormClass, strrpos($subFormClass, '_') + 1);
        $this->typeShort = strtolower(substr($this->type, -3));
        parent::__construct($subFormClass, $this->type);
    }

    /**
     * Erzeugt die Formularelemente.
     *
     * init-Methode wird von ZF nach dem Aufruf des Konstruktors aufgerufen
     */
    public function init()
    {
        parent::init();

        // mehrere DOIs / URNs pro Dokument werden nicht unterstützt, so dass der Add-Button obsolet ist
        $this->removeElement(parent::ELEMENT_ADD);

        $this->setLegend('admin_document_section_' . strtolower($this->type));

        $this->addCheckbox();

        $this->setDecorators(
            [
                'FormElements', // Zend decorator
                'TableWrapper',
                [
                    ['fieldsWrapper' => 'HtmlTag'],
                    ['tag' => 'div', 'class' => 'fields-wrapper'],
                ],
                [
                    'FieldsetWithButtons',
                    [], // Überschrift DOI bzw. URN innerhalb von Identifiers-Block
                ],
                [
                    ['divWrapper' => 'HtmlTag'],
                    ['tag' => 'div', 'class' => 'subform'],
                ],
            ]
        );

        $this->setRemoveEmptyCheckbox(false);
    }

    /**
     * Adds a checkbox for controlling auto generation of identifier.
     *
     * @throws Zend_Form_Exception
     */
    private function addCheckbox()
    {
        $name = self::ELEMENT_CHK_AUTO . $this->type;
        $this->addElement(
            'checkbox',
            $name,
            [
                'label' => 'admin_document_' . strtolower($name),
                'order' => 0,
            ]
        );
    }

    /**
     * Erzeugt Unterformulare abhängig von den Metadaten im Dokument.
     *
     * @param DocumentInterface $document
     */
    public function populateFromModel($document)
    {
        // muss die Checkbox entfernt werden?
        $removeCheckbox = $this->removeCheckboxForPublishedDocs($document);

        $this->clearSubForms();

        $this->addGenerateAtPublishCheckbox($document);

        $identifier = $document->getIdentifier();
        $values     = $this->filterIdentifier($identifier);

        $offset = $removeCheckbox ? 0 : 1; // Checkbox hat bereits den Offset 0
        if (empty($values)) {
            // es ist noch kein Identifier des Typs für das Dokument gespeichert
            $this->addSubFormAndFixOrder($offset);
            return;
        }

        // jeden Identifier des Typs anzeigen und ab dem 2. Identifier einen Lösch-Button anbieten
        foreach ($values as $index => $value) {
            $subForm = $this->addSubFormAndFixOrder($index + $offset, count($values) === 1);
            $subForm->populateFromModel($value);

            if ($index === 0 && $this->typeShort === 'doi') {
                // Status-Anzeige für die erste DOI: Hinweistext, der darauf hinweist, dass registrierte DOIs nicht verändert werden sollten
                $form = new Admin_Form_Document_RegistrationNote();
                $form->populateFromModel($value);
                $subForm->addSubForm($form, 'RegistrationNoteDOI', $index + $offset + 1);
            }
        }
    }

    /**
     * Generieren-Button wird nur angezeigt, wenn $disableGenerateButton null oder true ist
     *
     * @param int       $position
     * @param bool|null $disableGenerateButton
     * @return Zend_Form _subFormClass
     */
    protected function addSubFormAndFixOrder($position, $disableGenerateButton = null)
    {
        $subForm = $this->createSubForm();

        if ($disableGenerateButton === null) {
            // Generieren-Button wird angezeigt, weil bislang noch kein Identifier gespeichert wurde
            $this->addGenerateButton($subForm);
        } elseif ($disableGenerateButton) {
            // Generieren-Button neben dem ersten Eintrag anzeigen; den Button aber nicht anklickbar machen
            $this->addGenerateButton($subForm, false);
        }

        $this->prepareSubFormDecorators($subForm);

        $subForm->setOrder($position);
        $this->setOddEven($subForm);
        $this->addSubForm($subForm, $this->getSubFormBaseName() . $position);

        return $subForm;
    }

    /**
     * ist das Enrichment opus.doi.autoCreate (analog für URNs: opus.urn.autoCreate) nicht
     * vorhanden, so greift die Konfigurationseinstellung bei der Anzeige der Checkbox und
     * beim Veröffentlichen des Dokuments
     *
     * ist das Enrichment vorhanden, so legt der Wert des Enrichments (true oder false) das
     * Verhalten der DOI- bzw. URN-Generierung beim Veröffentlichen des Dokuments fest
     * und auch die Anzeige der Checkbox (aktiviert oder nicht aktiviert)
     *
     * @param DocumentInterface $document
     */
    private function addGenerateAtPublishCheckbox($document)
    {
        $autoGenerateCheckbox = $this->getElement(self::ELEMENT_CHK_AUTO . $this->type);

        if ($autoGenerateCheckbox === null) {
            return;
        }

        // Status der Checkbox aus Enrichment bestimmen (Checkbox aktiv oder nicht aktiv),
        // wenn Enrichment für vorliegendes Dokument gesetzt ist
        $enrichmentKeyName = $this->getEnrichmentKeyName();
        $enrichment        = $document->getEnrichment($enrichmentKeyName);
        if ($enrichment !== null) {
            $autoGenerateCheckbox->setChecked($enrichment->getValue() === 'true');
            return; // Enrichment gefunden: Methode verlassen
        }

        // Enrichment wurde nicht gefunden: Status der Checkbox bestimmt sich aus der Konfiguration
        $config = $this->getApplicationConfig();
        switch ($this->typeShort) {
            case 'doi':
                $checkboxValue = isset($config->doi->autoCreate)
                    && filter_var($config->doi->autoCreate, FILTER_VALIDATE_BOOLEAN);
                $autoGenerateCheckbox->setChecked($checkboxValue);
                break;
            case 'urn':
                $checkboxValue = isset($config->urn->autoCreate)
                    && filter_var($config->urn->autoCreate, FILTER_VALIDATE_BOOLEAN);
                $autoGenerateCheckbox->setChecked($checkboxValue);
                break;
        }
    }

    /**
     * bei bereits veröffentlichten Dokumenten soll die Checkbox zum automatischen
     * Setzen der ID nicht angezeigt werden
     *
     * @param DocumentInterface $document das zu editierende Dokument
     * @return bool liefert true zurück, wenn die Checkbox entfernt wurde
     */
    private function removeCheckboxForPublishedDocs($document)
    {
        if ($document->getServerState() === 'published') {
            $this->removeElement(self::ELEMENT_CHK_AUTO . $this->type);
            return true;
        }
        return false;
    }

    /**
     * Filtert aus der übergebenen Liste von Identifiern nur die Identifier mit dem Typ aus.
     *
     * @param array $identifiers Liste mit Elementen vom Typ Identifier
     * @return array mit Elementen vom Typ Identifier (nach der Filterung auf Basis des Typs)
     */
    private function filterIdentifier($identifiers)
    {
        $result = [];
        foreach ($identifiers as $identifier) {
            $type = $identifier->getType();
            if ($type === $this->typeShort) {
                $result[] = $identifier;
            }
        }
        return $result;
    }

    /**
     * Erzeugt Unterformulare basierend auf den Informationen in den POST Daten.
     *
     * TODO was passiert wenn ein invalides Formular auftaucht beim anschließenden $form->populate()?
     *
     * @param array                  $post
     * @param DocumentInterface|null $document
     */
    public function constructFromPost($post, $document = null)
    {
        $keys = array_keys($post);

        $removeCheckbox = $this->removeCheckboxForPublishedDocs($document);

        $position = $removeCheckbox ? 0 : 1; // Checkbox hat bereits den Offset 0

        foreach ($keys as $index => $key) {
            // Prüfen ob Unterformluar (array) oder Feld
            if (is_array($post[$key]) && $this->isValidSubForm($post[$key])) {
                if ((! $removeCheckbox && count($keys) === 2) || ($removeCheckbox && count($keys) === 1)) {
                    // nur in diesem Fall wird der Generieren-Button überhaupt angezeigt
                    if ($post[$key][Admin_Form_Document_IdentifierSpecific::ELEMENT_VALUE] === '') {
                        $this->addSubFormAndFixOrder($position);
                    } else {
                        $subform      = $this->addSubFormAndFixOrder($position, true);
                        $identifierId = $post[$key][Admin_Form_Document_IdentifierSpecific::ELEMENT_ID];
                        if ($identifierId !== null && $identifierId !== '') {
                            $position++;
                            // Status-Anzeige für die erste DOI: Hinweistext, der darauf hinweist,
                            // dass registrierte DOIs nicht verändert werden sollten
                            $form = new Admin_Form_Document_RegistrationNote();
                            $form->populateFromModel($identifierId);
                            $subform->addSubForm($form, 'RegistrationNoteDOI', $position);
                        }
                    }
                } else {
                    $this->addSubFormAndFixOrder($position, false);
                }
                $position++;
            }
        }
    }

    /**
     * Verarbeitet POST Request fuer Formular.
     *
     * Der POST wird nicht an die Unterformulare weitergeleitet.
     * Bei der bisherigen Verwendung der Klasse ist das
     * nicht notwendig.
     *
     * @param array $data POST Daten für Unterformular
     * @param array $context POST Daten für gesamtes Formular
     * @return string|null Ergebnis der Verarbeitung
     */
    public function processPost($data, $context)
    {
        foreach ($data as $subFormName => $subdata) {
            $subform = $this->getSubForm($subFormName);
            if ($subform !== null) {
                if (array_key_exists(self::ELEMENT_REMOVE, $subdata)) {
                    $result = $this->processPostRemove($subFormName, $subdata);

                    // wenn nur noch ein Eingabefeld für Identifier des Typs übrig bleibt: Generieren-Button anzeigen
                    if (count($this->getSubForms()) === 1) {
                        $subforms    = $this->getSubForms();
                        $firstIdForm = reset($subforms); // TODO ERROR ?
                        $this->addGenerateButton($firstIdForm, false);
                        // TODO ohne den nachfolgenden Aufruf wird der Button nicht neben,
                        //      sondern über dem Input-Field ausgegeben
                        $this->prepareSubFormDecorators($firstIdForm);
                    }

                    return $result;
                }

                if (array_key_exists(self::ELEMENT_GENERATE, $subdata)) {
                    // ID des Dokuments wird für DOI-Generierung benötigt
                    // TODO kann Schlüssel dynamisch ermittelt werden
                    $docId = $context['Actions'][Admin_Form_Document_Actions::ELEMENT_ID];
                    return $this->processPostGenerate($subform, $docId);
                }

                $result = $subform->processPost($subdata, $context);
                if ($result !== null) {
                    if (is_array($result)) {
                        $result['subformName'] = $subFormName;
                    }
                    return $result;
                }
            } else {
                // im POST-Request auch noch die Autogen-Checkbox mitgegeben
                // ignoriere Checkbox, wenn auf Button Generate / Remove gedrückt wurde
                if ($subFormName === self::ELEMENT_CHK_AUTO . $this->type) {
                    continue;
                }
                $this->getLogger()->err(__METHOD__ . ': Subform with name ' . $subFormName . ' does not exits.');
            }
        }

        return null;
    }

    /**
     * @param Zend_Form $subform
     * @param int       $docId
     * @return string
     * @throws Exception
     */
    protected function processPostGenerate($subform, $docId)
    {
        switch ($this->subFormClass) {
            case 'Admin_Form_Document_IdentifierDOI':
                try {
                    $doiManager = new DoiManager();
                    $doiValue   = $doiManager->generateNewDoi($docId);
                    $subform->setValue($doiValue);
                    if ($doiValue !== '') {
                        // Generieren-Button deaktivieren
                        $button = $subform->getElement(self::ELEMENT_GENERATE);
                        $button->setAttrib('disabled', 'disabled');
                    }
                } catch (DoiException $e) {
                    // generation of DOI value failed: show error message
                }
                break;

            case 'Admin_Form_Document_IdentifierURN':
                try {
                    $urnGenerator = new Admin_Model_UrnGenerator();
                    $urnValue     = $urnGenerator->generateUrnForDocument($docId);
                    $subform->setValue($urnValue);
                    if ($urnValue !== '') {
                        // Generieren-Button deaktivieren
                        $button = $subform->getElement(self::ELEMENT_GENERATE);
                        $button->setAttrib('disabled', 'disabled');
                    }
                } catch (Application_Exception $e) {
                    // generation of URN value failed: show error message
                }
                break;

            default:
                throw new Exception('Generate action is not supported for ' . $this->type);
        }

        $this->addAnchor($this);
        return Admin_Form_Document::RESULT_SHOW;
    }

    /**
     * Aktualisiert das in der Datenbank gespeicherte Dokument (hier: seine Identifier)
     *
     * @param DocumentInterface $document
     */
    public function updateModel($document)
    {
        // Array von Identifier Objekten eines Typs
        $values = $this->getSubFormModels($document);

        if (! empty($values)) {
            // sammle die Werte der Identifier des Typs aus dem Formular auf
            // umständliche Behandlung erforderlich, weil die Behandlung der Identifier-Typen in 3 Teilen erfolgt
            // DOI, URN und alle anderen Identifier-Typen
            $identifierValues = [];
            foreach ($values as $identifier) {
                if ($identifier->getValue() !== '') {
                    $identifierValues[] = $identifier->getValue();
                }
            }

            $identifierValuesIndex = 0;
            $identifierValuesCount = count($identifierValues);

            $identifiers = [];
            foreach ($document->getIdentifier() as $identifier) {
                if ($identifier->getType() !== $this->typeShort) {
                    // sammle alle Identifier, die nicht vom aktuell betrachteten Typ sind, ohne weitere Prüfung auf
                    $identifiers[] = $identifier;
                } else {
                    // besondere Behandlung von Identifiern des aktuell betrachteten Typs
                    if ($identifierValuesIndex < $identifierValuesCount) {
                        $identifier->setValue($identifierValues[$identifierValuesIndex]);
                        $identifierValuesIndex++;
                        $identifiers[] = $identifier;
                    }
                }
            }

            while ($identifierValuesIndex < $identifierValuesCount) {
                $identifier = Identifier::new();
                $identifier->setType($this->typeShort);
                $identifier->setValue($identifierValues[$identifierValuesIndex]);
                $identifiers[] = $identifier;
                $identifierValuesIndex++;
            }

            $document->setIdentifier($identifiers);
        }

        $this->handleEnrichment($document);
    }

    /**
     * Behandlung des Enrichments opus.doi.autoCreate bzw. opus.urn.autoCreate in Abhängigkeit vom Status der Checkbox
     *
     * @param DocumentInterface $document
     */
    private function handleEnrichment($document)
    {
        $autoGenerateCheckbox = $this->getElement(self::ELEMENT_CHK_AUTO . $this->type);
        if ($autoGenerateCheckbox !== null) {
            // Null-Check wichtig, da Checkbox nur bei nicht veröffentlichten Dokumenten angezeigt wird
            $autoGenerateValue = $autoGenerateCheckbox->isChecked();
            $enrichmentValue   = $autoGenerateValue ? 'true' : 'false';

            $enrichmentKeyName = $this->getEnrichmentKeyName();
            $enrichments       = $document->getEnrichment();
            $enrichmentExists  = false;

            $newEnrichments = [];
            foreach ($enrichments as $enrichment) {
                if ($enrichment->getKeyName() === $enrichmentKeyName) {
                    $enrichmentExists = true;
                    $enrichment->setValue($enrichmentValue);
                }
                $newEnrichments[] = $enrichment;
            }

            if (! $enrichmentExists) {
                $enrichment = Enrichment::new();
                $enrichment->setKeyName($enrichmentKeyName);
                $enrichment->setValue($enrichmentValue);
                $newEnrichments[] = $enrichment;
            }

            $document->setEnrichment($newEnrichments);
        }
    }

    /**
     * Erzeugt neues Unterformular zum Hinzufügen.
     *
     * @return Zend_Form
     */
    public function createSubForm()
    {
        $classname = $this->subFormClass;
        $subform   = new $classname();

        // Entfernen-Button sollte nur ab dem zweiten Identifier angeboten werden
        $firstForm = empty($this->getSubForms());

        if (! $firstForm) {
            $this->addRemoveButton($subform);
        }

        return $subform;
    }

    /**
     * Erzeugt Button für das sofortige Generieren eines Identifiers des Typs.
     * Der Generieren-Button soll grundsätzlich nur erscheinen, wenn nicht mehr als eine DOI mit dem Dokument
     * verknüpft ist. Ist bereits eine nicht-leere DOI mit dem Dokument verknüpft, so soll der Button zwar
     * angezeigt werden, aber nicht anklickbar sein.
     *
     * Außerdem macht der Button nur dann Sinn, wenn in der Konfiguration im Schlüssel
     * doi.generatorClass der Name einer tatsächlich existierenden Klasse angegeben wurde.
     *
     * @param Zend_Form $subform
     * @param bool      $enabled
     */
    private function addGenerateButton($subform, $enabled = true)
    {
        $button = $this->createElement(
            'submit',
            self::ELEMENT_GENERATE,
            [
                'label' => 'admin_button_generate',
            ]
        );
        if (! $enabled) {
            $button->setAttrib('disabled', 'disabled');
        }

        $subform->addElement($button);
    }

    /**
     * Bereitet die Dekoratoren für das Unterformular vor.
     *
     * @param Zend_Form $subform
     */
    protected function prepareSubFormDecorators($subform)
    {
        $subform->addDecorator(['tableRowWrapper' => 'HtmlTag'], ['tag' => 'tr']);
        $this->applyDecoratorsToElements($subform->getElements());
    }

    /**
     * Erzeugt den Button für das Entfernen des 2. bis n-ten Identifiers des Typs.
     *
     * @param Zend_Form $subform
     */
    protected function addRemoveButton($subform)
    {
        $button = $this->createElement(
            'submit',
            self::ELEMENT_REMOVE,
            [
                'label' => 'admin_button_remove',
            ]
        );
        $subform->addElement($button);
    }

    /**
     * @return false
     */
    public function isEmpty()
    {
        return false;
    }

    /**
     * Entfernt Unterformular mit dem übergebenen Namen.
     *
     * Methode wurde überschrieben, da im Identifier-Kontext nicht der doppelte Aufruf
     * der Methode _removeGapsInSubFormOrder erforderlich ist (Methode _removeGapsInSubFormOrder
     * wird schon innerhalb der Methode removeSubForm aufgerufen)
     *
     * @param string $name Name des Unterformulars das entfernt werden sollte
     * @return int
     */
    protected function removeSubFormAndFixOrder($name)
    {
        $order = $this->getSubForm($name)->getOrder();
        $this->removeSubForm($name);
        return $order;
    }

    /**
     * Methode wurde überschrieben, da Spezialbehandlung durch die Checkbox erforderlich ist
     */
    protected function removeGapsInSubFormOrder()
    {
        $subforms = $this->getSubForms();

        $renamedSubforms = [];

        $checkbox = $this->getElement(self::ELEMENT_CHK_AUTO . $this->type);
        $pos      = $checkbox === null ? 0 : 1;

        foreach ($subforms as $index => $subform) {
            $subform->setOrder($pos);
            $name                   = $this->getSubFormBaseName() . $pos;
            $renamedSubforms[$name] = $subform;
            $this->setOddEven($subform);
            $pos++;
        }

        $this->setSubForms($renamedSubforms);
    }

    /**
     * @return string
     */
    private function getEnrichmentKeyName()
    {
        return 'opus.' . $this->typeShort . '.autoCreate';
    }
}
