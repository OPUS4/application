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

class Admin_Form_Document_MultiIdentifierSubForm extends Admin_Form_Document_MultiSubForm
{

    /**
     * Name des Buttons zum Entfernen eines Unterformulars (z.B. Identifier).
     */
    const ELEMENT_REMOVE = 'Remove';

    /**
     * Checkbox, die den Status der automatischen Generierung des Identifiers bei der Veröffentlichung des Dokuments anzeigt
     */
    const ELEMENT_CHK_AUTO = 'Auto';

    /**
     * Name des Buttons für die sofortige Generierung eines Identifiers
     */
    const ELEMENT_GENERATE = 'Generate';

    /**
     * Typ des Identifiers
     * @var string
     */
    private $_type;

    /**
     * Kurzbezeichnung des Identifier-Typs (doi | urn)
     * @var string
     */
    private $_typeShort;

    /**
     * Konstruiert Instanz von Formular.
     *
     * @param string $subFormClass Name der Klasse für Unterformulare
     */
    public function __construct($subFormClass)
    {
        // Typ aus Klassennamen ableiten (Suffix nach dem letzten Unterstrich)
        $this->_type = substr($subFormClass, strrpos($subFormClass, '_') + 1);
        $this->_typeShort = strtolower(substr($this->_type, -3));
        parent::__construct($subFormClass, $this->_type);
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

        $this->setLegend('admin_document_section_' . strtolower($this->_type));

        $this->addCheckbox();

        $this->setDecorators(
            array(
                'FormElements', // Zend decorator
                'TableWrapper',
                array(
                    array('fieldsWrapper' => 'HtmlTag'),
                    array('tag' => 'div', 'class' => 'fields-wrapper')
                ),
                array(
                    'FieldsetWithButtons', array() // Überschrift DOI bzw. URN innerhalb von Identifiers-Block
                ),
                array(
                    array('divWrapper' => 'HtmlTag'),
                    array('tag' => 'div', 'class' => 'subform')
                )
            )
        );

        $this->setRemoveEmptyCheckbox(false);
    }

    /**
     * Adds a checkbox for controlling auto generation of identifier.
     * @throws Zend_Form_Exception
     */
    private function addCheckbox()
    {
        $name = self::ELEMENT_CHK_AUTO . $this->_type;
        $this->addElement(
            'checkbox',
            $name,
            array(
                'label' => 'admin_document_' . strtolower($name),
                'order' => 0
            )
        );
    }

    /**
     * Erzeugt Unterformulare abhängig von den Metadaten im Dokument.
     *
     * @param Opus_Document $document
     */
    public function populateFromModel($document)
    {

        // muss die Checkbox entfernt werden?
        $removeCheckbox = $this->removeCheckboxForPublishedDocs($document);

        $this->clearSubForms();

        $this->addGenerateAtPublishCheckbox($document);

        $identifier = $document->getIdentifier();
        $values = $this->filterIdentifier($identifier);

        $offset = $removeCheckbox ? 0 : 1; // Checkbox hat bereits den Offset 0
        if (empty($values)) {
            // es ist noch kein Identifier des Typs für das Dokument gespeichert
            $this->_addSubForm($offset);
            return;
        }

        // jeden Identifier des Typs anzeigen und ab dem 2. Identifier einen Lösch-Button anbieten
        foreach ($values as $index => $value) {
            $subForm = $this->_addSubForm($index + $offset, count($values) == 1);
            $subForm->populateFromModel($value);

            if ($index == 0 && $this->_typeShort == 'doi') {
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
     * @param int $position
     * @param null $disableGenerateButton
     *
     * @return _subFormClass
     */
    protected function _addSubForm($position, $disableGenerateButton = null)
    {
        $subForm = $this->createSubForm();

        if (is_null($disableGenerateButton)) {
            // Generieren-Button wird angezeigt, weil bislang noch kein Identifier gespeichert wurde
            $this->addGenerateButton($subForm);
        }
        else if ($disableGenerateButton) {
            // Generieren-Button neben dem ersten Eintrag anzeigen; den Button aber nicht anklickbar machen
            $this->addGenerateButton($subForm, false);
        }

        $this->prepareSubFormDecorators($subForm);

        $subForm->setOrder($position);
        $this->_setOddEven($subForm);
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
     */
    private function addGenerateAtPublishCheckbox($document)
    {
        $autoGenerateCheckbox = $this->getElement(self::ELEMENT_CHK_AUTO . $this->_type);

        if (is_null($autoGenerateCheckbox)) {
            return;
        }

        // Status der Checkbox aus Enrichment bestimmen (Checkbox aktiv oder nicht aktiv),
        // wenn Enrichment für vorliegendes Dokument gesetzt ist
        $enrichmentKeyName = $this->getEnrichmentKeyName();
        $enrichment = $document->getEnrichment($enrichmentKeyName);
        if (!is_null($enrichment)) {
            $autoGenerateCheckbox->setChecked($enrichment->getValue() == 'true');
            return; // Enrichment gefunden: Methode verlassen
        }

        // Enrichment wurde nicht gefunden: Status der Checkbox bestimmt sich aus der Konfiguration
        $config = $this->getApplicationConfig();
        switch ($this->_typeShort) {
            case 'doi':
                $autoGenerateCheckbox->setChecked($config->doi->autoCreate || $config->doi->autoCreate == '1');
                break;
            case 'urn':
                $autoGenerateCheckbox->setChecked($config->urn->autoCreate || $config->urn->autoCreate == '1');
                break;
        }
    }

    /**
     * bei bereits veröffentlichten Dokumenten soll die Checkbox zum automatischen
     * Setzen der ID nicht angezeigt werden
     *
     * @param Opus_Document $document das zu editierende Dokument
     * @return liefert true zurück, wenn die Checkbox entfernt wurde
     */
    private function removeCheckboxForPublishedDocs($document)
    {
        if ($document->getServerState() == 'published'){
            $this->removeElement(self::ELEMENT_CHK_AUTO . $this->_type);
            return true;
        }
        return false;
    }

    /**
     * Filtert aus der übergebenen Liste von Identifiern nur die Identifier mit dem Typ aus.
     *
     * @param array $identifiers Liste mit Elementen vom Typ Opus_Identifier
     * @return array mit Elementen vom Typ Opus_Identifier (nach der Filterung auf Basis des Typs)
     */
    private function filterIdentifier($identifiers)
    {
        $result = array();
        foreach ($identifiers as $identifier) {
            $type = $identifier->getType();
            if ($type == $this->_typeShort) {
                $result[] = $identifier;
            }
        }
        return $result;
    }

    /**
     * Erzeugt Unterformulare basierend auf den Informationen in den POST Daten.
     *
     * TODO was passiert wenn ein invalides Formular auftaucht beim anschließenden $form->populate()?
     */
    public function constructFromPost($post, $document = null)
    {
        $keys = array_keys($post);

        $removeCheckbox = $this->removeCheckboxForPublishedDocs($document);

        $position = $removeCheckbox ? 0 : 1; // Checkbox hat bereits den Offset 0

        foreach ($keys as $index => $key) {
            // Prüfen ob Unterformluar (array) oder Feld
            if (is_array($post[$key]) && $this->isValidSubForm($post[$key])) {
                if ((!$removeCheckbox && count($keys) == 2) || ($removeCheckbox && count($keys) == 1)) {
                    // nur in diesem Fall wird der Generieren-Button überhaupt angezeigt
                    if ($post[$key][Admin_Form_Document_IdentifierSpecific::ELEMENT_VALUE] == '') {
                        $this->_addSubForm($position);
                    }
                    else {
                        $subform = $this->_addSubForm($position, true);
                        $identifierId = $post[$key][Admin_Form_Document_IdentifierSpecific::ELEMENT_ID];
                        if (!is_null($identifierId) && $identifierId != '') {
                            $position++;
                            // Status-Anzeige für die erste DOI: Hinweistext, der darauf hinweist, dass registrierte DOIs nicht verändert werden sollten
                            $form = new Admin_Form_Document_RegistrationNote();
                            $form->populateFromModel($identifierId);
                            $subform->addSubForm($form, 'RegistrationNoteDOI', $position);
                        }
                    }
                }
                else {
                    $this->_addSubForm($position, false);
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
     * @return string Ergebnis der Verarbeitung
     */
    public function processPost($data, $context)
    {
        foreach ($data as $subFormName => $subdata) {
            $subform = $this->getSubForm($subFormName);
            if (!is_null($subform)) {

                if (array_key_exists(self::ELEMENT_REMOVE, $subdata)) {
                    $result = $this->processPostRemove($subFormName, $subdata);

                    // wenn nur noch ein Eingabefeld für Identifier des Typs übrig bleibt: Generieren-Button anzeigen
                    if (count($this->getSubForms()) == 1) {
                        $firstIdForm = reset($this->getSubForms());
                        $this->addGenerateButton($firstIdForm, false);
                        // TODO ohne den nachfolgenden Aufruf wird der Button nicht neben, sondern über dem Input-Field ausgegeben
                        $this->prepareSubFormDecorators($firstIdForm);
                    }

                    return $result;
                }

                if (array_key_exists(self::ELEMENT_GENERATE, $subdata)) {
                    // ID des Dokuments wird für DOI-Generierung benötigt
                    $docId = $context['Actions'][Admin_Form_Document_Actions::ELEMENT_ID]; // TODO kann Schlüssel dynamisch ermittelt werden
                    return $this->processPostGenerate($subform, $docId);
                }

                $result = $subform->processPost($subdata, $context);
                if (!is_null($result)) {
                    if (is_array($result)) {
                        $result['subformName'] = $subFormName;
                    }
                    return $result;
                }

            }
            else {
                // im POST-Request auch noch die Autogen-Checkbox mitgegeben
                // ignoriere Checkbox, wenn auf Button Generate / Remove gedrückt wurde
                if ($subFormName == self::ELEMENT_CHK_AUTO . $this->_type) {
                    continue;
                }
                $this->getLogger()->err(__METHOD__ . ': Subform with name ' . $subFormName . ' does not exits.');
            }
        }

        return null;
    }

    protected function processPostGenerate($subform, $docId)
    {
        switch ($this->_subFormClass) {
            case 'Admin_Form_Document_IdentifierDOI':
                try {
                    $doiManager = new Opus_Doi_DoiManager();
                    $doiValue = $doiManager->generateNewDoi($docId);
                    $subform->setValue($doiValue);
                    if ($doiValue != '') {
                        // Generieren-Button deaktivieren
                        $button = $subform->getElement(self::ELEMENT_GENERATE);
                        $button->setAttrib('disabled', 'disabled');
                    }
                }
                catch (Opus_Doi_DoiException $e) {
                    // generation of DOI value failed: show error message
                }
                break;

            case 'Admin_Form_Document_IdentifierURN':
                try {
                    $urnGenerator = new Admin_Model_UrnGenerator();
                    $urnValue = $urnGenerator->generateUrnForDocument($docId);
                    $subform->setValue($urnValue);
                    if ($urnValue != '') {
                        // Generieren-Button deaktivieren
                        $button = $subform->getElement(self::ELEMENT_GENERATE);
                        $button->setAttrib('disabled', 'disabled');
                    }
                }
                catch (Application_Exception $e) {
                    // generation of URN value failed: show error message
                }
                break;

            default:
                throw new Exception('Generate action is not supported for ' . $this->_type);
        }

        $this->_addAnker($this);
        return Admin_Form_Document::RESULT_SHOW;
    }

    /**
     * Aktualisiert das in der Datenbank gespeicherte Dokument (hier: seine Identifier)
     *
     * @param Opus_Document $document
     */
    public function updateModel($document)
    {
        // Array von Opus_Identifier Objekten eines Typs
        $values = $this->getSubFormModels($document);

        if (!empty($values)) {
            // sammle die Werte der Identifier des Typs aus dem Formular auf
            // umständliche Behandlung erforderlich, weil die Behandlung der Identifier-Typen in 3 Teilen erfolgt
            // DOI, URN und alle anderen Identifier-Typen
            $identifierValues = array();
            foreach ($values as $identifier) {
                if ($identifier->getValue() != '') {
                    $identifierValues[] = $identifier->getValue();
                }
            }

            $identifierValuesIndex = 0;
            $identifierValuesCount = count($identifierValues);

            $identifiers = array();
            foreach ($document->getIdentifier() as $identifier) {
                if ($identifier->getType() != $this->_typeShort) {
                    // sammle alle Identifier, die nicht vom aktuell betrachteten Typ sind, ohne weitere Prüfung auf
                    $identifiers[] = $identifier;
                }
                else {
                    // besondere Behandlung von Identifiern des aktuell betrachteten Typs
                    if ($identifierValuesIndex < $identifierValuesCount) {
                        $identifier->setValue($identifierValues[$identifierValuesIndex]);
                        $identifierValuesIndex++;
                        $identifiers[] = $identifier;
                    }
                }
            }

            while ($identifierValuesIndex < $identifierValuesCount) {
                $identifier = new Opus_Identifier();
                $identifier->setType($this->_typeShort);
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
     * @param $document
     */
    private function handleEnrichment($document)
    {
        $autoGenerateCheckbox = $this->getElement(self::ELEMENT_CHK_AUTO . $this->_type);
        if (!is_null($autoGenerateCheckbox)) {
            // Null-Check wichtig, da Checkbox nur bei nicht veröffentlichten Dokumenten angezeigt wird
            $autoGenerateValue = $autoGenerateCheckbox->isChecked();
            $enrichmentValue = $autoGenerateValue ? 'true' : 'false';

            $enrichmentKeyName = $this->getEnrichmentKeyName();
            $enrichments = $document->getEnrichment();
            $enrichmentExists = false;

            $newEnrichments = array();
            foreach ($enrichments as $enrichment) {
                if ($enrichment->getKeyName() == $enrichmentKeyName) {
                    $enrichmentExists = true;
                    $enrichment->setValue($enrichmentValue);
                }
                $newEnrichments[] = $enrichment;
            }

            if (!$enrichmentExists) {
                $enrichment = new Opus_Enrichment();
                $enrichment->setKeyName($enrichmentKeyName);
                $enrichment->setValue($enrichmentValue);
                $newEnrichments[] = $enrichment;
            }

            $document->setEnrichment($newEnrichments);
        }
    }

    /**
     * Erzeugt neues Unterformular zum Hinzufügen.
     * @return _subFormClass
     */
    public function createSubForm() {
        $classname = $this->_subFormClass;
        $subform = new $classname();

        // Entfernen-Button sollte nur ab dem zweiten Identifier angeboten werden
        $firstForm = empty($this->getSubForms());

        if (!$firstForm) {
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
     */
    private function addGenerateButton($subform, $enabled = true) {
        $button = $this->createElement(
            'submit',
            self::ELEMENT_GENERATE,
            array(
                'label' => 'admin_button_generate'
            )
        );
        if (!$enabled) {
            $button->setAttrib('disabled', 'disabled');
        }

        $subform->addElement($button);
    }


    /**
     * Bereitet die Dekoratoren für das Unterformular vor.
     *
     * @param type $subform
     */
    protected function prepareSubFormDecorators($subform)
    {
        $subform->addDecorator(array('tableRowWrapper' => 'HtmlTag'), array('tag' => 'tr'));
        $this->applyDecoratorsToElements($subform->getElements());
    }

    /**
     * Erzeugt den Button für das Entfernen des 2. bis n-ten Identifiers des Typs.
     */
    protected function addRemoveButton($subform)
    {
        $button = $this->createElement(
            'submit',
            self::ELEMENT_REMOVE,
            array(
                'label' => 'admin_button_remove'
            )
        );
        $subform->addElement($button);
    }

    /**
     * @return bool
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
     */
    protected function _removeSubForm($name)
    {
        $order = $this->getSubForm($name)->getOrder();
        $this->removeSubForm($name);
        return $order;
    }

    /**
     * Methode wurde überschrieben, da Spezialbehandlung durch die Checkbox erforderlich ist
     */
    protected function _removeGapsInSubFormOrder()
    {
        $subforms = $this->getSubForms();

        $renamedSubforms = array();

        $checkbox = $this->getElement(self::ELEMENT_CHK_AUTO . $this->_type);
        $pos = is_null($checkbox) ? 0 : 1;

        foreach ($subforms as $index => $subform) {
            $subform->setOrder($pos);
            $name = $this->getSubFormBaseName() . $pos;
            $renamedSubforms[$name] = $subform;
            $this->_setOddEven($subform);
            $pos++;
        }

        $this->setSubForms($renamedSubforms);
    }

    private function getEnrichmentKeyName()
    {
        return 'opus.' .  $this->_typeShort . '.autoCreate';
    }
}
