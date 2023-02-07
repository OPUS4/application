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

use Opus\Common\Enrichment;
use Opus\Common\EnrichmentInterface;
use Opus\Common\EnrichmentKey;
use Opus\Common\Model\ModelException;
use Opus\Enrichment\SelectType;
use Opus\Enrichment\TextType;

/**
 * Unterformular für einzelne Enrichments im Metadaten-Formular.
 */
class Admin_Form_Document_Enrichment extends Admin_Form_AbstractModelSubForm
{
    /**
     * Name des Formularelements für die Enrichment-ID.
     */
    public const ELEMENT_ID = 'Id';

    /**
     * Name des Formularelements für den Namen des Enrichment-Keys.
     */
    public const ELEMENT_KEY_NAME = 'KeyName';

    /**
     * Name des Formularelements für den Enrichment-Wert.
     */
    public const ELEMENT_VALUE = 'Value';

    /** @var bool Wenn true, dann werden Fehler bezüglich des Formularelements für den Enrichmentwert ignoriert */
    private $ignoreValueErrors = false;

    /**
     * Erzeugt die Formularelemente. Das Formularelement für den Enrichment-Wert
     * wird erst in das Formular eingefügt, wenn der tatsächlich ausgewählte
     * Enrichment-Key (aus dem sich schließlich der Enrichment-Type ergibt)
     * feststeht. Aus dem Enrichment-Type ergibt sich schließlich die Art des
     * Formularelements für den Enrichment-Wert, z.B. Textfeld oder Auswahlfeld.
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
     * Initialisiert die Formularelemente mit den Werten aus dem übergebenen Enrichment-Model. Diese Methode wird beim
     * initialen Formularaufruf (d.h. nur im Kontext eines GET-Requests) aufgerufen.
     *
     * @param Enrichment $enrichment Enrichment aus der Datenbank, das im Formular angezeigt werden soll
     */
    public function populateFromModel($enrichment)
    {
        $this->getElement(self::ELEMENT_ID)->setValue($enrichment->getId());

        $keyNameElement    = $this->getElement(self::ELEMENT_KEY_NAME);
        $enrichmentKeyName = $enrichment->getKeyName();

        // kommt der EnrichmentKey nicht in der EnrichmentKey-Auswahl vor, dann hinzufügen
        // in diesem Fall handelt es sich um einen nicht registrierten EnrichmentKey
        $keyNameElement->addKeyNameIfMissing($enrichmentKeyName);
        $keyNameElement->setValue($enrichmentKeyName);

        $enrichmentKey = $enrichment->getEnrichmentKey();
        if ($enrichmentKey === null) {
            $this->getLogger()->info('Enrichment ' . $enrichment->getId() . " does not provide key object - unknown / unregistered enrichment key name '$enrichmentKeyName'");
            // in diesem Fall wird der Wert des Enrichments in einem Textfeld ausgegeben
        }

        $this->createValueFormElement($enrichment->getValue(), $enrichmentKey);
    }

    /**
     * Erzeugt ein für das Enrichment passendes Eingabeformularfeld (in Abhängigkeit des EnrichmentTypes, der dem
     * EnrichmentKey des Enrichments zugeordnet wurde). Ist kein EnrichmentType zugeordnet, so wird ein einfaches
     * Texteingabefeld verwendet.
     *
     * @param string             $enrichmentValue Wert des anzuzeigenden Enrichments (in der Datenbank)
     * @param EnrichmentKey|null $enrichmentKey EnrichmentKey des Enrichments, für das ein Eingabeformularelement
     *                                               erzeugt werden soll
     * @param string|null        $formValue aktueller Formularwert für das Enrichment (nur bei der Verarbeitung eines
     *                                      POST-Requests gesetzt)
     */
    private function createValueFormElement($enrichmentValue, $enrichmentKey = null, $formValue = null)
    {
        $enrichmentType = null;
        if ($enrichmentKey !== null) {
            $enrichmentType = $enrichmentKey->getEnrichmentType();
        }

        if ($enrichmentType === null) {
            // es handelt sich um ein Enrichment, das einen EnrichmentKey verwendet,
            // der noch keinen zugeordneten Typ besitzt oder um ein Enrichment, das
            // einen nicht registrierten EnrichmentKey verwendet: in diesem Fall wird der
            // EnrichmentType TextType angenommen (einfacher Text)
            $enrichmentType = new TextType();
        }

        $value = $enrichmentValue;
        if ($value === null) {
            // wird durch das Klicken auf den Hinzufügen-Button ein neue Formularzeile
            // für die Eingabe eines Enrichments hinzugefügt und ist der Typ des Schlüssels
            // der Select-Typ, so gilt der erste Auswahlwert als vorausgewählt
            if ($enrichmentType->getFormElementName() === 'Select') {
                $values = $enrichmentType->getValues();
                // zusätzliche Prüfung hier erforderlich, weil die Typkonfiguration (Werteliste) bei Select-Typ
                // kein Pflichtfeld ist (TODO Jens angefragt, ob das geändert werden soll)
                if (! empty($values)) {
                    $value = $values[0];
                }
            }
        }

        // neues Formularfeld für die Eingabe des Enrichment-Wertes erzeugen
        // wenn $value bezüglich der Typkonfiguration nicht zulässig ist,
        // wird $value durch den nachfolgenden Methodenaufruf nicht gesetzt
        $element = $enrichmentType->getFormElement($value);

        $enrichmentKeyName = null;
        if ($enrichmentKey !== null) {
            $enrichmentKeyName = $enrichmentKey->getName();
        }
        $translationKey = $this->handleEnrichmentKeySpecificTranslations('errorMessage', $enrichmentKeyName, false);
        $element->addErrorMessage($translationKey);

        // neues Formularelement soll vor dem Entfernen-Button erscheinen
        $element->setOrder(2);

        // neues Formularelement in das bestehende Unterformular einfügen
        $elements                      = $this->getElements();
        $elements[self::ELEMENT_VALUE] = $element;
        $this->setElements($elements);

        if ($enrichmentValue !== null) {
            if (! $enrichmentType->isStrictValidation()) {
                // verstößt der im Enrichment gespeicherte Wert gegen die aktuelle Typkonfiguration?
                // (Verstoß wird angezeigt, allerdings wird das Speichern zugelassen, sofern der Wert
                // vom Benutzer nicht verändert wurde)
                $elementValue = $element->getValue();
                $isValidValue = false;
                if (! ($elementValue === false)) {
                    $isValidValue = $element->isValid($value);

                    // der Aufruf der isValid-Methode hat einen Seiteneffekt, der bei Select-Elementen
                    // zum Ersetzen des Index durch den tatsächlichen Wert führt
                    $this->setDefault($element->getName(), $elementValue);
                }

                if (! $isValidValue) {
                    // in einem Select-Feld kann nur der erste Wert gegen die Typkonfiguration verstoßen
                    if ($enrichmentType->getFormElementName() === 'Select' && $formValue === 0) {
                        // Hinweistext anzeigen, der auf Verstoß hinweist
                        $this->handleValidationErrorNonStrict($enrichmentKey);
                    } else {
                        // wenn der Formularwert mit dem gespeicherten Wert übereinstimmt,
                        // dann im "Non Strict"-Mode Hinweis für den Benutzer anzeigen
                        if ($formValue === null || $enrichmentValue === $formValue) {
                            $this->handleValidationErrorNonStrict($enrichmentKey);
                        }
                    }
                }
            }
        }

        if ($enrichmentType->getFormElementName() === 'Select') {
            // Sonderbehandlung für Select-Formularfeld erforderlich: aktuellen Enrichment-Wert
            // in Auswahlliste eintragen, sofern er nicht bereits in der Auswahlliste enthalten ist
            $this->addOptionToSelectElement($element, $enrichmentType, $value);
        }
    }

    /**
     * Besondere Behandlung von Enrichment-Typen, die zur Wertauswahl ein Select-Formularfeld verwenden.
     * Hier kann es erforderlich sein, dass im vorliegenden Dokument ein Enrichment-Wert genutzt wird,
     * der nicht in der konfigurierten Werteliste im Enrichment-Key enthalten ist. Ein solcher Wert
     * soll dennoch (als erster Eintrag) im Select-Formularfeld zur Auswahl angeboten werden.
     *
     * @param Zend_Form_Element_Select $element Select-Formularfeld
     * @param SelectType               $enrichmentType Enrichment-Typ
     * @param string                   $value Wert, der zur Auswahlliste hinzugefügt werden soll
     */
    private function addOptionToSelectElement($element, $enrichmentType, $value)
    {
        // Feldliste erweitern, wenn $value nicht bereits in der Feldliste auftritt
        if ($value !== null && ! in_array($value, $enrichmentType->getValues())) {
            // in diesem Fall muss sichergestellt werden, dass der ursprüngliche Wert des Enrichments
            // im vorliegenden Dokument nicht mehr als gültig betrachtet und daher nicht mehr gespeichert werden darf
            $values = $enrichmentType->getValues();
            array_unshift($values, $value); // zusätzlichen Wert am Anfang hinzufügen
            $element->setMultiOptions($values);
            $element->setValue(0);
        }
    }

    /**
     * Aktualisiert Enrichment Modell mit Werten im Formular.
     *
     * @param Enrichment $enrichment
     */
    public function updateModel($enrichment)
    {
        $enrichmentKeyName = $this->getElementValue(self::ELEMENT_KEY_NAME);
        $enrichmentKey     = EnrichmentKey::fetchByName($enrichmentKeyName);

        $enrichmentValue = $this->getElementValue(self::ELEMENT_VALUE);

        if ($enrichmentKey !== null) {
            // Enrichment-Key existiert tatsächlich (es handelt sich um einen registrierten Key)
            $enrichment->setKeyName($enrichmentKeyName);

            // besondere Behandlung von Enrichment-Keys, die als Select-Formularlement dargestellt werden
            $enrichmentType = $enrichmentKey->getEnrichmentType();
            if ($enrichmentType !== null && $enrichmentType->getFormElementName() === 'Select') {
                // bei Select-Feldern wird im POST nicht der ausgewählte Wert übergeben,
                // sondern der Index des Wertes in der Werteliste (beginnend mit 0)
                // daher ist hier ein zusätzlicher Mapping-Schritt erforderlich, der vom im POST
                // angegebenen Index den tatsächlich ausgewählten Wert ableitet

                // falls keine strikte Validierung stattfindet, dann darf der ursprünglich im
                // Dokument gespeichert Enrichment-Wert (steht in Select-Feldliste an erster Stelle)
                // auch dann gespeichert werden, wenn er gemäß der Konfiguration des Enrichment-Keys
                // eigentlich nicht gültig ist: in diesem Fall keinen neuen Wert im Enrichment setzen
                $indexOffset = 0;
                if ($enrichment->getId() !== null) {
                    // keine Behandlung von Enrichments, die noch nicht in der Datenbank gespeichert sind,
                    // (nach dem Hinzufügen von Enrichments über Hinzufügen-Button)
                    if (! in_array($enrichment->getValue(), $enrichmentType->getValues())) {
                        if ($enrichmentValue === 0) {
                            return; // keine Änderung des Enrichment-Werts
                        }

                        // beim Mapping von Select-Feldwertindex auf tatsächlichen Wert aus Typkonfiguration 1 abziehen
                        $indexOffset = -1;
                    }
                }
                $enrichmentValue = $enrichmentType->getValues()[$enrichmentValue + $indexOffset];
            }
        }

        $enrichment->setValue($enrichmentValue);
    }

    /**
     * Liefert angezeigtes oder neues (hinzuzufügendes) Enrichment Modell.
     *
     * @return EnrichmentInterface
     */
    public function getModel()
    {
        $enrichmentId = $this->getElement(self::ELEMENT_ID)->getValue();

        if (empty($enrichmentId) && ! is_numeric($enrichmentId)) {
            $enrichmentId = null;
        }

        try {
            $enrichment = Enrichment::get($enrichmentId);
        } catch (ModelException $omnfe) {
            $this->getLogger()->err(
                __METHOD__ . " Unknown enrichment ID = '$enrichmentId' (" . $omnfe->getMessage() . ').'
            );
            $enrichment = Enrichment::new();
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
     * Initialisierung des Formularelements für den Enrichment-Wert in Abhängigkeit vom EnrichmentType des
     * ausgewählten Enrichment-Keys.
     *
     * Der Name des Enrichment-Key kann als optionales Argument übergeben werden. Wird kein Enrichment-Key-Name übergeben,
     * so wird der erste Enrichment-Key (in der nach Name sortierten Reihenfolge) betrachtet.
     *
     * Diese Methode wird nur im Kontext der Verarbeitung eines POST-Requests aufgerufen.
     *
     * @param string|null $enrichmentKeyName Name eines Enrichment-Keys oder null
     * @param string|null $enrichmentId ID des Enrichments, das in der Datenbank gespeichert ist oder null
     * @param string|null $formValue aktueller Formularwert für den Enrichment-Wert oder null
     */
    public function initValueFormElement($enrichmentKeyName = null, $enrichmentId = null, $formValue = null)
    {
        // wurde kein EnrichmentKey-Name übergeben, so ermittle den Namen des ersten Enrichment-Keys im Auswahlfeld
        if ($enrichmentKeyName === null) {
            $enrichmentKeyElement = $this->getElement(self::ELEMENT_KEY_NAME);
            $allEnrichmentKeys    = $enrichmentKeyElement->getMultiOptions();
            if (! empty($allEnrichmentKeys)) {
                // der erste Enrichment-Key der in der Auswahlliste steht, bestimmt das Eingabefeld
                reset($allEnrichmentKeys);
                $enrichmentKeyName = key($allEnrichmentKeys);
            }
        }

        $enrichment = null;
        if ($enrichmentId !== null) {
            // Formularfeld zeigt den Wert eines in der Datenbank gespeicherten Enrichments
            try {
                $enrichment = Enrichment::get($enrichmentId);
            } catch (ModelException $e) {
                // ignore exception silently
            }
        }

        $enrichmentKey = EnrichmentKey::fetchByName($enrichmentKeyName);
        if ($enrichmentKey === null) {
            if ($enrichment !== null && ($enrichmentKeyName === $enrichment->getKeyName())) {
                // der im Enrichment gespeicherte EnrichmentKey-Name ist nicht registriert
                $this->getLogger()->info("processing of unregistered enrichment key name '$enrichmentKeyName'");
            } else {
                // der im POST übergebene EnrichmentKey-Name ist nicht registriert und stimmt nicht mit dem
                // im Enrichment gespeicherten EnrichmentKey-Name überein: POST wurde manipuliert - Fallback auf
                // den ersten Auswahlwert
                $this->getLogger()->warn("could not find enrichment key with name '$enrichmentKeyName'");
            }
        }

        $enrichmentValue = $enrichment === null ? null : $enrichment->getValue();
        $this->createValueFormElement($enrichmentValue, $enrichmentKey, $formValue);
    }

    /**
     * wenn ein Enrichment bereits in der Datenbank gespeichert ist und einen nicht registrierten
     * EnrichmentKey verwendet, so muss der Name des EnrichmentKey als zusätzlcher Auswahlwert
     * im Select-Element ELEMENT_KEY_NAME aufgenommen werden
     *
     * @param string $enrichmentKeyName
     * @param int    $enrichmentId
     */
    private function initEnrichmentKeyNameElement($enrichmentKeyName, $enrichmentId)
    {
        if ($enrichmentKeyName === null || $enrichmentId === null) {
            return;
        }

        $enrichment = null;
        try {
            $enrichment = Enrichment::get($enrichmentId);
        } catch (ModelException $e) {
            // ignore silently
            return;
        }

        if ($enrichment !== null) {
            $keyNameElement = $this->getElement(self::ELEMENT_KEY_NAME);
            if (! $keyNameElement->hasKeyName($enrichmentKeyName)) {
                // der nicht registrierte EnrichmentKey-Name wird nur dann zum Auswahlfeld
                // hinzugefügt, wenn er mit dem KeyName des in der Datenbank gespeicherten
                // Enrichments übereinstimmt (sonst könnte man durch Manipulationd des POST-
                // Request beliebige EnrichmentKeys verwenden)
                if ($enrichmentKeyName === $enrichment->getKeyName()) {
                    $keyNameElement->addKeyNameIfMissing($enrichmentKeyName);
                    $this->getLogger()->debug('added option ' . $enrichmentKeyName . ' to enrichment key name element');
                }
            }
        }
    }

    /**
     * Initialisiert das Formularelement für die Eingabe des Enrichment-Wertes.
     * Diese Methode wird nur im Kontext der Verarbeitung eines POST-Requests
     * aufgerufen.
     *
     * @param array $post Array mit den im POST-Requests übergebenen Daten
     */
    public function initValueElement($post)
    {
        $subFormName       = $this->getName();
        $enrichmentKeyName = null;
        if (array_key_exists($subFormName, $post)) {
            $enrichmentKeyName = $post[$subFormName][self::ELEMENT_KEY_NAME];
        }

        $enrichmentId = null;
        if (array_key_exists(self::ELEMENT_ID, $post[$subFormName])) {
            $enrichmentId = $post[$subFormName][self::ELEMENT_ID];
            if ($enrichmentId === '') {
                $enrichmentId = null;
            }
        }

        $enrichmentValue = null;
        if (array_key_exists(self::ELEMENT_VALUE, $post[$subFormName])) {
            $enrichmentValue = $post[$subFormName][self::ELEMENT_VALUE];
        }

        $this->initEnrichmentKeyNameElement($enrichmentKeyName, $enrichmentId);
        $this->initValueFormElement($enrichmentKeyName, $enrichmentId, $enrichmentValue);
    }

    /**
     * Bei der Anzeige des Formulars im Non-Edit-Mode soll auch eine Ausgabe erfolgen,
     * wenn der EnrichmentType als Checkbox dargestellt wird. In diesem Fall soll
     * der Text No/Nein erscheinen.
     *
     * Außerdem soll ein möglicher Verstoß des Enrichmentwerts gegen die Typkonfiguration
     * des Enrichment-Keys nicht als Validierungsfehler im Non-Edit-Mode erscheinen.
     */
    public function prepareRenderingAsView()
    {
        $this->setRemoveEmptyCheckbox(false);

        $element = $this->getElement(self::ELEMENT_VALUE);
        $element->setAttrib('data-opusValidationError', 'false'); // wird vom JavaScript-Code ausgewertet
        $element->removeDecorator('Errors');
        $element->removeDecorator('Hint');

        parent::prepareRenderingAsView();
    }

    /**
     * @param array  $enrichmentData
     * @param string $enrichmentType
     * @param bool   $parentValidationResult
     * @return bool
     */
    private function handleSelectFieldStrict($enrichmentData, $enrichmentType, $parentValidationResult)
    {
        if (array_key_exists(self::ELEMENT_VALUE, $enrichmentData)) {
            $formValue = (int) $enrichmentData[self::ELEMENT_VALUE]; // das ist nicht der ausgewählte Wert, sondern der Index des Wertes innerhalb der Select-Liste
            if ($formValue === 0) {
                // der ausgewählte Wert ist nicht zulässig, wenn der gespeicherte Enrichmentwert gegen
                // die Typkonfiguration verstößt: in diesem Fall erscheint der ungültige Wert als erster
                // Auswahlfeld im Select-Element
                $enrichmentId = $enrichmentData[self::ELEMENT_ID];
                $enrichment   = null;
                try {
                    $enrichment = Enrichment::get($enrichmentId);
                } catch (ModelException $e) {
                    // ignore exception silently and do not change validation result
                }

                if ($enrichment !== null && array_search($enrichment->getValue(), $enrichmentType->getValues()) === false) {
                    $this->getElement(self::ELEMENT_VALUE)->markAsError();
                    return false; // Auswahlwert ist nach Typkonfiguration nicht zulässig
                }
            } else {
                $options = $enrichmentType->getValues();
                if ($formValue === count($options)) {
                    // durch die Hinzufügung des aktuell im Enrichment gespeicherten Wertes (der nicht
                    // in der Typkonfiguration aufgeführt ist) verlängert sich die Select-Auswahlliste
                    // um einen Eintrag (weil der ungültige Wert aus dem Enrichment an die erste Stelle
                    // eingeführt wird, steht an der letzten Stelle ein gültiger Wert aus der Typkonfiguration)

                    $this->ignoreValueErrors = true;

                    // Fehlermeldung am Formularelement löschen
                    $this->getElement(self::ELEMENT_VALUE)->removeDecorator('Errors');

                    return true;
                }
            }
        }
        return $parentValidationResult;
    }

    /**
     * Diese Methode wird nur bei der Verarbeitung eines POST-Requests aufgerufen.
     *
     * @param array $data
     * @return bool
     * @throws Zend_Form_Exception
     */
    public function isValid($data)
    {
        $validationResult = parent::isValid($data);

        $enrichmentData = $data[$this->getName()];
        $enrichmentKey  = EnrichmentKey::fetchByName($enrichmentData[self::ELEMENT_KEY_NAME]);

        // ggf. kann das negative Validierungsergebnis noch auf "positiv" (true / valid) geändert werden,
        // wenn die Validation Policy des Enrichment Types des verwendeten Enrichment Keys auf "none"
        // gesetzt wurde und sich der Enrichment-Wert im POST-Request nicht vom ursprünglich im
        // Dokument gespeicherten Enrichment-Wert unterscheidet
        if ($enrichmentKey !== null) {
            $enrichmentType = $enrichmentKey->getEnrichmentType();
            if ($enrichmentType !== null) {
                if ($enrichmentType->isStrictValidation()) {
                    if ($enrichmentType->getFormElementName() === 'Select') {
                        // wenn der erste Auswahlwert im Select-Element gewählt wurde, so muss geprüft werden, ob
                        // dieser Wert möglicherweise gegen die Typkonfiguration verstößt (als erster Wert wird immer
                        // der aktuell im Enrichment gespeicherte Wert verwendet - dieser kann möglicherweise gegen
                        // die aktuell gültige Typkonfiguration verstoßen)
                        // in diesem Fall muss isValid den Wert false zurückliefern

                        $validationResult = $this->handleSelectFieldStrict($enrichmentData, $enrichmentType, $validationResult);
                    }
                } else {
                    // hat sich der Enrichment-Wert nicht geändert, so ist der (nicht geänderte)
                    // Enrichment-Wert weiterhin gültig, auch wenn er gegen die Typkonfiguration verstößt

                    if (! array_key_exists(self::ELEMENT_ID, $enrichmentData)) {
                        return false; // negatives Validierungsergebnis bleibt bestehen
                    }

                    $enrichmentId = $enrichmentData[self::ELEMENT_ID];
                    try {
                        $enrichment = Enrichment::get($enrichmentId);

                        if (! array_key_exists(self::ELEMENT_VALUE, $enrichmentData)) {
                            return false; // negatives Validierungsergebnis bleibt bestehen
                        }

                        $formValue      = $enrichmentData[self::ELEMENT_VALUE];
                        $formValueAsInt = -1;
                        if ($enrichmentType->getFormElementName() === 'Select') {
                            // bei Select-Formularfeldern wird im POST-Request nicht der ausgewählte Wert,
                            // sondern der Index des Wertes in der Auswahlliste zurückgeben: daher ist hier
                            // ein zusätzlicher Schritt zur Ermittlung des Formularwertes erforderlich
                            $options        = $this->getElement(self::ELEMENT_VALUE)->getMultiOptions();
                            $formValueAsInt = intval($formValue);
                            if (0 <= $formValueAsInt && $formValueAsInt < count($options)) {
                                $formValue = $options[$formValueAsInt];
                            } else {
                                $formValue = null;
                            }
                        }

                        if ($formValue !== null && $enrichment->getValue() === $formValue) {
                            // Wert des Enrichments wurde nicht geändert und es findet keine strikte Validierung statt
                            // Validierungsergebnis für das Gesamtformular wird daher auf "positiv" geändert
                            $this->ignoreValueErrors = true;

                            // zusätzlicher Schritt: es könnte sein, dass der Ursprungswert gegn die Typkonfiguration
                            // verstößt: in diesem Fall einen grauen Hinweis anzeigen

                            $valueElem = $this->getElement(self::ELEMENT_VALUE);
                            if ($enrichmentType->getFormElementName() === 'Select') {
                                $formElementValidation = in_array($formValue, $enrichmentType->getValues());
                            } else {
                                $formElementValidation = $valueElem->isValid($formValue);
                            }
                            if (! $formElementValidation) {
                                // grauen Hinweis auf Verstoß anzeigen, weil der ursprüngliche Feldwert gegen die
                                // Typkonfiguration verstößt
                                $this->handleValidationErrorNonStrict($enrichmentKey);
                            }
                            return true;
                        } else {
                            // Sonderbehandlung bei Select-Feldern: hier ist der letzte Wert als gültig zu betrachten,
                            // wenn in die Select-Liste der bzgl. der Typkonfiguration ungültige Wert als erster Eintrag
                            // aufgenommen wurde
                            if ($enrichmentType->getFormElementName() === 'Select') {
                                $options = $enrichmentType->getValues();
                                if ($formValueAsInt === count($options)) {
                                    $this->ignoreValueErrors = true;

                                    // Fehlermeldung am Formularelement löschen
                                    $this->getElement(self::ELEMENT_VALUE)->removeDecorator('Errors');

                                    return true;
                                }
                            }
                        }
                    } catch (ModelException $e) {
                        // ignore exception silently: do not change validation result
                    }
                }
            }
        }
        return $validationResult;
    }

    /**
     * Die entsprechende Methode in Zend_Form musste überschrieben werden, weil die API keine Möglichkeit bietet,
     * nach dem Aufruf von isValid auf dem Formularlement für den Enrichmentwert die in _errors gespeicherten Fehler
     * zu entfernen. Dies ist aber genau dann erforderlich, wenn keine strikte Validierung stattfindet, und der
     * ursprüngliche Enrichmentwert nicht verändert wurde.
     *
     * @param string|null $name
     * @param bool        $suppressArrayNotation
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
     * Wert lässt sich weiterhin speichern.
     *
     * Wichtig: diese Methode muss sowohl beim ersten Formularaufruf (GET-Request) als auch beim
     * Speichern des Formulars (POST-Request) aufgerufen werden, wenn es Validierungsfehler gibt.
     *
     * @param null|EnrichmentKey $enrichmentKey Name des Enrichment-Keys
     * @throws Zend_Form_Exception
     */
    private function handleValidationErrorNonStrict($enrichmentKey = null)
    {
        $element = $this->getElement(self::ELEMENT_VALUE);
        $element->setAttrib('data-opusValidationError', 'true'); // wird vom JavaScript-Code ausgewertet

        $enrichmentKeyName = null;
        if ($enrichmentKey !== null) {
            $enrichmentKeyName = $enrichmentKey->getName();
        }
        $hint = $this->handleEnrichmentKeySpecificTranslations('validationMessage', $enrichmentKeyName, true);
        $element->setHint($hint);

        $element->removeDecorator('Errors');
    }

    /**
     * @param string      $keySuffix
     * @param string|null $enrichmentKeyName
     * @param bool        $doTranslation
     * @return string
     *
     * TODO tests for this function
     * TODO function does not have to be private
     * TODO function could have a better name - it doesn't just "handle" it returns something "get"
     */
    private function handleEnrichmentKeySpecificTranslations($keySuffix, $enrichmentKeyName = null, $doTranslation = false)
    {
        $translator        = $this->getTranslator();
        $translationPrefix = 'admin_enrichment_';
        if ($enrichmentKeyName !== null) {
            $translationKey = $translationPrefix . $enrichmentKeyName . '_' . $keySuffix;
            if ($translator->isTranslated($translationKey)) {
                if ($doTranslation) {
                    return $translator->translate($translationKey);
                }
                return $translationKey;
            }
        }

        $translationKey = $translationPrefix . $keySuffix;
        if ($doTranslation) {
            return $translator->translate($translationKey);
        }
        return $translationKey;
    }
}
