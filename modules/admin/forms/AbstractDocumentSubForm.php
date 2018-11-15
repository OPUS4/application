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
 */

/**
 * Abstraktes Unterformular (SubForm) fuer Metadaten-Formular.
 *
 * @category    Application
 * @package     Module_Admin
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
abstract class Admin_Form_AbstractDocumentSubForm extends Application_Form_AbstractViewable {

    public function init() {
        parent::init();

        $this->setDisableLoadDefaultDecorators(true);
        $this->setDecorators(
            array(
                'FormElements',
                array(
                    array('fieldsWrapper' => 'HtmlTag'), 
                    array('tag' => 'div', 'class' => 'fields-wrapper')
                ),
                'FieldsetWithButtons',
                array(
                    array('divWrapper' => 'HtmlTag'), 
                    array('tag' => 'div', 'class' => 'subform')
                )
            )
        );
    }

    /**
     * Initialisiert das Formular mit den Werten des Models.
     *
     * @param $model
     */
    public function populateFromModel($model) {
        // leere Implementation
    }

    /**
     * Erzeugt Unterformularstruktur anhand der POST Hierarchy.
     *
     * @param array $post
     *
     * TODO Möglich mit populate() zu verschmelzen?
     */
    public function constructFromPost($post, $document = null) {
    }

    /**
     * Verarbeitet POST Request vom Formular.
     *
     * Das Defaultverhalten ist das weiterleiten des POST an die Unterformulare.
     *
     * @param $data POST Daten fuer Unterformular
     * @param $context POST Daten vom gesamten Request
     *
     * TODO Modifiziere zu $context = null um context optional zu machen?
     */
    public function processPost($data, $context) {
        $subforms = $this->getSubForms();

        foreach ($subforms as $name => $subform) {
            if (array_key_exists($name, $data)) {
                $result = $subform->processPost($data[$name], $context);

                if (!is_null($result)) {
                    return $result;
                }
            }
        }

        return null;
    }

    /**
     * Aktualisiert die Instanz von Opus_Document durch Formularwerte.
     *
     * TODO consider options for ChangeLog
     * @param Opus_Document $document
     */
    public function updateModel($model) {
        $subforms = $this->getSubForms();

        foreach ($subforms as $form) {
            $form->updateModel($model);
        }
    }

    /**
     * Funktion wird aufgerufen, wenn nach dem Hinzufügen einer Person oder Collection das Metadaten-Formular wieder
     * angezeigt wird.
     *
     * @param $request
     * @param null $session
     */
    public function continueEdit($request, $session = null) {
    }

    /**
     * Zusätzlich Validierungsfunktion für Prüfungen über mehrere Unterformulare hinweg.
     *
     * Bei dieser Funktion werden die POST Daten für das gesamte Formular mit heruntergereicht, um dann einen Abgleich
     * mit beliebigen Teilen des gesamten Formulars durchführen zu können.
     *
     * @param array $data
     * @param array $globalContext
     * @return boolean true - wenn alle Abhängigkeiten erfüllt sind
     */
    public function isDependenciesValid($data, $globalContext) {
        $result = true;

        foreach ($this->getSubForms() as $name => $subform) {
            if (array_key_exists($name, $data) && !$subform->isDependenciesValid($data[$name], $globalContext)) {
                $result = false; // trotzdem Validierung über alle Unterformulare um auch mehrere Meldungen anzuzeigen
            }
        }

        return $result;
    }

    /**
     * Liefert Helper fuer die Handhabung von Datumsangaben.
     *
     * @return \Application_Controller_Action_Helper_Dates
     */
    public function getDatesHelper() {
        return Zend_Controller_Action_HelperBroker::getStaticHelper('Dates');
    }

    /**
     * Translate legend if possible.
     *
     * This is done, so the translation in fieldset decorator can be disabled. It won't check if translation is
     * possible and therefore create log messages. Unfortunately some of our legends cannot be translated.
     *
     * @param string $legend
     * @return void|Zend_Form
     */
    public function setLegend($legend) {
        $translator = $this->getTranslator();
        if (!is_null($translator) && $translator->isTranslated($legend)) {
            parent::setLegend($translator->translate($legend));
        }
        else {
            parent::setLegend($legend);
        }
    }

}
