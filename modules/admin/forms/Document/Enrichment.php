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
     * Formularfeld mit dem Wert initialisiert.
     *
     * @param      $enrichmentKey EnrichmentKey, des Enrichments, für das ein
     *                            Eingabeformularelement erzeugt werden soll
     * @param null $value         optionaler Wert für das erzeugte Formularfeld
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

        // neues Formularfeld für die Eingabe des Enrichment-Wertes erzeugen
        $element = $enrichmentType->getFormElement($value);

        // neues Formularelement soll vor dem Entfernen-Button erscheinen
        $element->setOrder(2);

        // neues Formularelement in das bestehende Unterformular einfügen
        $elements = $this->getElements();
        $elements[self::ELEMENT_VALUE] = $element;
        $this->setElements($elements);
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
            // Enrichment-Key existiert tatsächlich
            $enrichment->setKeyName($enrichmentKeyName);

            $enrichmentType = $enrichmentKey->getEnrichmentType();
            if (! is_null($enrichmentType) && $enrichmentType->getFormElementName() === 'Select') {
                // bei Select-Feldern wird im POST nicht der ausgewählte Wert übergeben,
                // sondern der Index des Wertes in der Werteliste (beginnend mit 0)
                // daher ist hier ein zusätzlicher Mapping-Schritt erforderlich
                $enrichmentValue = $enrichmentType->getValues()[$enrichmentValue];
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
     * Initialisierung des Formularelements für den Enrichment-Werts in
     * Abhängigkeit vom EnrichmentType des ausgewählten EnrichmentKeys.
     *
     * Der Name des Enrichment-Key kann als optionales Argument übergeben werden.
     * Wird kein Name übergeben, so wird der erste Enrichment-Key (in der nach
     * Name sortierten Reihenfolge) betrachtet.
     *
     * @param null $enrichmentKeyName Name eines Enrichment-Keys oder null
     */
    public function initEnrichmentValueElement($enrichmentKeyName = null)
    {
        // wurde kein Name eines EnrichmentKeys übergeben, so ermittle den Namen
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
            // werden - der konkrete Wert wird später durch einen andere Methode
            // gesetzt
            $this->setEnrichmentValueFormElement($enrichmentKey);
        } else {
            $this->getLogger()->err('could not find enrichment key with name ' . $enrichmentKey);
        }
    }

    /**
     * Bei der Anzeige des Formulars im Non-Edit-Mode soll auch eine Ausgabe erfolgen,
     * wenn der EnrichmentType als Checkbox dargestellt wird. In diesem Fall soll
     * der Text No/Nein erscheinen.
     */
    public function prepareRenderingAsView()
    {
        $this->setRemoveEmptyCheckbox(false);
        parent::prepareRenderingAsView();
    }
}
