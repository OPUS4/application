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
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * SubForm um mehrere Unterformulare (z.B. Patente) zu verwalten.
 *
 * Die Unterformularhierarchy sieht folgendermaßen aus:
 *
 * DocumentMultiSubForm
 * +- SubForm0
 * |  +-
 * |
 * |
 * +- SubForm1
 * +- ...
 * +- Add Button
 */
class Admin_Form_Document_MultiSubForm extends Admin_Form_AbstractDocumentSubForm {

    /**
     * Name von Button zum Hinzufügen eines Unterformulars (z.B. Enrichment).
     */
    const ELEMENT_ADD = 'Add';

    /**
     * Name von Button zum Entfernen eines Unterformulars (z.B. Identifier).
     */
    const ELEMENT_REMOVE = 'Remove';

    /**
     * Klasse für Unterformulare.
     * @var type
     */
    protected $_subFormClass;

    /**
     * Opus_Document Feldname für Unterformulare.
     * @var type
     */
    protected $_fieldName;

    /**
     * Validierungsextension für die Unterformulare.
     * @var type
     */
    private $_subformValidator;

    private $_renderAsTableEnabled = false;

    private $_columns;

    /**
     * Konstruiert Instanz von Fomular.
     *
     * @param string $subFormClass Name der Klasse für Unterformulare
     * @param string $fieldName Name des Opus_Document Feldes, das angezeigt werden soll
     * @param string $validator Object für Validierungen über Unterformulare hinweg
     * @param multi $options
     */
    public function __construct($subFormClass, $fieldName, $validator = null, $options = null) {
        $this->_subFormClass = $subFormClass;
        $this->_fieldName = $fieldName;

        if (is_null($validator) || $validator instanceof Application_Form_Validate_IMultiSubForm) {
            $this->_subformValidator = $validator;
        }
        else {
            throw new Application_Exception(
                'Fehler beim Instanzieren von ' . __CLASS__
                . '. Validator ist keine Instanz von Application_Form_Validate_IMultiSubForm.'
            );
        }

        parent::__construct($options);
    }

    /**
     * Erzeugt die Formularelemente.
     */
    public function init() {
        parent::init();

        $this->initButton();

        $this->setLegend('admin_document_section_' . strtolower($this->_fieldName));

        $this->getElement(self::ELEMENT_ADD)->setDecorators(array())->setDisableLoadDefaultDecorators(true);

        if (!is_null($this->getColumns())) {
            $this->_renderAsTableEnabled = true;
            $this->setDecorators(
                array(
                    'FormElements', // Zend decorator
                    'TableHeader',
                    'TableWrapper',
                    array(
                        array('fieldsWrapper' => 'HtmlTag'), 
                        array('tag' => 'div', 'class' => 'fields-wrapper')
                    ),
                    array(
                        'FieldsetWithButtons', array('legendButtons' => self::ELEMENT_ADD)
                    ),
                    array(
                        array('divWrapper' => 'HtmlTag'), 
                        array('tag' => 'div', 'class' => 'subform')
                    )
                )
            );
        }
        else {
            $this->getDecorator('FieldsetWithButtons')->setLegendButtons(self::ELEMENT_ADD);
        }
    }

    protected function initButton() {
        $this->addElement('submit', self::ELEMENT_ADD, array('order' => 1000, 'label' => 'admin_button_add'));
    }

    /**
     * Erzeugt Unterformulare abhängig von den Metadaten im Dokument.
     *
     * @param Opus_Document $document
     */
    public function populateFromModel($document) {
       $this->clearSubForms();

       $values = $this->getFieldValues($document);

       $maxIndex = 0;

       foreach ($values as $index => $value) {
           if ($maxIndex < $index) {
               $maxIndex = $index;
           }
           $subForm = $this->_addSubForm($index);
           $subForm->populateFromModel($value);
       }

       // Sicherstellen, daß Button zum Hinzufügen zuletzt angezeigt wird
       $this->getElement(self::ELEMENT_ADD)->setOrder($maxIndex + 1);
    }

    /**
     * Holt vom Dokument den Wert des konfigurierten Feldes.
     * @param Opus_Document $document
     * @return array
     */
    public function getFieldValues($document) {
       $field = $document->getField($this->_fieldName);

       if (!is_null($field)) {
            return $field->getValue();
       }
       else {
           $this->getLogger()->err(__METHOD__ . " Feld $this->__fieldName nicht gefunden.");
       }
    }

    /**
     * Erzeugt Unterformulare basierend auf den Informationen in den POST Daten.
     *
     * TODO was passiert wenn ein invalides Formular auftaucht beim anschließenden $form->populate()?
     */
    public function constructFromPost($post, $document = null) {
        $keys = array_keys($post);

        $position = 0;

        foreach ($keys as $index => $key) {
            // Prüfen ob Unterformluar (array) oder Feld
            if (is_array($post[$key]) && $this->isValidSubForm($post[$key])) {
                $this->_addSubForm($position);
                $position++;
            }
        }
    }

    /**
     * Prüft, ob die POST Daten verwendet werden können, um Unterformular anzulegen.
     *
     * Die Standardimplementation liefert immer TRUE zurück.
     *
     * @param array $post
     * @return boolean TRUE - valides Unterformular; FALSE - ungültiges Unterformular
     */
    public function isValidSubForm($post) {
        return true;
    }

    /**
     * Verarbeitet POST Request fuer Formular.
     *
     * Der POST wird nicht an die Unterformulare weitergeleitet. Bei der bisherigen Verwendung der Klasse ist das
     * nicht notwendig.
     *
     * @param array $data POST Daten für Unterformular
     * @param array $context POST Daten für gesamtes Formular
     * @return string Ergebnis der Verarbeitung
     */
    public function processPost($data, $context) {
        // Prüfen ob "Hinzufügen" geklickt wurde
        if (array_key_exists(self::ELEMENT_ADD, $data)) {
            return $this->processPostAdd();
        }
        else {
            // Prüfen ob in einem Unterformular "Entfernen" geklickt wurde
            foreach ($data as $subFormName => $subdata) {
                $subform = $this->getSubForm($subFormName);
                if (!is_null($subform)) {
                    if (array_key_exists(self::ELEMENT_REMOVE, $subdata)) {
                        return $this->processPostRemove($subFormName, $subdata);
                    }
                    else {
                        $result = $subform->processPost($subdata, $context);
                        if (!is_null($result)) {
                            if (is_array($result)) {
                                $result['subformName'] = $subFormName;
                            }
                            return $result;
                        }
                    }
                }
                else {
                    $this->getLogger()->err(__METHOD__ . ': Subform with name ' . $subFormName . ' does not exits.');
                }
            }
        }

        return null;
    }

    protected function processPostRemove($subFormName, $subdata) {
        // TODO separate function for getting position?
        $position = $this->_removeSubForm($subFormName);

        $this->_addAnker($this->determineSubFormForAnker($position));

        return Admin_Form_Document::RESULT_SHOW;
    }

    protected function processPostAdd() {
        $subform = $this->appendSubForm();
        $this->_addAnker($subform);
        return Admin_Form_Document::RESULT_SHOW;
    }

    /**
     * Aktualisiert das Dokument.
     *
     * @param Opus_Document $document
     */
    public function updateModel($document) {
       $field = $document->getField($this->_fieldName);

       $values = $this->getSubFormModels($document);

       $field->setValue($values);
    }

    /**
     * Sammelt Werte (Modelle) von Unterformularen ein.
     *
     * Standardimplementation benötigt Parameter $document nicht.
     *
     * @return array
     */
    public function getSubFormModels($document = null) {
        $subforms = $this->getSubForms();

        $values = array();

        foreach ($subforms as $subform) {
            if (!is_null($subform)) {
                $value = $subform->getModel();

                if (!is_null($value)) {
                    $values[] = $value;
                }
            }
        }

        return $values;
    }

    /**
     * Fügt ein Unterformular an der gewünschten Position hinzu.
     *
     * @param int $position
     * @return \_subFormClass
     */
    protected function _addSubForm($position) {
        $subForm = $this->createSubForm();
        $subForm->setOrder($position);

        $this->_setOddEven($subForm);
        $this->addSubForm($subForm, $this->getSubFormBaseName() . $position);

        return $subForm;
    }

    public function removeSubForm($name) {
        $result = parent::removeSubForm($name);
        $this->_removeGapsInSubFormOrder();
        return $result;
    }

    /**
     * @param $subForm
     */
    protected function _setOddEven($subForm) {
        $position = $subForm->getOrder();

        $multiWrapper = $subForm->getDecorator('multiWrapper');

        if (!is_null($multiWrapper) && $multiWrapper instanceof Zend_Form_Decorator_HtmlTag) {
            $multiClass = $multiWrapper->getOption('class');
            $markerClass = ($position % 2 == 0) ? 'even' : 'odd';

            // TODO nicht 100% robust aber momentan ausreichend
            if (strpos($multiClass, 'even') !== false || strpos($multiClass, 'odd') !== false) {
                $multiClass = preg_replace('/odd|even/', $markerClass, $multiClass);
            }
            else {
                $multiClass .= ' ' . $markerClass;
            }

            $multiWrapper->setOption('class', $multiClass);
        }
    }

    public function getSubFormBaseName() {
        return $this->_fieldName;
    }

    /**
     * Erzeugt neues Unterformular zum Hinzufügen.
     * @return \_subFormClass
     */
    public function createSubForm() {
        $subform = $this->createNewSubFormInstance();

        $this->addRemoveButton($subform);
        $this->prepareSubFormDecorators($subform);

        return $subform;
    }

    /**
     * Bereites die Dekoratoren für das Unterformular vor.
     *
     * @param type $subform
     */
    protected function prepareSubFormDecorators($subform) {
        if ($this->isRenderAsTableEnabled()) {
            $subform->addDecorator(array('tableRowWrapper' => 'HtmlTag'), array('tag' => 'tr'));
            $this->applyDecoratorsToElements($subform->getElements());
        }
        else {
            $subform
                ->addDecorator(array('removeButton' => 'Button'), array('name' => 'Remove'))
                ->addDecorator(array('dataWrapper' => 'HtmlTag'), array('class' => 'data-wrapper multiple-data'))
                ->addDecorator(array('multiWrapper' => 'HtmlTag'), array('class' => 'multiple-wrapper'));
        }
    }

    /**
     * @param $elements
     */
    protected function applyDecoratorsToElements($elements) {
        foreach ($elements as $element) {
            $name = $element->getName();
            if ($name !== 'Id') {
                $element->removeDecorator('dataWrapper');
                $element->removeDecorator('LabelNotEmpty');
                $element->removeDecorator('ElementHtmlTag');
                $element->addDecorator(
                    array('tableCellWrapper' => 'ElementHtmlTag'), array('tag' => 'td',
                    'class' => "$name-data")
                );
            }
            else {
                $element->setDecorators(array());
                $element->loadDefaultDecoratorsIsDisabled(true);
            }
        }
    }

    protected function addRemoveButton($subform) {
        $button = $this->createRemoveButton();
        $subform->addElement($button);

        if ($this->isRenderAsTableEnabled()) {
            $idElement = $subform->getElement('Id');
            if (!is_null($idElement)) {
                $button->addDecorator('RemoveButton', array('element' => $idElement));
            }
            else {
                $this->getLogger()->err(__METHOD__ . 'Subform does not have element \'Id\'.');
            }
        }
    }

    /**
     * Erzeugt den Button fuer das Entfernen eines Unterformulars.
     *
     * Der Button hat keine Dekoratoren. Das heisst er kann dem Unterformular hinzugefuegt werden ohne dort die
     * Ausgabe zu beeinflussen. Das INPUT Element fuer den Button wird ueber einen Dekorator fuer das Formular
     * ausgegeben.
     *
     * @return Zend_Form_Element
     */
    protected function createRemoveButton() {
        return $this->createElement(
            'submit', self::ELEMENT_REMOVE, array('label' => 'admin_button_remove',
            'decorators' => array(), 'disableLoadDefaultDecorators' => true)
        );
    }

    /**
     * Erzeugt neue Instanz der Unterformklasse.
     * @return \_subFormClass
     */
    public function createNewSubFormInstance() {
        return new $this->_subFormClass();
    }

    /**
     * Entfernt Unterformular mit dem übergebenen Namen.
     * @param string $name Name des Unterformulars das entfernt werden sollte
     */
    protected function _removeSubForm($name) {
        $order = $this->getSubForm($name)->getOrder();

        $this->removeSubForm($name);
        $this->_removeGapsInSubFormOrder();

        return $order;
    }

    /**
     * Sorgt für lückenlose Nummerierung der Unterformulare.
     *
     * Warum ist dies wichtig? Bei der Konstruktion des Formulares vom POST werden die Unterformulare von 0 angefangen
     * durchnummeriert. Die Namen entsprechen also getSubFormBaseName() . $index, z.B. Identifier0, Identifier1 usw.
     * Das passiert unabhängig von den eigentlichen Namen der Unterformulare. Daher müssen die Namen der Unterformulare
     * vor der Ausgabe des Formulars lückenlos sein, z.B. nach dem Entfernen eines Identfier. Würden beliebige Namen
     * verwendet werden müsste unter Umständen, z.B. beim ändern der Rolle einer Person, mit Konflikten gerechnet
     * werden und die Unit Tests wären unübersichtlicher. Wenn die Anzahl der Unterformular zum Beispiel 5 ist, könnte
     * dann nicht garantiert werden, daß der Name "Identifier5" nicht schon belegt ist.
     */
    protected function _removeGapsInSubFormOrder() {
        $subforms = $this->getSubForms();

        $renamedSubforms = array();

        $pos = 0;

        foreach ($subforms as $index => $subform) {
            $subform->setOrder($pos);
            $name = $this->getSubFormBaseName() . $pos;
            $renamedSubforms[$name] = $subform;
            $this->_setOddEven($subform);
            $pos++;
        }

        $this->setSubForms($renamedSubforms);
    }

    /**
     * Erzeugt ein weiteres Unterformular an letzter Stelle.
     */
    public function appendSubForm() {
        $subforms = $this->getSubForms();

        return $this->_addSubForm(count($subforms));
    }

    /**
     * Ermittelt an welchem Unterformular der Sprungankor plaziert werden sollte.
     *
     * Wenn es keine Unterformulare mehr gibt, kommt der Anker ans übergeordnete Formular. Ansonsten kommt der Ankor an
     * das nächste Formular, daß aufgerutscht ist oder wenn das letzte Unterformular entfernt wurde, kommt der Ankor an
     * das neue letzte Formular.
     *
     * @param type $removedPosition
     * @return \Admin_Form_Document_MultiSubForm
     */
    public function determineSubFormForAnker($removedPosition) {
        $subforms = $this->getSubForms();

        $subformCount = count($subforms);

        if ($subformCount == 0) {
            return $this;
        }
        else if ($removedPosition < $subformCount) {
            $keys = array_keys($subforms);
            return $this->getSubForm($keys[$removedPosition]);
        }
        else {
            $keys = array_keys($subforms);
            return $this->getSubForm($keys[$subformCount - 1]);
        }
    }

    /**
     * Fuegt Anker fuer Positionierung des Formulars im Browser hinzu.
     *
     * Durch den Anker springt der Browser nach einem POST zu der gewuenschten Stelle, zum Beispiel dem gerade neu
     * hinzugefuegten Unterformular.
     *
     * @param Zend_Form $subform
     */
    protected function _addAnker($subform) {
        $subform->addDecorator(
            array('currentAnker' => 'HtmlTag'),
            array('tag' => 'a', 'placement' => 'prepend', 'name' => 'current')
        );
    }

    /**
     * Validiere TitleMain eingaben im Formular.
     *
     * Zusätzlich zu den normalen Validierungen für Formularelemente wird geprüft, ob eine Sprache zweimal ausgewählt
     * wurde.
     *
     * @param array $data
     * @return boolean
     */
    public function isValid($data, $context = null) {
        // wird immer aufgerufen um gegebenenfalls weitere Nachrichten anzuzeigen
        $result = true;

        if (!is_null($this->_subformValidator)) {
            if (array_key_exists($this->getName(), $data)) {
                $this->_subformValidator->prepareValidation($this, $data[$this->getName()], $context);
                $result = $this->_subformValidator->isValid($data[$this->getName()], $context);
            }
        }

        return $result && parent::isValid($data);
    }

    /**
     * Ermittelt, ob das Formular leer ist.
     *
     * Das Formular ist leer, wenn es keine Unterformulare gibt, als keine Modelle angezeigt werden (z.B. Identifier).
     *
     * @return boolean TRUE - wenn keine Unterformulare
     */
    public function isEmpty() {
        return count($this->getSubForms()) == 0;
    }

    public function setColumns($columns) {
        $this->_columns = $columns;
    }

    public function getColumns() {
        $columns = $this->_columns;

        if (!is_null($columns) && !$this->isViewModeEnabled()) {
            $columns[] = array('class' => 'Remove'); // Extra Spalte für Remove-Button
        }

        return $columns ;
    }

    public function setOptions(array $options) {
        if (isset($options['columns'])) {
            $this->setColumns($options['columns']);
            unset($options['columns']);
        }

        parent::setOptions($options);
    }

   public function isRenderAsTableEnabled() {
       return $this->_renderAsTableEnabled;
   }

}
