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

use Opus\Common\Log;

/**
 * Prüft, ob die Sprache bereits benutzt wurde.
 *
 * Dieser Validator wird an ein Language Element gehängt, damit dort auch die entsprechende Meldung erscheint. Für die
 * Validierung wird dieser Validator mit einem Array mit den ausgewählten Sprachen in allen Unterformularen versorgt.
 * Er prüft dann, ob bei den vorher plazierten Formularen die Sprache schon einmal ausgewählt wurde.
 *
 * TODO Redundanz mit DuplicateValue eliminieren
 */
class Application_Form_Validate_LanguageUsedOnceOnly extends Zend_Validate_Abstract
{
    /**
     * Error constant for language ID that does not exist.
     */
    public const NOT_VALID = 'notValid';

    /**
     * Ausgewählte Sprachen in den Unterformularen.
     *
     * @var array
     */
    private $languages;

    /**
     * Position des Formulars im übergeordneten Formular.
     *
     * @var int
     */
    private $position;

    /**
     * Definition der Fehlermeldungen.
     *
     * @var array
     * @phpcs:disable
     */
    protected $_messageTemplates = [
        self::NOT_VALID => 'admin_document_error_MoreThanOneTitleInLanguage',
    ];
    // @phpcs:enable

    /**
     * Konstruiert Validator der prüft, ob Sprache bereits genutzt wurde.
     *
     * Die Reihenfolge der Einträge in $languages entspricht der Reihenfolge der Unterformulare
     * (TitleMain0 bis TitleMain[n]).
     *
     * @param array $languages Ausgewählte Sprachen in den Unterformularen (Titeln gleichen Typs)
     * @param int   $position Position des Unterformulars im Context
     */
    public function __construct($languages, $position)
    {
        $this->languages = $languages;
        $this->position  = $position;
        $this->setTranslator(Application_Translate::getInstance());
    }

    /**
     * Prüft, ob ausgewählte Sprache bereits vorher verwendet wurde.
     *
     * @param string     $value Ausgewählte Sprache
     * @param array|null $context POST Daten für gesamtes Unterformular
     * @return bool true - wenn die Sprache noch nicht verwendet wurde; ansonten false
     */
    public function isValid($value, $context = null)
    {
        $value = (string) $value;
        $this->_setValue($value);

        // TODO WHY
        if ($this->languages === null) {
            return true;
        }

        $langCount = count($this->languages);

        if (! $this->position < $langCount) {
             Log::get()->err(__CLASS__ . ' mit Position > count(Languages) konstruiert.');
        }

        if ($this->languages !== null) {
            for ($index = 0; $index < $this->position && $index < $langCount; $index++) {
                if ($value === $this->languages[$index]) {
                    $this->_error(self::NOT_VALID);
                    return false;
                }
            }
        } else {
             Log::get()->err(__CLASS__ . ' mit Languages = NULL konstruiert.');
        }

        return true;
    }

    /**
     * Liefert Position für Validator.
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Liefert Array mit ausgewählten Sprachen aller Unterformulare.
     *
     * @return array
     */
    public function getLanguages()
    {
        return $this->languages;
    }

    /**
     * Translation is required for error messages, even if validated element is not translated (e.g. Languages).
     *
     * @return false
     */
    public function translatorIsDisabled()
    {
        return false;
    }
}
