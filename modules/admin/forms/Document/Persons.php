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
 * Unterformular fuer die mit einem Dokument verknuepften Personen.
 */
class Admin_Form_Document_Persons extends Admin_Form_AbstractDocumentSubForm
{
    /**
     * Button, um die Sortierung der Personen auszulösen nachdem die SortOrder Werte editiert wurden.
     *
     * Der Button muss nicht verwendet werden, dient aber dazu dem Nutzer eine Möglichkeit zu geben das Ergebnis der
     * Sortierung zu überprüfen, bevor das Dokument gespeichert wird.
     */
    public const ELEMENT_SORT = 'Sort';

    /**
     * Button zum Hinzufügen einer Person zum Dokument.
     */
    public const ELEMENT_ADD = 'Add';

    /**
     * Bestimmt die Reihenfolge der Sektionen für die einzelnen Rollen.
     *
     * @var array
     */
    private static $personRoles = [
        'author',
        'editor',
        'translator',
        'contributor',
        'other',
        'advisor',
        'referee',
        'submitter',
    ];

    /**
     * Erzeugt Unterformular für Personen.
     *
     * Für jede mögliche Rolle wird ein Unterformular angelegt.
     */
    public function init()
    {
        parent::init();

        $this->setLegend('admin_document_section_persons');

        $this->addElement(
            'submit',
            'Sort',
            [
                'label'                        => 'admin_button_sort',
                'decorators'                   => [],
                'disableLoadDefaultDecorators' => true,
            ]
        );

        $this->getDecorator('FieldsetWithButtons')->setLegendButtons(['Sort']);

        foreach (self::$personRoles as $roleName) {
            $subform = new Admin_Form_Document_PersonRole($roleName);
            $this->addSubForm($subform, $roleName);
        }
    }

    /**
     * @param DocumentInterface $document
     */
    public function populateFromModel($document)
    {
        $subforms = $this->getSubForms();

        foreach ($subforms as $subform) {
            $subform->populateFromModel($document);
        }
    }

    /**
     * Konstruiert Formular basierend auf POST Informationen.
     *
     * Die Teilbereiche des POST werden an die entsprechenden Unterformulare weitergereicht.
     *
     * @param array                  $post
     * @param DocumentInterface|null $document
     */
    public function constructFromPost($post, $document = null)
    {
        foreach ($post as $key => $data) {
            $subform = $this->getSubForm($key);
            if ($subform !== null) {
                $subform->constructFromPost($data, $document);
            }
        }
    }

    /**
     * Verarbeitet einen POST um die notwendigen Aktionen zu ermitteln.
     *
     * @param array $post POST Daten fur dieses Formular
     * @param array $context Komplette POST Daten
     * @return array|string|null
     */
    public function processPost($post, $context)
    {
        foreach ($post as $index => $data) {
            $subform = $this->getSubForm($index);
            if ($subform !== null) {
                $result = $subform->processPost($data, $context);

                if ($result !== null) {
                    $action = is_array($result) ? $result['result'] : $result;

                    switch ($action) {
                        case Admin_Form_Document_PersonRoles::RESULT_CHANGE_ROLE:
                            $role        = $result['role'];
                            $subFormName = $result['subformName'];
                            $personForm  = $subform->getSubForm($subFormName);
                            $subform->removeSubForm($subFormName);
                            $this->getSubForm($role)->addSubFormForPerson($personForm); // TODO Seiteneffekte?
                            $result = Admin_Form_Document::RESULT_SHOW;
                            break;
                        default:
                            // tue nichts für unbekannte Ergebnisse
                            break;
                    }

                    return $result;
                }
            }
        }

        // Wenn 'Sort' Button geklickt wurde, kann die POST verarbeitung nachdem die Unterformulare sortiert wurden
        // hier abgebrochen werden.
        if (array_key_exists(self::ELEMENT_SORT, $post)) {
            return Admin_Form_Document::RESULT_SHOW;
        }

        return null;
    }

    /**
     * Wird nach dem Rücksprung von Add/Edit Seite für Person aufgerufen, um das Ergebnis ins Formular einzubringen.
     *
     * @param Zend_Controller_Request_Http         $request
     * @param null|Admin_Model_DocumentEditSession $session
     */
    public function continueEdit($request, $session = null)
    {
        $addedPersons = $session->retrievePersons();

        if (count($addedPersons) === 0) {
            $action = $request->getParam('continue', null);

            if ($action === 'addperson') {
                $personId = $request->getParam('person', null);

                if ($personId !== null) {
                    $addedPersons[] = [
                        'person'  => $personId,
                        'role'    => $request->getParam('role', 'author'),
                        'contact' => $request->getParam('contact', 'false'),
                        'order'   => $request->getParam('order', null),
                    ];
                } else {
                    $this->getLogger()->err(__METHOD__ . ' Attempt to add person without ID.');
                }
            }
        }

        foreach ($addedPersons as $person) {
            $this->addPerson($person);
        }
    }

    /**
     * Fügt ein neues Unterformular für eine Person zu einem der Rollenunterformulare hinzu.
     *
     * Wenn keine Rolle angegeben wurde, wird 'other' verwendet. Das war eine willkürliche Entscheidung.
     *
     * @param array $personProps
     */
    public function addPerson($personProps)
    {
        $role = $personProps['role'] ?? 'other';

        $subform = $this->getSubFormForRole($role);

        if ($subform !== null) {
            $subform->addPerson($personProps);
        }
    }

    /**
     * Liefert das Unterformular für eine Rolle.
     *
     * @param string $role
     * @return Admin_Form_Document_PersonRole
     */
    public function getSubFormForRole($role)
    {
        return $this->getSubForm($role);
    }

    /**
     * Liefert Array mit vom Datemmodell erlaubten Rollen.
     *
     * @return array
     *
     * TODO wohin?
     */
    public static function getRoles()
    {
        return self::$personRoles;
    }
}
