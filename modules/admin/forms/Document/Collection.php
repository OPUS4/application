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
use Opus\Common\CollectionInterface;

/**
 * Unterformular fuer eine zugewiesene Collection.
 */
class Admin_Form_Document_Collection extends Admin_Form_AbstractDocumentSubForm
{
    /**
     * Name von Formularelement fuer Collection-ID.
     */
    public const ELEMENT_ID = 'Id';

    /**
     * Name von Formularelement fuer das Editieren der Collection-Zuweisung zum Dokument.
     *
     * Ein Klick auf diesen Button zeigt die zugewiesene Collection in der Hierarchy an und erlaubt es dem Nutzer eine
     * andere beliebige Collection auszuwählen. Die alte Zuweisung wird durch die neue ersetzt. Der Use Case für diese
     * Funktion sind fast richtige Zuweisungen durch Einsteller, die vom Bearbeiter korrigiert werden müssen.
     */
    public const ELEMENT_EDIT = 'Edit';

    /**
     * Name von Formularelement fuer das Enfernen der Collection vom Dokument.
     */
    public const ELEMENT_REMOVE = 'Remove';

    /** @var string */
    private $collectionName;

    /**
     * Erzeugt die Formularelemente.
     *
     * TODO disable translation für EDIT Element
     */
    public function init()
    {
        parent::init();

        $this->addElement('hidden', self::ELEMENT_ID);
        $this->addElement('submit', self::ELEMENT_EDIT);
        $this->getElement(self::ELEMENT_EDIT)->setDisableTranslator(true); // Collections are translated manually
        $this->addElement('submit', self::ELEMENT_REMOVE, ['label' => 'admin_button_remove']);
    }

    /**
     * Initialisiert das Formular mit einer Collection.
     *
     * @param CollectionInterface $collection
     */
    public function populateFromModel($collection)
    {
        $this->getElement(self::ELEMENT_ID)->setValue($collection->getId());
        $displayName = $this->getDisplayNameForCollection($collection);
        $this->getElement(self::ELEMENT_EDIT)->setLabel($displayName);
        $this->setLegend($displayName);
    }

    /**
     * Ermittelt Anzeigenamen fuer Sammlung.
     *
     * Root-Collections haben keinen Namen. In diesem Fall wird der Name der CollectionRole angezeigt. Da Collections
     * normalerweise nicht übersetzt werden, muss der Name der CollectionRole hier separate übersetzt werden.
     *
     * @param CollectionInterface $collection
     * @return string
     */
    protected function getDisplayNameForCollection($collection)
    {
        $displayName = $collection->getDisplayName();
        if (($displayName === null || strlen(trim($displayName)) === 0) && $collection->isRoot()) {
            $translator     = $this->getTranslator();
            $translationKey = 'default_collection_role_' . $collection->getRoleName();
            if ($translator->isTranslated($translationKey)) {
                $displayName = $translator->translate($translationKey);
            } else {
                $displayName = $collection->getRoleName();
            }
        }
        return $displayName;
    }

    /**
     * Verarbeitet einen POST Request für das Formular.
     *
     * @param array $data POST Daten für Unterformular
     * @param array $context POST Daten für gesamtes Metadaten-Formular
     * @return string|null Ergebnis der Verarbeitung oder NULL
     */
    public function processPost($data, $context)
    {
        if (array_key_exists(self::ELEMENT_REMOVE, $data)) {
            return 'remove';
        } elseif (array_key_exists(self::ELEMENT_EDIT, $data)) {
            // TODO edit collection (neue zuweisen, alte entfernen)
            // TODO Seitenwechel, POST sichern, Return value
            return 'edit';
        }

        return null;
    }

    /**
     * Liefert das Model für die angezeigte Collection.
     *
     * @return Collection
     */
    public function getModel()
    {
        $colId = $this->getElement(self::ELEMENT_ID)->getValue();

        return Collection::get($colId);
    }

    /**
     * Initialisiert das Formular basierend auf POST Daten.
     *
     * Der POST enthält nur die ID der Collection, damit der Name im Formular angezeigt werden kann,
     * muss die Collection instanziert werden.
     *
     * @param array $post
     *
     * TODO catch bad POST
     * TODO catch unknown Collection
     */
    public function populateFromPost($post)
    {
        $colId      = $post[self::ELEMENT_ID];
        $collection = Collection::get($colId);
        $this->populateFromModel($collection);
    }

    /**
     * Setzt die Decoratoren für das Formular.
     */
    public function loadDefaultDecorators()
    {
        $this->setDecorators(
            [
                'PrepareElements',
                ['ViewScript', ['viewScript' => 'form/collectionForm.phtml']],
                [['multiWrapper' => 'HtmlTag'], ['class' => 'multiple-wrapper']],
            ]
        );
    }

    /**
     * Überschreibt Funktion zum Entfernen aller Formularelemente für die Metadaten-Übersicht, um den Namen der
     * Collection im Formular zu speichern.
     */
    public function removeElements()
    {
        $this->collectionName = $this->getElement(self::ELEMENT_EDIT)->getLabel();
        parent::removeElements();
    }

    /**
     * Liefert den Namen der angezeigten Collection.
     *
     * @return string
     */
    public function getCollectionName()
    {
        return $this->collectionName;
    }
}
