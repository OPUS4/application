<?php

/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the North Rhine-Westphalian Library Service Center,
 * the Cooperative Library Network Berlin-Brandenburg, the Saarland University
 * and State Library, the Saxon State Library - Dresden State and University
 * Library, the Bielefeld University Library and the University Library of
 * Hamburg University of Technology with funding from the German Research
 * Foundation and the European Regional Development Fund.
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
 * @copyright  Copyright (c) 2009, OPUS 4 development team
 * @license    http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Builds the language selection form.
 */
class Application_View_Helper_LanguageSelector extends Zend_View_Helper_Abstract
{
    /**
     * Get an instance of the view helper.
     *
     * @return array|null
     */
    public function languageSelector()
    {
        if (isset($this->view->languageSelectorDisabled) && $this->view->languageSelectorDisabled === true) {
            return null;
        }
        $returnParams = Zend_Controller_Action_HelperBroker::getStaticHelper('ReturnParams');

        $currentLocale = new Zend_Locale(Application_Translate::getInstance()->getLocale());

        $configHelper = new Application_Configuration();

        // only show languages that are present in resources and activated in configuration
        $translations  = Application_Translate::getInstance()->getList();
        $supportedLang = $configHelper->getActivatedLanguages();
        $translations  = array_intersect($translations, $supportedLang);

        $result = [];
        foreach ($translations as $translation) {
            if ($currentLocale->getLanguage() !== $translation) {
                $languageName = $currentLocale->getTranslation($translation, 'language', $translation);
                $languageUrl  = $this->view->url(
                    array_merge(
                        [
                            'action'     => 'language',
                            'controller' => 'index',
                            'module'     => 'home',
                            'language'   => $translation,
                        ],
                        $returnParams->getReturnParameters()
                    ),
                    null,
                    true
                );
                array_push($result, ['name' => htmlspecialchars($languageName), 'url' => $languageUrl]);
            }
        }

        return $result;
    }
}
