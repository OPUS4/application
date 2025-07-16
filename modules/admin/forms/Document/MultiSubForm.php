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
class Admin_Form_Document_MultiSubForm extends Admin_Form_AbstractDocumentSubForm
{
    /**
     * Name von Button zum Entfernen eines Unterformulars (z.B. Identifier).
     */
    public const ELEMENT_REMOVE = 'Remove';

    /** @var string Klasse für Unterformulare. */
    protected $subFormClass;

    /** @var string Document Feldname für Unterformulare. */
    protected $fieldName;

    /**
     * Validierungsextension für die Unterformulare.
     *
     * @var Application_Form_Validate_MultiSubFormInterface
     */
    private $subformValidator;

    /** @var bool */
    protected $renderAsTableEnabled = false;

    /** @var array */
    private $columns;

    /**
     * Konstruiert Instanz von Formular.
     *
     * @param string                                               $subFormClass Name der Klasse für Unterformulare
     * @param string                                               $fieldName Name des Document Feldes, das angezeigt werden soll
     * @param Application_Form_Validate_MultiSubFormInterface|null $validator Object für Validierungen über Unterformulare hinweg
     * @param array|null                                           $options
     */
    public function __construct($subFormClass, $fieldName, $validator = null, $options = null)
    {
        $this->subFormClass = $subFormClass;
        $this->fieldName    = $fieldName;

        if ($validator === null || $validator instanceof Application_Form_Validate_MultiSubFormInterface) {
            $this->subformValidator = $validator;
        } else {
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
    public function init()
    {
        parent::init();

        if ($this->getColumns() !== null) {
            $this->renderAsTableEnabled = true;
            $this->setDecorators(
                [
                    'FormElements', // Zend decorator
                    'TableHeader',
                    'TableWrapper',
                    [
                        ['fieldsWrapper' => 'HtmlTag'],
                        ['tag' => 'div', 'class' => 'fields-wrapper'],
                    ],
                    [
                        ['divWrapper' => 'HtmlTag'],
                        ['tag' => 'div', 'class' => 'subform'],
                    ],
                ]
            );
        }
    }

    /**
     * Erzeugt Unterformulare abhängig von den Metadaten im Dokument.
     *
     * @param DocumentInterface $document
     */
    public function populateFromModel($document)
    {
        $this->clearSubForms();

        $values = $this->getFieldValues($document);

        $maxIndex = 0;

        foreach ($values as $index => $value) {
            if ($maxIndex < $index) {
                $maxIndex = $index;
            }
            $subForm = $this->addSubFormAndFixOrder($index);
            $subForm->populateFromModel($value);
        }
    }

    /**
     * Holt vom Dokument den Wert des konfigurierten Feldes.
     *
     * @param DocumentInterface $document
     * @return array
     */
    public function getFieldValues($document)
    {
        $field = $document->getField($this->fieldName);

        if ($field !== null) {
            return $field->getValue();
        } else {
            $this->getLogger()->err(__METHOD__ . " Feld $this->__fieldName nicht gefunden.");
            return []; // TODO throw exception?
        }
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
        if ($post === null) {
            return;
        }

        $keys = array_keys($post);

        $position = 0;

        foreach ($keys as $index => $key) {
            // Prüfen ob Unterformluar (array) oder Feld
            if (is_array($post[$key]) && $this->isValidSubForm($post[$key])) {
                $this->addSubFormAndFixOrder($position);
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
     * @return true TRUE - valides Unterformular; FALSE - ungültiges Unterformular
     */
    public function isValidSubForm($post)
    {
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
     * @return string|array|null Ergebnis der Verarbeitung
     */
    public function processPost($data, $context)
    {
        // Prüfen ob in einem Unterformular "Entfernen" geklickt wurde
        foreach ($data as $subFormName => $subdata) {
            $subform = $this->getSubForm($subFormName);
            if ($subform !== null) {
                if (array_key_exists(self::ELEMENT_REMOVE, $subdata)) {
                    return $this->processPostRemove($subFormName, $subdata);
                } else {
                    $result = $subform->processPost($subdata, $context);
                    if ($result !== null) {
                        if (is_array($result)) {
                            $result['subformName'] = $subFormName;
                        }
                        return $result;
                    }
                }
            } else {
                $this->getLogger()->err(__METHOD__ . ': Subform with name ' . $subFormName . ' does not exits.');
            }
        }

        return null;
    }

    /**
     * @param string $subFormName
     * @param array  $subdata
     * @return string
     */
    protected function processPostRemove($subFormName, $subdata)
    {
        // TODO separate function for getting position?
        $position = $this->removeSubFormAndFixOrder($subFormName);

        $this->addAnchor($this->determineSubFormForAnchor($position));

        return Admin_Form_Document::RESULT_SHOW;
    }

    /**
     * Aktualisiert das Dokument.
     *
     * @param DocumentInterface $document
     */
    public function updateModel($document)
    {
        $field = $document->getField($this->fieldName);

        $values = $this->getSubFormModels($document);

        $field->setValue($values);
    }

    /**
     * Sammelt Werte (Modelle) von Unterformularen ein.
     *
     * Standardimplementation benötigt Parameter $document nicht.
     *
     * @param DocumentInterface|null $document
     * @return array
     */
    public function getSubFormModels($document = null)
    {
        $subforms = $this->getSubForms();

        $values = [];

        foreach ($subforms as $subform) {
            if ($subform !== null) {
                $value = $subform->getModel();

                if ($value !== null) {
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
     * @return Zend_Form
     */
    protected function addSubFormAndFixOrder($position)
    {
        $subForm = $this->createSubForm();
        $subForm->setOrder($position);

        $this->setOddEven($subForm);
        $this->addSubForm($subForm, $this->getSubFormBaseName() . $position);

        return $subForm;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function removeSubForm($name)
    {
        $result = parent::removeSubForm($name);
        $this->removeGapsInSubFormOrder();
        return $result;
    }

    /**
     * @param Zend_Form $subForm
     */
    protected function setOddEven($subForm)
    {
        $position = $subForm->getOrder();

        $multiWrapper = $subForm->getDecorator('multiWrapper');

        if ($multiWrapper !== null && $multiWrapper instanceof Zend_Form_Decorator_HtmlTag) {
            $multiClass  = $multiWrapper->getOption('class');
            $markerClass = $position % 2 === 0 ? 'even' : 'odd';

            // TODO nicht 100% robust aber momentan ausreichend
            if (strpos($multiClass, 'even') !== false || strpos($multiClass, 'odd') !== false) {
                $multiClass = preg_replace('/odd|even/', $markerClass, $multiClass);
            } else {
                $multiClass .= ' ' . $markerClass;
            }

            $multiWrapper->setOption('class', $multiClass);
        }
    }

    /**
     * @return string
     */
    public function getSubFormBaseName()
    {
        return $this->fieldName;
    }

    /**
     * Erzeugt neues Unterformular zum Hinzufügen.
     *
     * @return Zend_Form
     */
    public function createSubForm()
    {
        $subform = $this->createNewSubFormInstance();

        $this->addRemoveButton($subform);
        $this->prepareSubFormDecorators($subform);

        return $subform;
    }

    /**
     * Bereites die Dekoratoren für das Unterformular vor.
     *
     * @param Zend_Form $subform
     */
    protected function prepareSubFormDecorators($subform)
    {
        if ($this->isRenderAsTableEnabled()) {
            $subform->addDecorator(['tableRowWrapper' => 'HtmlTag'], ['tag' => 'tr']);
            $this->applyDecoratorsToElements($subform->getElements());
        } else {
            $subform
                ->addDecorator(['removeButton' => 'Button'], ['name' => 'Remove'])
                ->addDecorator(['dataWrapper' => 'HtmlTag'], ['class' => 'data-wrapper multiple-data'])
                ->addDecorator(['multiWrapper' => 'HtmlTag'], ['class' => 'multiple-wrapper']);
        }
    }

    /**
     * @param array $elements
     */
    protected function applyDecoratorsToElements($elements)
    {
        foreach ($elements as $element) {
            $name = $element->getName();
            if ($name !== 'Id') {
                $element->removeDecorator('dataWrapper');
                $element->removeDecorator('LabelNotEmpty');
                $element->removeDecorator('ElementHtmlTag');
                $element->addDecorator(
                    ['tableCellWrapper' => 'ElementHtmlTag'],
                    [
                        'tag'   => 'td',
                        'class' => "$name-data",
                    ]
                );
            } else {
                $element->setDecorators([]);
                $element->loadDefaultDecoratorsIsDisabled(true);
            }
        }
    }

    /**
     * @param Zend_Form $subform
     * @throws Zend_Exception
     * @throws Zend_Form_Exception
     */
    protected function addRemoveButton($subform)
    {
        $button = $this->createRemoveButton();
        $subform->addElement($button);

        if ($this->isRenderAsTableEnabled()) {
            $idElement = $subform->getElement('Id');
            if ($idElement !== null) {
                $button->addDecorator('RemoveButton', ['element' => $idElement]);
            } else {
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
    protected function createRemoveButton()
    {
        return $this->createElement(
            'submit',
            self::ELEMENT_REMOVE,
            [
                'label'                        => 'admin_button_remove',
                'decorators'                   => [],
                'disableLoadDefaultDecorators' => true,
            ]
        );
    }

    /**
     * Erzeugt neue Instanz der Unterformklasse.
     *
     * @return Zend_Form
     */
    public function createNewSubFormInstance()
    {
        return new $this->subFormClass();
    }

    /**
     * Entfernt Unterformular mit dem übergebenen Namen.
     *
     * @param string $name Name des Unterformulars das entfernt werden sollte
     * @return int|null TODO BUG must be just int
     */
    protected function removeSubFormAndFixOrder($name)
    {
        $order = $this->getSubForm($name)->getOrder();

        $this->removeSubForm($name);
        $this->removeGapsInSubFormOrder();

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
    protected function removeGapsInSubFormOrder()
    {
        $subforms = $this->getSubForms();

        $renamedSubforms = [];

        $pos = 0;

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
     * Erzeugt ein weiteres Unterformular an letzter Stelle.
     *
     * @return Zend_Form
     */
    public function appendSubForm()
    {
        $subforms = $this->getSubForms();

        return $this->addSubFormAndFixOrder(count($subforms));
    }

    /**
     * Ermittelt an welchem Unterformular der Sprungankor plaziert werden sollte.
     *
     * Wenn es keine Unterformulare mehr gibt, kommt der Anker ans übergeordnete Formular. Ansonsten kommt der Ankor an
     * das nächste Formular, daß aufgerutscht ist oder wenn das letzte Unterformular entfernt wurde, kommt der Ankor an
     * das neue letzte Formular.
     *
     * @param int $removedPosition
     * @return self|null|Zend_Form
     */
    public function determineSubFormForAnchor($removedPosition)
    {
        $subforms = $this->getSubForms();

        $subformCount = count($subforms);

        if ($subformCount === 0) {
            return $this;
        } elseif ($removedPosition < $subformCount) {
            $keys = array_keys($subforms);
            return $this->getSubForm($keys[$removedPosition]);
        } else {
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
    protected function addAnchor($subform)
    {
        $subform->addDecorator(
            ['currentAnchor' => 'HtmlTag'],
            ['tag' => 'a', 'placement' => 'prepend', 'name' => 'current']
        );
    }

    /**
     * Validiere TitleMain eingaben im Formular.
     *
     * Zusätzlich zu den normalen Validierungen für Formularelemente wird geprüft, ob eine Sprache zweimal ausgewählt
     * wurde.
     *
     * @param array      $data
     * @param array|null $context
     * @return bool
     */
    public function isValid($data, $context = null)
    {
        // wird immer aufgerufen um gegebenenfalls weitere Nachrichten anzuzeigen
        $result = true;

        if ($this->subformValidator !== null) {
            if (array_key_exists($this->getName(), $data)) {
                $this->subformValidator->prepareValidation($this, $data[$this->getName()], $context);
                $result = $this->subformValidator->isValid($data[$this->getName()], $context);
            }
        }

        return $result && parent::isValid($data);
    }

    /**
     * Ermittelt, ob das Formular leer ist.
     *
     * Das Formular ist leer, wenn es keine Unterformulare gibt, als keine Modelle angezeigt werden (z.B. Identifier).
     *
     * @return bool TRUE - wenn keine Unterformulare
     */
    public function isEmpty()
    {
        return count($this->getSubForms()) === 0;
    }

    /**
     * @param array $columns
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        $columns = $this->columns;

        if ($columns !== null && ! $this->isViewModeEnabled()) {
            $columns[] = ['class' => 'Remove']; // Extra Spalte für Remove-Button
        }

        return $columns;
    }

    public function setOptions(array $options)
    {
        if (isset($options['columns'])) {
            $this->setColumns($options['columns']);
            unset($options['columns']);
        }

        parent::setOptions($options);
    }

    /**
     * @return bool
     */
    public function isRenderAsTableEnabled()
    {
        return $this->renderAsTableEnabled;
    }
}
