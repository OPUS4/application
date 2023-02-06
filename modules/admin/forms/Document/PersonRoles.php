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

/**
 * Unterformular fuer die Buttons, um die Rolle einer Person zu ändern.
 */
class Admin_Form_Document_PersonRoles extends Admin_Form_AbstractDocumentSubForm
{
    /**
     * Name fuer Formularelement fuer Feld Role.
     */
    public const ELEMENT_PREFIX_ROLE = 'Role';

    /**
     * Konstante für das Ändern der Rolle für eine Person.
     */
    public const RESULT_CHANGE_ROLE = 'changeRole';

    /**
     * Mögliche Rollen für eine Person.
     *
     * @var array
     *
     * TODO centralize
     */
    private $personRoles = [
        'author'      => 'author',
        'editor'      => 'editor',
        'translator'  => 'translator',
        'contributor' => 'contributor',
        'other'       => 'other',
        'advisor'     => 'advisor',
        'referee'     => 'referee',
        'submitter'   => 'submitter',
    ];

    /**
     * Konstruiert Formular zum Ändern der Rolle einer Person.
     *
     * @param null|string $role Name der aktuellen Rolle
     * @param null|mixed  $options
     */
    public function __construct($role = null, $options = null)
    {
        if ($role !== null && isset($this->personRoles[$role])) {
            unset($this->personRoles[$role]);
        }

        parent::__construct($options);
    }

    /**
     * Erzeugt Buttons für sämtliche Rollen und kümmert sich um Dekoratoren.
     */
    public function init()
    {
        parent::init();

        $roles = $this->personRoles;

        $this->setDecorators(
            [
                'FormElements',
                ['HtmlTag', ['tag' => 'ul', 'class' => 'links']],
            ]
        );

        foreach ($roles as $role) {
            $this->addElement(
                'submit',
                $this->getRoleElementName($role),
                [
                    'decorators' => ['ViewHelper', ['HtmlTag', ['tag' => 'li']]],
                    'label'      => 'Opus_Person_Role_Value_' . ucfirst($role),
                ]
            );
        }
    }

    /**
     * Prüft ob in einem POST einer der Rollen-Buttons geklickt wurde.
     *
     * @param array $post POST Daten für Formular
     * @param array $context POST Daten für gesamtes Formular
     * @return array|null
     */
    public function processPost($post, $context)
    {
        // Prüfen, ob Button für Rollenänderung ausgewählt wurde
        foreach ($this->personRoles as $role) {
            if (array_key_exists($this->getRoleElementName($role), $post)) {
                return [
                    'result' => self::RESULT_CHANGE_ROLE,
                    'role'   => $role,
                ];
            }
        }

        return null;
    }

    /**
     * Liefert Namen des Elements für eine Rolle.
     *
     * @param string $role
     * @return string
     */
    public function getRoleElementName($role)
    {
        return self::ELEMENT_PREFIX_ROLE . ucfirst($role ?? '');
    }
}
