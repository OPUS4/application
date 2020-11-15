<?PHP
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
 * @package     View
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2014-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Language;

/**
 *
 * TODO override setLabel for more robust translation
 */
class Application_Form_Element_Language extends Application_Form_Element_Select
{

    private static $_languageList;

    public function init()
    {
        parent::init();

        $this->setLabel($this->getView()->translate($this->getName()));

        $this->setDisableTranslator(true); // languages are already translated

        foreach ($this->getLanguageList() as $index => $language) {
            $this->addMultiOption($index, $language);
        }
    }

    public static function getLanguageList()
    {
        if (is_null(self::$_languageList)) {
            self::initLanguageList();
        }
        return self::$_languageList;
    }

    /**
     * Setup language list.
     *
     * @return void
     */
    public static function initLanguageList()
    {
        $translate = \Zend_Registry::get(Application_Translate::REGISTRY_KEY);
        $languages = [];
        foreach (Language::getAllActiveTable() as $languageRow) {
            $langId = $languageRow['part2_t'];
            $languages[$langId] = $translate->translateLanguage($langId);
        }
        self::$_languageList = $languages;
        \Zend_Registry::set('Available_Languages', $languages);
    }
}
