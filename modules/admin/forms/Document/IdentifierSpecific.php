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
 * @copyright   Copyright (c) 2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Identifier;
use Opus\Common\IdentifierInterface;
use Opus\Common\Model\NotFoundException;

class Admin_Form_Document_IdentifierSpecific extends Admin_Form_AbstractModelSubForm
{
    /**
     * Name für Formularelement Input-Field Identifier-Wert
     */
    public const ELEMENT_VALUE = 'Value';

    /**
     * Name für Formularelement Input-Field Identifier-Id
     */
    public const ELEMENT_ID = 'IdentifierId';

    /**
     * Name für Formelement, das Dokument-ID enthält (wird für die Überprüfung
     * der Eindeutigkeit von Identifier-Werten benötigt).
     *
     * TODO BUG this is a hidden dependency on a different form class - Use central constant?
     */
    public const ELEMENT_DOC_ID = 'DocId';

    /** @var string Type of identifier TODO BUG never written */
    private $type;

    /**
     * Erzeugt Elemente für Identifier Formular.
     */
    public function init()
    {
        parent::init();

        $this->addElement(
            'text',
            self::ELEMENT_VALUE,
            [
                'label' => $this->type,
                'size'  => '80',
            ]
        );
        $this->addElement('hidden', self::ELEMENT_ID);
        $this->addElement('hidden', self::ELEMENT_DOC_ID);
    }

    /**
     * Befüllt Formularelemente aus Identifier Instanz.
     *
     * @param Identifier $identifier
     */
    public function populateFromModel($identifier)
    {
        $this->getElement(self::ELEMENT_VALUE)->setValue($identifier->getValue());
        $this->getElement(self::ELEMENT_ID)->setValue($identifier->getId());
        $this->getElement(self::ELEMENT_DOC_ID)->setValue($identifier->getParentId());
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->getElement(self::ELEMENT_VALUE)->setValue($value);
    }

    /**
     * Aktualisiert Identifier Instanz aus Formularelementen.
     *
     * @param IdentifierInterface $identifier
     */
    public function updateModel($identifier)
    {
        $value = $this->getElement(self::ELEMENT_VALUE)->getValue();
        $identifier->setValue($value);
    }

    /**
     * @return IdentifierInterface
     * @throws Zend_Exception
     */
    public function getModel()
    {
        $modelId = $this->getElement(self::ELEMENT_ID)->getValue();

        $identifier = null;

        if ($modelId !== null && strlen(trim($modelId)) > 0) {
            try {
                $identifier = Identifier::get($modelId);
            } catch (NotFoundException $omnfe) {
                $this->getLogger()->err(__METHOD__ . " Unknown identifier ID = '$modelId'.");
            }
        }

        if ($identifier === null) {
            $identifier = Identifier::new();
            $identifier->setType($this->type);
        }

        $this->updateModel($identifier);

        return $identifier;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
