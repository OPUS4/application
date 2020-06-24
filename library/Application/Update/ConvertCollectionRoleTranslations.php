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
 * @category    Application
 * @package     Application_Update
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Handles update of "speaking" collection role names.
 *
 * Checks if collection role names are translated. If not creates translation keys and
 * converts names into translations. A name is replaced by a generated name that does not contain special characters.
 */
class Application_Update_ConvertCollectionRoleTranslations extends Application_Update_PluginAbstract
{

    public function run()
    {
        $this->log('Validation of collection role names starting ...');

        $manager = new Application_Translate_TranslationManager();

        $validator = new Application_Form_Validate_CollectionRoleName();

        $roles = Opus_CollectionRole::fetchAll();

        $colors = new Opus_Util_ConsoleColors();

        foreach ($roles as $role) {
            $name = $role->getName();

            if (! $validator->isValid($name)) {
                $roleId = $role->getId();
                $this->log($colors->red("Collection role (id = $roleId) name is invalid (\"$name\")"));

                // create translation values using invalid collection role name
                $supportedLanguages = Application_Configuration::getInstance()->getSupportedLanguages();
                $translations = [];
                foreach($supportedLanguages as $lang) {
                    $translations[$lang] = $name;
                }

                $translationKey = "default_collection_role_$name";

                // use OaiName as new name for collection role
                $name = $role->getOaiName();
                $role->setName($name);
                $this->log("  Change collection role name to OaiName '$name'");

                try {
                    $translation = $manager->getTranslation($translationKey);

                    if (! is_null($translation)) {
                        /* TODO this should not happen, because any invalid name would not be a valid translation key
                         *      either
                         */
                        $this->log($colors->red(
                            "  Translation key '{$colors->blue($translationKey)}' exists" .
                            ' (will not be removed automatically)'
                        ));
                    }
                } catch (\Opus\Translate\UnknownTranslationKey $ex) {
                    $this->log("  Translation key '{$colors->blue($translationKey)}' does not exist");
                }

                // store translation
                $translationKey = "default_collection_role_$name";
                $manager->setTranslation($translationKey, $translations, 'default');
                $this->log("  Storing old name as translation under '{$colors->blue($translationKey)}'");

                $role->store();
            }
        }

        $this->log('Validation of collection role names finished');
    }
}
