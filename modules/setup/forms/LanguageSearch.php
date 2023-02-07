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

/**
 * Form for search translations and translation keys.
 *
 * TODO reduce vertical height of form ("Search: TEXT Button")
 *
 * TODO add option for search keys/content only
 * TODO add option for filtering by module
 */
class Setup_Form_LanguageSearch extends Application_Form_Abstract
{
    /**
     * Input field for search string for translations.
     */
    public const ELEMENT_FILTER = 'search';

    /**
     * Select for modules included in search.
     */
    public const ELEMENT_MODULES = 'modules';

    /**
     * Select for state of translations (All, Edited, Added).
     */
    public const ELEMENT_STATE = 'state';

    /**
     * Select for scope of search (keys, translations)
     */
    public const ELEMENT_SCOPE = 'scope';

    /**
     * Button for starting search.
     */
    public const ELEMENT_SUBMIT = 'show';

    /**
     * TODO not supported yet (keys are always included) - make keys optional
     */
    public const ELEMENT_INCLUDE_KEYS = 'SearchKeys';

    public function init()
    {
        parent::init();

        $this->setElementDecorators(['ViewHelper']);

        $element = $this->createElement('text', self::ELEMENT_FILTER, [
            'size' => '40',
        ]);
        $this->addElement($element);

        $element = $this->createElement('submit', self::ELEMENT_SUBMIT, [
            'label' => 'setup_translation_search_button',
        ]);
        $this->addElement($element);

        $element = $this->createElement('TranslationState', self::ELEMENT_STATE);
        $this->addElement($element);

        $element = $this->createElement('TranslationScope', self::ELEMENT_SCOPE);
        $this->addElement($element);

        $element = $this->createElement('TranslationModules', self::ELEMENT_MODULES);
        $this->addElement($element);

        $this->setDecorators([
            ['ViewScript', ['viewScript' => 'languagesearch.phtml']],
            'Form',
        ]);
    }

    /**
     * @param Zend_Controller_Request_Http $request
     *
     * TODO should this code go somewhere else (add responsibility to class)
     */
    public function populateFromRequest($request)
    {
        $module = $request->getParam('modules', null);
        if ($module !== null) {
            $module = strtolower($module);
        }

        $scope = $request->getParam('scope', null);
        if ($scope !== null) {
            $scope = strtolower($scope);
        }

        $state = $request->getParam('state', null);
        if ($state !== null) {
            $state = strtolower($state);
        }

        $this->getElement(self::ELEMENT_MODULES)->setValue($module);
        $this->getElement(self::ELEMENT_SCOPE)->setValue($scope);
        $this->getElement(self::ELEMENT_STATE)->setValue($state);
    }
}
