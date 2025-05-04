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
 * Unterformular für die Personen in einer bestimmten Role für ein Dokument.
 */
class Admin_Form_Document_PersonRole extends Admin_Form_Document_DefaultMultiSubForm
{
    /**
     * Name fuer Button um Person hinzuzufuegen.
     */
    public const ELEMENT_ADD = 'Add';

    /**
     * Name der Rolle fuer Personen im Unterformular.
     *
     * @var string
     */
    private $roleName;

    /**
     * Konstruiert Unterformular fuer Personen in einer Rolle.
     *
     * @param string     $roleName
     * @param null|mixed $options
     */
    public function __construct($roleName, $options = null)
    {
        $this->roleName = $roleName;

        // __construct ruft init Funktion auf
        parent::__construct('Admin_Form_Document_Person', 'Person' . ucfirst($roleName), null, $options);
    }

    /**
     * Liefert Namen der Rolle fuer dieses Unterformular.
     *
     * @return string
     */
    public function getRoleName()
    {
        return $this->roleName;
    }

    /**
     * Verarbeitet die POST Daten für dieses Formular.
     *
     * @param array      $post
     * @param array|null $context
     * @return string
     */
    public function processPost($post, $context)
    {
        $result = parent::processPost($post, $context);

        if ($result !== null) {
            $action = is_array($result) ? $result['result'] : $result;

            switch ($action) {
                case Admin_Form_Document_PersonMoves::RESULT_MOVE:
                    $move        = $result['move'];
                    $subFormName = $result['subformName'];
                    $this->moveSubForm($subFormName, $move);
                    $result = Admin_Form_Document::RESULT_SHOW;
                    break;
                case Admin_Form_Document::RESULT_SWITCH_TO:
                    // Ergebnis (Edit) mit Rolle anreichern
                    $result['target']['role'] = $this->roleName; // TODO BUG
                    break;
                default:
                    // do nothing
                    break;
            }
        }

        $this->sortSubFormsBySortOrder();

        return $result;
    }

    /**
     * Manipuliert das Unterformular, so daß es bei der Sortierung an der gewünschten Position landet.
     *
     * Nach der POST Verarbeitung werden die Unterformulare sortiert. Wenn eine Person verschoben werden soll, wird
     * vor der Sortierung der SortOrder Wert auf die Zielposition gesetzt, bzw. bei "Down" auf eine Position weiter,
     * und die ursprüngliche Position (Order) wird auf -1 gesetzt.
     *
     * Nach dem ausführen dieser Funktion muss $this->sortSubFormsBySortOrder() ausgeführt werden bevor das Formular
     * verwendet wird, damit alle Unterformulare an der richtigen Position stehen.
     *
     * @param string $subFormName
     * @param string $direction
     */
    protected function moveSubForm($subFormName, $direction)
    {
        $subform = $this->getSubForm($subFormName);

        $position = 0;

        switch ($direction) {
            case 'First':
                // '0' ist bereits Wert für $position
                break;
            case 'Up':
                $position = $subform->getOrder() - 1;
                if ($position < 0) {
                    $position = 0;
                }
                break;
            case 'Down':
                $position     = $subform->getOrder() + 2;
                $subFormCount = count($this->getSubForms());
                if ($position > $subFormCount) {
                    $position = $subFormCount;
                }
                break;
            case 'Last':
                $position = count($this->getSubForms());
                break;
            default:
                // für unbekannte Richtung, verändere garnichts
                return;
        }

        $subform->setOrder(-1);
        $subform->getElement(Admin_Form_Document_Person::ELEMENT_SORT_ORDER)->setValue($position + 1);
    }

    /**
     * Fügt ein Unterformular an der gewünschten Position ein.
     *
     * Nach dem ausführen dieser Funktion muss $this->sortSubFormsBySortOrder() ausgeführt werden bevor das Formular
     * verwendet wird, damit alle Unterformulare an der richtigen Position stehen.
     *
     * @param Zend_Form $subForm Unterformular, daß eingefügt werden soll
     * @param int       $position SortOrder/Position für neues Formular
     */
    protected function insertSubForm($subForm, $position)
    {
        $subFormCount = count($this->getSubForms());

        if ($position > $subFormCount) {
            $position = $subFormCount + 1;
        } elseif ($position < 0) {
            $position = 1;
        }

        $subForm->setOrder(-1);
        $subForm->getElement(Admin_Form_Document_Person::ELEMENT_SORT_ORDER)->setValue($position);
        $this->addSubForm($subForm, $this->getSubFormBaseName() . $subFormCount);
    }

    /**
     * Sortiert die Personen Unterformulare anhand der SortOrder Werte.
     *
     * Es muss ein Unterschied gemacht werden zwischen einem modifizierten SortOrder Wert und einem Wert der gleich der
     * Order des Unterformulars ist ($form->getOrder() === SortOrder-Value). Wenn ich bei einer Person, z.B. der 4-ten,
     * das SortOrder Feld auf 2 setze, heißt das, daß diese Person auf die Position 2 wechseln soll und alle Personen
     * ab dort einen Schritt nach unten rutschen.
     */
    public function sortSubFormsBySortOrder()
    {
        $subforms = $this->getSubForms();

        $digitsOrder     = strlen(count($subforms));
        $maxSortOrder    = $this->getMaxSortOrder($subforms);
        $digitsSortOrder = strlen($maxSortOrder + 1); // damit bei 99 auch 100 noch verarbeitet werden kann

        $sorted = [];

        foreach ($subforms as $name => $subform) {
            $sortKey                     = $this->getSortKey($subform, $maxSortOrder, $digitsSortOrder, $digitsOrder);
            $sorted[$subform->getName()] = $sortKey;
        }

        asort($sorted);

        $pos = 0;

        $subforms = [];

        foreach ($sorted as $name => $order) {
            $subform = $this->getSubForm($name);
            $subform->setOrder($pos);
            $subforms[$this->getSubFormBaseName() . $pos] = $subform;
            $pos++;
        }

        $this->setSubForms($subforms);
    }

    /**
     * Ermittelt den höchsten Wert für SortOrder im Formular.
     *
     * Der Wert wird benötigt um festzustellen wieviele Digits im Sortierschlüssel für den SortOrder-Wert benötigt
     * werden, da der Nutzer auch größere Werte eingeben kann.
     *
     * @param array $subforms
     * @return int Größter gefundener Wert von SortOrder
     */
    public function getMaxSortOrder($subforms)
    {
        $maxSortOrder = 0;
        foreach ($subforms as $subform) {
            $sortOrder = $subform->getElement(Admin_Form_Document_Person::ELEMENT_SORT_ORDER)->getValue();
            if ($sortOrder > $maxSortOrder) {
                $maxSortOrder = $sortOrder;
            }
        }
        return $maxSortOrder;
    }

    /**
     * Konstruiert einen Schlüssel für die Sortierung der Personen Formulare.
     *
     * Der Schlüssel hat folgende Struktur.
     *
     * SORTORDER_MODIFIED_OLDPOSITION
     *
     * Zuerst kommt die gewünscht SortOrder, dann kommt ein Flag, ob die SortOrder modifiziert wurde, also nicht mehr
     * der aktuellen Position entspricht, und zum Schluss kommt die alte Position.
     *
     * Um beliebig viele Unterfomulare oder beliebig große SortOrder Werte zu unterstützen werden diese mit einer
     * festen Länge, gegebenfalls mit 0 aufgefüllt ausgegeben. Die Anzahl der Digits wird übergeben, damit die
     * Berechnung nicht für jeden Schlüssel erfolgen muss.
     *
     * Wenn der SortOrder Wert leer ist wird er auf $maxSortOrder + 1 gesetzt, damit diese Unterformular nach ganz
     * hinten kommen. Das muss bei der Berechnung der Digits berücksichtig werden (99 + 1 = 100).
     *
     * @param Zend_Form $subform
     * @param int       $maxSortOrder
     * @param int       $digitsSortOrder
     * @param int       $digitsOrder
     * @return string
     */
    public function getSortKey($subform, $maxSortOrder, $digitsSortOrder = 2, $digitsOrder = 2)
    {
        $sortOrder = $subform->getElement(Admin_Form_Document_Person::ELEMENT_SORT_ORDER)->getValue();
        $sortOrder = $sortOrder ?? $maxSortOrder + 1;
        $order     = $subform->getOrder() + 1;
        $modified  = $sortOrder === $order ? 1 : 0; // NICHT MODIFIZIERT (1) : MODIFIZIERT (0)
        return sprintf('%1$0' . $digitsSortOrder . 'd_%2$d_%3$0' . $digitsOrder . 'd', $sortOrder, $modified, $order);
    }

    /**
     * Überschreibt updateModel damit vorher die SortOrder berücksichtigt werden kann.
     *
     * @param DocumentInterface $document
     */
    public function updateModel($document)
    {
        $this->sortSubFormsBySortOrder();
        parent::updateModel($document);
    }

    /**
     * Verarbeitet Klick auf Add Button für Personen.
     *
     * Die Funktion von Admin_Form_Document_MultiSubForm wurde überschrieben, damit ein anderes Ergebnis an den
     * Controller weitergegeben werden kann und ein Wechsel auf eine andere Seite erfolgt, um eine Person hinzufügen
     * zu können.
     *
     * @return array
     */
    protected function processPostAdd()
    {
        // Hinzufuegen wurde ausgewaehlt
        return [
            'result' => Admin_Form_Document::RESULT_SWITCH_TO,
            'target' => [
                'module'     => 'admin',
                'controller' => 'person',
                'action'     => 'assign',
                'role'       => $this->roleName,
            ],
        ];
    }

    /**
     * @param DocumentInterface|null $document
     * @return array
     */
    public function getSubFormModels($document = null)
    {
        $subforms = $this->getSubForms();

        $persons = [];

        foreach ($subforms as $name => $subform) {
            $person    = $subform->getLinkModel($document->getId(), $this->roleName); // TODO should return Link Objekt
            $persons[] = $person;
        }

        return $persons;
    }

    /**
     * Fügt ein Person-SubForm hinzu, daß vorher eine andere Rolle hatte.
     *
     * @param Zend_Form $subForm
     */
    public function addSubFormForPerson($subForm)
    {
        // Unterformular vorbereiten
        $rolesForm = new Admin_Form_Document_PersonRoles($this->roleName);
        $subForm->addSubForm($rolesForm, 'Roles');

        // Unterformular einfügen
        $position = count($this->getSubForms());
        $subForm->setOrder($position);
        $this->setOddEven($subForm);
        $this->addSubForm($subForm, $this->getSubFormBaseName() . $position);
    }

    /**
     * @param Zend_Form $subform
     */
    protected function prepareSubFormDecorators($subform)
    {
        // do nothing
    }

    /**
     * Überschrieben, damit die Unterformular Elemente nicht gruppiert werden.
     *
     * @param Zend_Form $subform
     */
    protected function addRemoveButton($subform)
    {
        $button = $this->createRemoveButton();
        $button->setDecorators(['ViewHelper']);
        $subform->addElement($button);
    }

    /**
     * Erzeugt neues Unterformular für eine Person.
     *
     * @return Admin_Form_Document_Person
     */
    public function createNewSubFormInstance()
    {
        $subform = new Admin_Form_Document_Person();

        $rolesForm = new Admin_Form_Document_PersonRoles($this->roleName);
        $subform->addSubForm($rolesForm, 'Roles');

        $movesForm = new Admin_Form_Document_PersonMoves();
        $subform->addSubForm($movesForm, 'Moves');

        return $subform;
    }

    /**
     * Fügt ein Unterformular für eine Person hinzu.
     *
     * Die notwendigen Informationen werden in einem Array übergeben. Dieses Array kommt von Informationen, die im
     * Formular für das Hinzufügen von Personen zu einem Dokument gesammelt wurden.
     *
     * @param array $personProps
     */
    public function addPerson($personProps)
    {
        if (! array_key_exists('person', $personProps)) {
            $this->getLogger()->err(__METHOD__ . " Attempt to add person without ID.");
            return;
        }

        $personId = (int) $personProps['person'];

        if ($this->getSubFormForPerson($personId) === null) {
            $allowContact = array_key_exists('contact', $personProps) ? $personProps['contact'] : 0;
            $sortOrder    = array_key_exists('order', $personProps) ? $personProps['order'] : null;
            $sortOrder    = $sortOrder ?? count($this->getSubForms()) + 1;

            $form = $this->createSubForm();

            $form->getElement(Admin_Form_Person::ELEMENT_PERSON_ID)->setValue($personId);
            $form->getElement(Admin_Form_Document_Person::ELEMENT_ROLE)->setValue($this->roleName);
            $form->getElement(Admin_Form_Document_Person::ELEMENT_ALLOW_CONTACT)->setValue($allowContact);

            $this->insertSubForm($form, $sortOrder);

            $this->sortSubFormsBySortOrder();
        }
    }

    /**
     * Liefert das Unterformular für eine bestimmte Person-ID.
     *
     * Wird verwendet, um das doppelte zuweisen einer Person in der selben Rolle zu verhindern.
     *
     * @param int $personId ID für Person
     * @return Zend_Form|null oder Unterformular mit Person-ID
     */
    public function getSubFormForPerson($personId)
    {
        foreach ($this->getSubForms() as $subform) {
            if ($personId === (int) $subform->getElementValue('PersonId')) {
                return $subform;
            }
        }

        return null;
    }

    /**
     * Prüft, ob die POST Daten ein gültiges Unterformular für eine Person repräsentieren.
     *
     * Ein Unterformular für eine Person muss immer das Feld PersonId enthalten. Fehlt es wurde wahrscheinlich der POST
     * manipuliert. Auf jeden Fall kann kein Unterformular hinzugefügt werden.
     *
     * @param array $post
     * @return bool
     */
    public function isValidSubForm($post)
    {
        if (array_key_exists('PersonId', $post)) {
            return true;
        } else {
            return false;
        }
    }
}
