<?php
/*
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
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Unterformular fuer die Buttons, um die Rolle einer Person zu ändern.
 */
class Admin_Form_Document_PersonMoves extends Admin_Form_AbstractDocumentSubForm
{

    /**
     * Konstante für Person an erster Stelle.
     */
    const POSITION_FIRST = 'first';

    /**
     * Konstante für Person an letzter Stelle.
     */
    const POSITION_LAST = 'last';

    /**
     * Konstante für Person in mittlerer Position.
     */
    const POSITION_DEFAULT = null;

    /**
     * Konstante für POST Ergebnis das signalisiert, daß Person verschoben werden soll.
     */
    const RESULT_MOVE = 'move';

    /**
     * Mögliche Rollen für eine Person.
     * @var array
     *
     * TODO centralize
     */
    private $_moves;

    /**
     * Flag für spezielle Position, erste oder letzte Stelle.
     * @var string
     */
    private $_position;

    /**
     * Konstruiert Formular.
     * @param string $position Parameter für besondere Position, z.B. erste oder letzte Stelle
     * @param mixed $options
     */
    public function __construct($position = null, $options = null)
    {
        $this->_position = $position;
        parent::__construct($options);
    }

    /**
     * Erzeugt Buttons für sämtliche Rollen und kümmert sich um Dekoratoren.
     */
    public function init()
    {
        parent::init();

        $this->setDecorators(
            [
            'FormElements',
            ['HtmlTag', ['tag' => 'ul', 'class' => 'links']]
            ]
        );

        $this->createButtons();
    }

    private function createButtons()
    {
        switch ($this->_position) {
            case self::POSITION_FIRST:
                $this->_moves = ['Down', 'Last'];
                break;
            case self::POSITION_LAST:
                $this->_moves = ['First', 'Up'];
                break;
            default:
                $this->_moves = ['First', 'Up', 'Down', 'Last'];
                break;
        }

        foreach ($this->_moves as $move) {
            $lower = strtolower($move);
            $this->addElement(
                'submit',
                $move,
                [
                'decorators' => ['ViewHelper',
                    ['HtmlTag', ['tag' => 'li', 'class' => 'move-' . $lower]]],
                'label' => 'admin_button_move_' . $lower
                ]
            );
        }
    }

    public function changePosition($position)
    {
        if ($this->_position !== $position) {
            $this->_position = $position;

            $this->clearElements();
            $this->createButtons();
        }
    }

    /**
     * Prüft ob in einem POST einer der Rollen-Buttons geklickt wurde.
     * @param array $post POST Daten für Formular
     * @param array $context POST Daten für gesamtes Formular
     * @return array
     */
    public function processPost($post, $context)
    {
        // Prüfen, ob Button für Rollenänderung ausgewählt wurde
        foreach ($this->_moves as $move) {
            if (array_key_exists($move, $post)) {
                return [
                    'result' => self::RESULT_MOVE,
                    'move' => $move
                ];
            }
        }

        return null;
    }
}
