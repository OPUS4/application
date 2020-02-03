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
 * @subpackage  Form_Document
 * @author      Jens Schwidder <schwidder@zib.de>
 * @author      Sascha Szott <opus-development@saschaszott.de>
 * @copyright   Copyright (c) 2013-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Unterformular für einzelne Enrichments im Metadaten-Formular.
 *
 */
class Admin_Form_Document_Enrichment extends Admin_Form_AbstractModelSubForm
{
    /**
     * Name des Formularelements für die Enrichment-ID.
     */
    const ELEMENT_ID = 'Id';

    /**
     * Name des Formularelements für den Namen des Enrichment-Keys.
     */
    const ELEMENT_KEY_NAME = 'KeyName';

    /**
     * Name des Formularelements für den Enrichment-Wert.
     */
    const ELEMENT_VALUE = 'Value';

    /**
     * wenn true, dann werden Fehler bezüglich des Formularelements für den Enrichmentwert ignoriert
     */
    private $ignoreValueErrors = false;

    /**
     * Erzeugt die Formularelemente. Das Formularelement für den Enrichment-Wert
     * wird erst in das Formular eingefügt, wenn der tatsächlich ausgewählte
     * Enrichment-Key (aus dem sich schließlich der Enrichment-Type ergibt)
     * feststeht. Aus dem Enrichment-Type ergibt sich die Art des Formularelements
     * für den Enrichment-Wert.
     */
    public function init()
    {
        parent::init();

        $this->addElement('Hidden', self::ELEMENT_ID);

        $this->addElement(
            'EnrichmentKey',
            self::ELEMENT_KEY_NAME,
            ['required' => true, 'class' => 'enrichmentKeyName']
        );
    }

    /**
     * Initialisiert die Formularelemente mit den Werten aus dem übergebenen
     * Enrichment-Model. Diese Methode wird beim ersten Formularaufruf (GET)
     * aufgerufen.
     *
     * @param Opus_Enrichment $enrichment
     */
    public function populateFromModel($enrichment)
    {
        $this->getElement(self::ELEMENT_ID)->setValue($enrichment->getId());
        $this->getElement(self::ELEMENT_KEY_NAME)->setValue($enrichment->getKeyName());

        $enrichmentKey = $enrichment->getEnrichmentKey();
        if (! is_null($enrichmentKey)) {
            $this->setEnrichmentValueFormElement($enrichmentKey, $enrichment->getValue());
        } else {
            $this->getLogger()->err('Enrichment ' . $enrichment->getId() . ' does not provide key object - unknown enrichment key');
        }
    }

    /**
     * Erzeugt ein für das Enrichment passendes Eingabeformularfeld (in Abhängigkeit
     * des EnrichmentTypes, der dem EnrichmentKey des Enrichments zugeordnet wurde).
     * Wurde ein Wert im zweiten Argument übergeben, so wird das neu eingefügte
     * Formularfeld mit diesem Wert initialisiert.
     *
     * @param Opus_EnrichmentKey $enrichmentKey EnrichmentKey des Enrichments, für das ein
     *                           Eingabeformularelement erzeugt werden soll
     * @param null $value        optionaler Wert für das erzeugte Formularfeld
     */
    private function setEnrichmentValueFormElement($enrichmentKey, $value = null)
    {
        $enrichmentType = $enrichmentKey->getEnrichmentType();
        if (is_null($enrichmentType)) {
            // es handelt sich um ein Enrichment, das einen EnrichmentKey verwendet,
            // der noch keinen zugeordneten Typ besitzt: in diesem Fall wird der
            // EnrichmentType TextType angenommen (einfacher Text)
            $enrichmentType = new Opus_Enrichment_TextType();
        }

        if ($enrichmentType->getFormElementName() === 'Select') {
            // Sonderbehandlung für Select-Formularfeld erforderlich: aktuellen Enrichment-Wert
            // im vorliegenden Dokument in Auswahlliste eintragen, sofern er nicht bereits in der
            // Auswahlliste enthalten ist
            $element = $this->createSelectFormElement($enrichmentType, $value);
        } else {
            // neues Formularfeld für die Eingabe des Enrichment-Wertes erzeugen
            $element = $enrichmentType->getFormElement($value);
        }

        // neues Formularelement soll vor dem Entfernen-Button erscheinen
        $element->setOrder(2);

        // neues Formularelement in das bestehende Unterformular einfügen
        $elements = $this->getElements();
        $elements[self::ELEMENT_VALUE] = $element;
        $this->setElements($elements);

        if (! $enrichmentType->isStrictValidation()) {
            // verstößt der im Enrichment gespeicherte Wert gegen die aktuelle Typkonfiguration
            if (! is_null($value) && ! $element->isValid($value)) {
                // Hinweistext anzeigen, der auf Verstoß hinweist
                $element->markAsError();
                $this->handleValidationErrorNonStrict();
            }
        }
    }

    /**
     * Besondere Behandlung von Enrichment-Typen, die zur Wertauswahl ein Select-Formularfeld verweden.
     * Hier kann es erforderlich sein, dass im vorliegenden Dokument ein Enrichment-Wert genutzt wird,
     * der nicht in der konfigurierten Werteliste im Enrichment-Key enthalten ist. Ein solcher Wert
     * soll dennoch (als erster Eintrag) im Select-Formularfeld zur Auswahl angeboten werden.
     *
     * @param Opus_Enrichment_SelectType $enrichmentType
     * @param string $value
     */
    private function createSelectFormElement($enrichmentType, $value)
    {
        if (is_null($value)) {
            $enrichmentId = $this->getElement(self::ELEMENT_ID)->getValue();
            try {
                $enrichment = new Opus_Enrichment($enrichmentId);
                $value = $enrichment->getValue();
            } catch (\Opus\Model\Exception $e) {
                // ignore exception silently
            }
        }

        // Feldliste erweitern, wenn $value nicht bereits in der Feldliste auftritt
        $addValueToOptions = ! is_null($value) && ! in_array($value, $enrichmentType->getValues());
        if ($addValueToOptions) {
            // Erweiterung der Feldliste des Select-Elements erforderlich
            $values = $enrichmentType->getValues();
            array_unshift($values, $value); // zusätzlichen Wert am Anfang hinzufügen
            $enrichmentType->setValues($values);
        }

        $element = $enrichmentType->getFormElement($value);

        if ($addValueToOptions && $enrichmentType->isStrictValidation()) {
            // in diesem Fall muss sichergestellt werden, dass der ursprüngliche Wert des Enrichments
            // im vorliegenden Dokument nicht mehr als gültig betrachtet und daher nicht mehr gespeichert werden darf
            $validator = $element->getValidator('Zend_Validate_InArray');

            // erster Wert (der nach der EnrichmentKey-Konfiguration nun nicht mehr gültig ist) muss
            // aus der Liste der als gültig akzeptierten Werte entfernt werden und der Validator aktualisiert werden
            $haystack = $validator->getHaystack();
            array_shift($haystack);
            $validator->setHaystack($haystack);
        }

        return $element;
    }

    /**
     * Aktualisiert Enrichment Modell mit Werten im Formular.
     *
     * @param Opus_Enrichment $enrichment
     */
    public function updateModel($enrichment)
    {
        $enrichmentKeyName = $this->getElementValue(self::ELEMENT_KEY_NAME);
        $enrichmentKey = Opus_EnrichmentKey::fetchByName($enrichmentKeyName);

        $enrichmentValue = $this->getElementValue(self::ELEMENT_VALUE);

        if (! is_null($enrichmentKey)) {
            // Enrichment-Key existiert tatsächlich (es handelt sich um einen registrierten Key)
            $enrichment->setKeyName($enrichmentKeyName);

            // besondere Behandlung von Enrichment-Keys, die als Select-Formularlement dargestellt werden
            $enrichmentType = $enrichmentKey->getEnrichmentType();
            if (! is_null($enrichmentType) && $enrichmentType->getFormElementName() === 'Select') {
                // bei Select-Feldern wird im POST nicht der ausgewählte Wert übergeben,
                // sondern der Index des Wertes in der Werteliste (beginnend mit 0)
                // daher ist hier ein zusätzlicher Mapping-Schritt erforderlich, der vom im POST
                // angegebenen Index den tatsächlich ausgewählten Wert ableitet

                // falls keine strikte Validierung stattfindet, dann darf der ursprünglich im
                // Dokument gespeichert Enrichment-Wert (steht in Select-Feldliste an erster Stelle)
                // auch dann gespeichert werden, wenn er gemäß der Konfiguration des Enrichment-Keys
                // eigentlich nicht gültig ist: in diesem Fall keinen neuen Wert im Enrichment setzen
                $indexOffset = 0;
                if (! in_array($enrichment->getValue(), $enrichmentType->getValues())) {
                    if ($enrichmentValue == 0) {
                        return; // keine Änderung des Enrichment-Werts
                    }

                    // beim Mapping von Select-Feldwertindex auf tatsächlichen Wert aus Typkonfiguration 1 abziehen
                    $indexOffset = -1;
                }

                $enrichmentValue = $enrichmentType->getValues()[$enrichmentValue + $indexOffset];
            }
        }

        $enrichment->setValue($enrichmentValue);
    }

    /**
     * Liefert angezeigtes oder neues (hinzuzufügendes) Enrichment Modell.
     *
     * @return \Opus_Enrichment
     */
    public function getModel()
    {
        $enrichmentId = $this->getElement(self::ELEMENT_ID)->getValue();

        if (empty($enrichmentId) && ! is_numeric($enrichmentId)) {
            $enrichmentId = null;
        }

        try {
            $enrichment = new Opus_Enrichment($enrichmentId);
        } catch (Opus_Model_NotFoundException $omnfe) {
            $this->getLogger()->err(
                __METHOD__ . " Unknown enrichment ID = '$enrichmentId' (" . $omnfe->getMessage() . ').'
            );
            $enrichment = new Opus_Enrichment();
        }

        $this->updateModel($enrichment);

        return $enrichment;
    }

    /**
     * Lädt die Dekoratoren für dieses Formular.
     */
    public function loadDefaultDecorators()
    {
        parent::loadDefaultDecorators();

        $this->removeDecorator('Fieldset');
    }

    /**
     * Initialisierung des Formularelements für den Enrichment-Wert in
     * Abhängigkeit vom EnrichmentType des ausgewählten Enrichment-Keys.
     *
     * Der Name des Enrichment-Key kann als optionales Argument übergeben werden.
     * Wird kein Name übergeben, so wird der erste Enrichment-Key (in der nach
     * Name sortierten Reihenfolge) betrachtet.
     *
     * @param string|null $enrichmentKeyName Name eines Enrichment-Keys oder null
     * @param string|null $enrichmentId ID des Enrichments
     */
    public function initEnrichmentValueElement($enrichmentKeyName = null, $enrichmentId = null)
    {
        // wurde kein Name eines Enrichment-Keys übergeben, so ermittle den Namen
        // des ersten Enrichment-Keys im Auswahlfeld
        if (is_null($enrichmentKeyName)) {
            $enrichmentKeyElement = $this->getElement(self::ELEMENT_KEY_NAME);
            $allEnrichmentKeys = $enrichmentKeyElement->getMultiOptions();
            if (! empty($allEnrichmentKeys)) {
                // der erste Enrichment-Key der in der Auswahlliste steht, bestimmt das Eingabefeld
                reset($allEnrichmentKeys);
                $enrichmentKeyName = key($allEnrichmentKeys);
            }
        }

        $enrichmentKey = Opus_EnrichmentKey::fetchByName($enrichmentKeyName);
        if (! is_null($enrichmentKey)) {
            // hier braucht erstmal nur das Formularelement für die Eingabe des
            // Enrichment-Wertes erzeugt und in das bestehende Formular eingebunden
            // werden - der konkrete Wert des Enrichments wird später durch einen andere Methode
            // gesetzt

            $valueToBeAdded = null;
            if (! is_null($enrichmentId)) {
                try {
                    $enrichment = new Opus_Enrichment($enrichmentId);
                    // besondere Überprüfung beim Select-Feld erforderlich: hier muss ggf. der aktuell
                    // im Enrichment gespeicherte Wert zur Werteliste des Select-Felds hinzugefügt werden,
                    // wenn er nicht bereits enthalten ist
                    $enrichmentType = $enrichment->getEnrichmentKey()->getEnrichmentType();
                    if (! is_null($enrichmentType) && $enrichmentType->getFormElementName() == 'Select') {
                        $enrichmentValue = $enrichment->getValue();
                        if (! in_array($enrichmentValue, $enrichmentType->getValues())) {
                            $valueToBeAdded = $enrichmentValue;
                        }
                    }
                } catch (\Opus\Model\Exception $e) {
                    // ignore exception silently
                }
            }
            $this->setEnrichmentValueFormElement($enrichmentKey, $valueToBeAdded);
        } else {
            $this->getLogger()->err('could not find enrichment key with name ' . $enrichmentKey);
        }
    }

    /**
     * Bei der Anzeige des Formulars im Non-Edit-Mode soll auch eine Ausgabe erfolgen,
     * wenn der EnrichmentType als Checkbox dargestellt wird. In diesem Fall soll
     * der Text No/Nein erscheinen.
     *
     * Außerdem soll ein möglicher Verstoß des Enrichmentwerts gegen die Typkonfiguration
     * des Enrichment-Keys nicht als Validierungsfehler im Non-Edit-Mode erscheinen.
     *
     */
    public function prepareRenderingAsView()
    {
        $this->setRemoveEmptyCheckbox(false);

        $element = $this->getElement(self::ELEMENT_VALUE);
        $element->setAttrib('data-opusValidationError', 'false');
        $element->removeDecorator('Errors');

        parent::prepareRenderingAsView();
    }

    public function isValid($data)
    {
        $validationResult = parent::isValid($data);

        if ($validationResult) {
            return true; // keine Validierungsfehler gefunden
        }

        // ggf. kann das negative Validierungsergebnis noch auf "positiv" (true / valid) geändert werden,
        // wenn die Validation Policy des Enrichment Types des verwendeten Enrichment Keys auf "none"
        // gesetzt wurde und sich der Enrichment-Wert im POST-Request nicht vom ursprünglich im
        // Dokument gespeicherten Enrichment-Wert unterscheidet
        $enrichmentData = $data[$this->getName()];
        $enrichmentKey = Opus_EnrichmentKey::fetchByName($enrichmentData[self::ELEMENT_KEY_NAME]);
        if (! is_null($enrichmentKey)) {
            $enrichmentType = $enrichmentKey->getEnrichmentType();
            if (! is_null($enrichmentType) && ! $enrichmentType->isStrictValidation()) {
                // hat sich der Enrichment-Wert nicht geändert, so ist der (nicht geänderte)
                // Enrichment-Wert weiterhin gültig, auch wenn er gegen die Typkonfiguration verstößt

                if (! array_key_exists(self::ELEMENT_ID, $enrichmentData)) {
                    return false; // negatives Validierungsergebnis bleibt bestehen
                }

                $enrichmentId = $enrichmentData[self::ELEMENT_ID];
                try {
                    $enrichment = new Opus_Enrichment($enrichmentId);

                    if (! array_key_exists(self::ELEMENT_VALUE, $enrichmentData)) {
                        return false; // negatives Validierungsergebnis bleibt bestehen
                    }

                    $formValue = $enrichmentData[self::ELEMENT_VALUE];
                    if ($enrichmentType->getFormElementName() == 'Select') {
                        // bei Select-Formularfeldern wird im POST-Request nicht der ausgewählte Wert,
                        // sondern der Index des Wertes in der Auswahlliste zurückgeben: daher ist hier
                        // ein zusätzlicher Schritt zur Ermittlung des Formularwertes erforderlich
                        $options = $this->getElement(self::ELEMENT_VALUE)->getMultiOptions();
                        $formValueAsInt = intval($formValue);
                        if (0 <= $formValueAsInt && $formValueAsInt < count($options)) {
                            $formValue = $options[$formValueAsInt];
                        } else {
                            $formValue = null;
                        }
                    }
                    if (! is_null($formValue) && $enrichment->getValue() === $formValue) {
                        // Wert des Enrichments wurde nicht geändert und es findet keine strikte Validierung statt
                        // Validierungsergebnis wird daher auf "positiv" geändert
                        $this->ignoreValueErrors = true;
                        $this->handleValidationErrorNonStrict();
                        return true;
                    }
                } catch (Opus\Model\Exception $e) {
                    // ignore exception silently: do not change validation result
                }
            }
        }
        return false;
    }

    /**
     * Die entsprechende Methode in Zend_Form musste überschrieben werden, weil die API keine Möglichkeit bietet,
     * nach dem Aufruf von isValid auf dem Formularlement für den Enrichmentwert die in _errors gespeicherten Fehler
     * zu entfernen. Dies ist aber genau dann erforderlich, wenn keine strikte Validierung stattfindet, und der
     * ursprüngliche Enrichmentwert nicht verändert wurde.
     *
     * @param null $name
     * @param bool $suppressArrayNotation
     * @return array
     */
    public function getErrors($name = null, $suppressArrayNotation = false)
    {
        if ($this->ignoreValueErrors) {
            // mögliche Fehler werden ignoriert
            return [];
        }

        return parent::getErrors($name, $suppressArrayNotation);
    }

    /**
     * Verstößt der in einem Feld gespeicherte Enrichment-Wert gegen die aktuelle Typkonfiguration
     * des Enrichment Keys, so wird im Validierungsmodus "non strict" nur ein Hinweis, aber keine
     * Fehlermeldung angezeigt. Der unveränderte (aber bezüglich der aktuellen Typkonfiguration invalide)
     * Wert lässt sicher weiterhin speichern.
     *
     * @throws Zend_Form_Exception
     */
    private function handleValidationErrorNonStrict()
    {
        $element = $this->getElement(self::ELEMENT_VALUE);
        $element->setAttrib('data-opusValidationError', 'true');
        $decorator = $element->getDecorator('Errors');
        $decorator->setOption('class', 'errors notice');
    }
}
