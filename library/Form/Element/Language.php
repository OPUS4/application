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
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * 
 */
class Form_Element_Language extends Form_Element_Select {

    private static $languageList;

    public function init() {
        parent::init();
        
        // $this->setLabel($this->getName()); // TODO remove
        // $this->setRequired(true); // TODO remove
        
        $languages = Zend_Registry::get('Available_Languages');
        
        foreach ($languages as $index => $language) {
            $this->addMultiOption($index, $language);
        }

        $this->setDisableTranslator(false); // TODO Check for multiple translations
    }

    public static function getLanguageList() {
        if (is_null(self::$languageList)) {
            self::initLanguageList();
        }
        return self::$languageList;
    }


    /**
     * Setup language list.
     *
     * @return void
     *
     * TODO move out (wird nicht für jeden Request benötigt)
     */
    public static function initLanguageList() {
        $translate = Zend_Registry::get(Application_Translate::REGISTRY_KEY);
        $languages = array();
        foreach (Opus_Language::getAllActiveTable() as $languageRow) {
            $ref_name = $languageRow['ref_name'];
            $part2_t = $languageRow['part2_t'];
            $languages[$part2_t] = $translate->translate($part2_t);
        }
        self::$languageList = $languages;
        Zend_Registry::set('Available_Languages', $languages);
    }
}
