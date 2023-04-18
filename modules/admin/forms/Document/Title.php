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

use Opus\Common\Title;
use Opus\Common\TitleInterface;

/**
 * Unterformular fuer das Editieren von Titeln.
 *
 * Für das Metadaten-Formular wurde vereinbart, daß der Typ eines Titels nicht mehr verändert werden kann.
 */
class Admin_Form_Document_Title extends Admin_Form_AbstractModelSubForm
{
    /**
     * Name von Formularelement fuer ID von Title Objekt.
     */
    public const ELEMENT_ID = 'Id';

    /**
     * Name von Formularelement fuer Titeltyp.
     */
    public const ELEMENT_TYPE = 'Type';

    /**
     * Name von Formularelement fuer Titelsprache.
     */
    public const ELEMENT_LANGUAGE = 'Language';

    /**
     * Name von Formularelement fuer Titeltext.
     */
    public const ELEMENT_VALUE = 'Value';

    /**
     * Erzeugt die Formularelemente.
     */
    public function init()
    {
        parent::init();

        $this->addElement('Hidden', self::ELEMENT_ID);
        $this->addElement('Hidden', self::ELEMENT_TYPE); // Der Typ eines Titels ist nicht editierbar
        $this->addElement('Language', self::ELEMENT_LANGUAGE, ['required' => true]);
        $this->addElement('textarea', self::ELEMENT_VALUE, [
            'required'   => true,
            'rows'       => '4',
            'decorators' => [
                'ViewHelper',
                'Errors',
                'Description',
                'ElementHtmlTag',
                [['dataWrapper' => 'HtmlTagWithId'], ['tag' => 'div', 'class' => 'data-wrapper']],
            ],
        ]);
        $this->getElement(self::ELEMENT_VALUE)->setErrorMessages(['isEmpty' => 'admin_validate_error_notempty']);
    }

    /**
     * Lädt die Decoratoren für das Formular.
     *
     * Der Fieldset Dekorator wird wieder entfernt, so fern vorhanden.
     */
    public function loadDefaultDecorators()
    {
        parent::loadDefaultDecorators();

        $this->removeDecorator('Fieldset');
    }

    /**
     * Initialisiert das Formular mit den Werten im Modell.
     *
     * @param TitleInterface $title
     */
    public function populateFromModel($title)
    {
        $this->getElement(self::ELEMENT_ID)->setValue($title->getId());
        $this->getElement(self::ELEMENT_TYPE)->setValue($title->getType());
        $this->getElement(self::ELEMENT_LANGUAGE)->setValue($title->getLanguage());
        $this->getElement(self::ELEMENT_VALUE)->setValue($title->getValue());
    }

    /**
     * Aktualisiert Modell mit den Werten im Formular.
     *
     * @param TitleInterface $title
     */
    public function updateModel($title)
    {
        $title->setLanguage($this->getElementValue(self::ELEMENT_LANGUAGE));
        $title->setType($this->getElementValue(self::ELEMENT_TYPE));
        $title->setValue($this->getElementValue(self::ELEMENT_VALUE));
    }

    /**
     * Liefert das angezeigte Objekt bzw. eine neue Instanz für Titel die im Formular hinzugefügt wurden.
     *
     * @return TitleInterface
     */
    public function getModel()
    {
        $titleId = $this->getElementValue(self::ELEMENT_ID);

        $title = Title::get($titleId);

        $this->updateModel($title);

        return $title;
    }
}
