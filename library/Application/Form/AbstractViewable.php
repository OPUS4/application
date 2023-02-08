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

/**
 * Abstrakte Basisklasse für Formulare, die als View angezeigt werden können.
 */
class Application_Form_AbstractViewable extends Application_Form_Abstract implements Application_Form_ViewableInterface
{
    /**
     * Wird TRUE gesetzt wenn das Formular für die Anzeige als View vorbereitet wird.
     *
     * @var bool
     */
    private $viewMode = false;

    /**
     * Option für das Entfernen von Elementen mit leerem Wert für das View Rendering.
     *
     * Manchmal will man sehen welche Felder nicht gesetzt wurden.
     *
     * @var bool
     */
    private $removeEmptyElements = true;

    /**
     * Option für das Entfernen von Checkboxen die nicht ausgewählt sind für das View Rendering.
     *
     * In manchen Situation möchte man sehen das 0 bzw. Nein ausgewählt wurde.
     *
     * @var bool
     */
    private $removeEmptyCheckbox = true;

    /**
     * Meldet, ob Anzeige als View vorbereitet wurde.
     *
     * @return bool
     */
    public function isViewModeEnabled()
    {
        return $this->viewMode;
    }

    /**
     * Aktiviert den View Mode für die Anzeige.
     *
     * @return $this
     */
    protected function setViewModeEnabled()
    {
        $this->viewMode = true;
        return $this;
    }

    /**
     * Bereitet das Formular für die Anzeige als View vor.
     */
    public function prepareRenderingAsView()
    {
        $this->setViewModeEnabled();
        $this->removeElements();
        $this->prepareRenderingOfElements();

        $subforms = $this->getSubForms();

        foreach ($subforms as $subform) {
            $subform->prepareRenderingAsView();
            if ($subform->isEmpty()) {
                $this->removeSubForm($subform->getName());
            }
        }
    }

    /**
     * Bereitet Formularelemente fuer statische Ausgabe in Metadaten-Übersicht vor.
     *
     * TODO rename function
     */
    protected function removeElements()
    {
        $elements = $this->getElements();

        foreach ($elements as $element) {
            $value = $element->getValue();

            if (
                $element instanceof Zend_Form_Element_Button
                || $element instanceof Zend_Form_Element_Submit
            ) {
                $this->removeElement($element->getName());
            } elseif (is_array($value)) {
                if (count($value) === 0 && $this->isRemoveEmptyElements()) {
                    $this->removeElement($element->getName());
                }
            } elseif (($value === null || trim($value) === '') && $this->isRemoveEmptyElements()) {
                $this->removeElement($element->getName());
            } elseif ($element instanceof Zend_Form_Element_Checkbox) {
                if (! $element->isChecked() && $this->isRemoveEmptyCheckbox() && $this->isRemoveEmptyElements()) {
                    $this->removeElement($element->getName());
                }
            }
        }
    }

    /**
     * Bereitet Formularelement für die Ausgabe als View vor.
     *
     * Es wird Application_Form_Decorator_ViewHelper verwendet, um Elemente als "View" ausgeben zu können.
     */
    protected function prepareRenderingOfElements()
    {
        $elements = $this->getElements();

        foreach ($elements as $element) {
            if ($element instanceof Application_Form_FormElementInterface) {
                $element->prepareRenderingAsView();
            } else {
                $decorator = $element->getDecorator('ViewHelper');
                if ($decorator instanceof Application_Form_Decorator_ViewHelper) {
                    $decorator->setViewOnlyEnabled(true);
                }
            }
        }
    }

    /**
     * Meldet, ob das Formulare leer ist.
     *
     * Ein Formulare ist leer wenn es keine Elemente und keine Unterformulare gibt. Diese Funktion wird von manchen
     * ableitenden Klassen überschrieben.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return count($this->getElements()) === 0 && count($this->getSubforms()) === 0;
    }

    /**
     * Setzt Option für das Entfernen von leeren Elementen.
     *
     * @param bool $removeEmptyElements
     */
    public function setRemoveEmptyElements($removeEmptyElements)
    {
        $this->removeEmptyElements = $removeEmptyElements;
    }

    /**
     * Meldet, ob Option für das Entfernen von leeren Element gesetzt ist.
     *
     * @return bool
     */
    public function isRemoveEmptyElements()
    {
        return $this->removeEmptyElements;
    }

    /**
     * Setzt Option für das Entfernen von leeren Checkboxen.
     *
     * @param bool $removeEmptyCheckbox
     * @return $this
     */
    public function setRemoveEmptyCheckbox($removeEmptyCheckbox)
    {
        $this->removeEmptyCheckbox = $removeEmptyCheckbox;
        return $this;
    }

    /**
     * Meldet, ob Option für das Entfernen von leeren Checkboxen gesetzt ist.
     *
     * @return bool
     */
    public function isRemoveEmptyCheckbox()
    {
        return $this->removeEmptyCheckbox;
    }
}
