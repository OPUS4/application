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

use Opus\Common\Collection;
use Opus\Common\DocumentInterface;

/**
 * Subform fuer Collections im Metadaten-Formular.
 *
 * Dieses Formular zeigt die dem Dokument zugewiesenen Collections an. Jede Collection erhält einen "Entfernen" Button
 * um die Zuweisung zu löschen. Außerdem gibt es einen Submit Button der den Nutzer zur Seite für das Zuweisen einer
 * weiteren Collection bringt.
 *
 * Für jede CollectionRole wird ein Zend_Form_SubForm angelegt. Diesem wiederum wird für jede zugehörige Collection
 * ein Admin_Form_Document_Collection Unterformular hinzugefügt. Dadurch entsteht eine Hierarchy für die Anzeige und
 * POST Verarbeitung.
 *
 * <pre>
 * Admin_Form_Document_Collections
 *   +-Zend_Form_SubForm
 *     +-Admin_Form_Document_Collection
 * </pre>
 *
 * Wenn eine neue Collection zugewiesen werden soll, muß dem Controller signalisiert werden, das der aktuelle POST in
 * der Session gespeichert werden muß und eine neue URL (zum Zuweisen der Collection) angesprungen werden soll.
 *
 * TODO eliminiere redundanten Code fuer CollectionRole SubForm (separate Klasse?) (vergl. mit MultiSubForm Klasse)
 */
class Admin_Form_Document_Collections extends Admin_Form_AbstractDocumentSubForm
{
    /**
     * Name für Button zum Hinzufügen von Collections.
     */
    public const ELEMENT_ADD = 'Add';

    /**
     * Initialisiert Elemente für gesamtes Collections Formular.
     */
    public function init()
    {
        parent::init();

        $this->addElement('submit', self::ELEMENT_ADD, [
            'order'                        => 1000,
            'label'                        => 'admin_button_add',
            'decorators'                   => [],
            'disableLoadDefaultDecorators' => true,
        ]);
        $this->setLegend('admin_document_section_collection');

        $this->getDecorator('FieldsetWithButtons')->setLegendButtons(self::ELEMENT_ADD);
    }

    /**
     * Erzeugt und initialisiert Unterformulare entsprechend den Collections eines Dokuments.
     *
     * @param DocumentInterface $document
     */
    public function populateFromModel($document)
    {
        $this->clearSubForms();

        $collectionRoles = $this->getGroupedCollections($document);

        // Iteriere über CollectionRole Namen für Dokument und erzeuge Unterformulare
        foreach ($collectionRoles as $roleName => $collections) {
            $roleForm = new Admin_Form_Document_Section();

            $roleForm->setLegend('default_collection_role_' . $roleName);

            $position = 0;

            // Iteriere über Collections für CollectionRole und erzeuge Unterformulare
            foreach ($collections as $index => $collection) {
                $collectionForm = $this->createCollectionForm($position++);
                $collectionForm->populateFromModel($collection);
                $roleForm->addSubForm($collectionForm, 'collection' . $index);
            }

            $this->addSubForm($roleForm, $this->normalizeName($roleName));
        }
    }

    /**
     * @param string $name
     * @return string
     */
    public function normalizeName($name)
    {
        return str_replace('-', '', $name);
    }

    /**
     * @param array $data
     * @param array $context
     * @return array|null
     */
    public function processPost($data, $context)
    {
        if (array_key_exists(self::ELEMENT_ADD, $data)) {
            // Neue Sammlung zuweisen
            return [
                'result' => Admin_Form_Document::RESULT_SWITCH_TO,
                'target' => [
                    'module'     => 'admin',
                    'controller' => 'collection',
                    'action'     => 'assign',
                ],
            ];
        } else {
            // POST Verarbeitung der Unterformular
            foreach ($data as $roleName => $collections) {
                $roleForm = $this->getSubForm($this->normalizeName($roleName));

                if ($roleForm !== null) {
                    foreach ($collections as $key => $collection) {
                        $colForm = $roleForm->getSubForm($key);

                        if ($colForm !== null) {
                            $result = $colForm->processPost($collection, $context);

                            if ($result === 'remove') {
                                $this->removeCollection($roleForm, $colForm);
                            }
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * @param Zend_Form $roleForm
     * @param Zend_Form $colForm
     */
    protected function removeCollection($roleForm, $colForm)
    {
        $roleForm->removeSubForm($colForm->getName());
        if (count($roleForm->getSubForms()) === 0) {
            $this->removeSubForm($roleForm->getName());
        } else {
            $roleForm->removeGapsInSubFormOrder('collection');
        }
    }

    /**
     * Erzeugt Unterformulare basierend auf den Informationen in den POST Daten.
     *
     * @param array                  $post
     * @param DocumentInterface|null $document
     */
    public function constructFromPost($post, $document = null)
    {
        foreach ($post as $roleName => $data) {
            // Prüfen ob Unterformluar (array) oder Feld
            if (is_array($data)) {
                $this->addCollectionSubForm($roleName, $data);
            }
        }
    }

    /**
     * Aktualisiert die Liste der zugewiesenen Collections für ein Dokument.
     *
     * Diese Funktion iteriert über alle Unterformulare und fragt die Collections ab. Die Collections werden in einem
     * Array gesammelt und dann dem Dokument zugewiesen.
     *
     * @param DocumentInterface $document
     */
    public function updateModel($document)
    {
        $roleForms = $this->getSubForms();

        $values = [];

        foreach ($roleForms as $roleForm) {
            $colForms = $roleForm->getSubForms();

            foreach ($colForms as $colForm) {
                $value = $colForm->getModel();

                if ($value !== null) {
                    $values[] = $value;
                }
            }
        }

        $field = $document->getField('Collection');

        $field->setValue($values);
    }

    /**
     * @param Zend_Controller_Request_Http $request
     * @param Zend_Session_Namespace|null  $session
     */
    public function continueEdit($request, $session = null)
    {
        if ($request->getParam('continue', null) === 'addcol') {
            $colId = $request->getParam('colId');

            $this->addCollection($colId);
        }
    }

    /**
     * Fügt Unterformular für eine Collection hinzu.
     *
     * @param string $roleName
     * @param array  $data
     *
     * TODO Sollte roleForm nur bei Bedarf hinzufügen.
     */
    protected function addCollectionSubForm($roleName, $data)
    {
        $roleForm = new Admin_Form_Document_Section();

        $roleForm->setLegend('default_collection_role_' . $roleName);

        $position = 0;

        foreach ($data as $index => $collection) {
            $collectionForm = $this->createCollectionForm($position++);
            $collectionForm->populateFromPost($collection);
            $roleForm->addSubForm($collectionForm, $index);
        }

        $this->addSubForm($roleForm, $this->normalizeName($roleName));
    }

    /**
     * Adds a collection to the form.
     *
     * @param int $colId
     */
    protected function addCollection($colId)
    {
        $collection     = Collection::get($colId);
        $collectionRole = $collection->getRole();
        $roleName       = $collectionRole->getName();

        $roleForm = $this->getRoleForm($roleName);

        $collectionForm = new Admin_Form_Document_Collection();
        $collectionForm->populateFromModel($collection);

        $position = count($roleForm->getSubForms());

        $roleForm->addSubForm($collectionForm, 'collection' . $position);
        $roleForm->removeGapsInSubFormOrder('collection');
    }

    /**
     * @param string $roleName
     * @return Admin_Form_Document_Section
     */
    protected function getRoleForm($roleName)
    {
        $roleForm = $this->getSubForm($this->normalizeName($roleName));

        if ($roleForm === null) {
            $roleForm = new Admin_Form_Document_Section();

            $roleForm->setLegend('default_collection_role_' . $roleName);

            $this->addSubForm($roleForm, $this->normalizeName($roleName));
        }

        return $roleForm;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return count($this->getSubForms()) === 0;
    }

    /**
     * @param int $position
     * @return Admin_Form_Document_Collection
     */
    public function createCollectionForm($position)
    {
        $subform = new Admin_Form_Document_Collection();

        $multiWrapper = $subform->getDecorator('multiWrapper');

        if ($multiWrapper !== null && $multiWrapper instanceof Zend_Form_Decorator_HtmlTag) {
            $multiClass  = $multiWrapper->getOption('class');
            $multiClass .= $position % 2 === 0 ? ' even' : ' odd';
            $multiWrapper->setOption('class', $multiClass);
        }

        return $subform;
    }

    /**
     * Returns the collections grouped by CollectionRole.
     *
     * @param DocumentInterface $document
     * @return array Collections grouped by CollectionRole
     */
    public function getGroupedCollections($document)
    {
        $groupedCollections = [];

        foreach ($document->getCollection() as $collection) {
            $roleName = $collection->getRoleName();

            if (! isset($groupedCollections[$roleName])) {
                $groupedCollections[$roleName] = [];
            }

            $collections = $groupedCollections[$roleName];

            $collections[] = $collection;

            $groupedCollections[$roleName] = $collections;
        }

        return $groupedCollections;
    }
}
