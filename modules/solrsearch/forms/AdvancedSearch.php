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

use Opus\Search\Util\Query;

/**
 * Advanced search form.
 *
 * The decorators have to be modified quite a bit, because the form elements
 * and form classes were developed for the administration.
 *
 * TODO if possible reduce necessary decorating (redundancies)
 * TODO some functionality can still be moved here (validation, etc.)
 */
class Solrsearch_Form_AdvancedSearch extends Application_Form_Abstract
{
    /**
     * Name of display group for the search fields.
     */
    public const GROUP_SEARCHFIELDS = 'searchfields';

    /**
     * Name of element for selecting maximum number of search results per page.
     */
    public const ELEMENT_HITS_PER_PAGE = 'rows';

    /**
     * Name of search button.
     */
    public const ELEMENT_SEARCH = 'Search';

    /**
     * Name of reset button.
     */
    public const ELEMENT_RESET = 'Reset';

    /**
     * Name of hidden field for search type.
     */
    public const ELEMENT_SEARCHTYPE = 'searchtype';

    /**
     * Name of hidden field for index of first entry on page.
     */
    public const ELEMENT_START = 'start';

    /**
     * Name of hidden field for column/field used for sorting of results.
     */
    public const ELEMENT_SORTFIELD = 'sortfield';

    /**
     * Name of hidden field for direction of sorting.
     */
    public const ELEMENT_SORTORDER = 'sortorder';

    /**
     * Display mode of form for different use cases.
     *
     * - advanced (default)
     * - authorsearch
     *
     * @var string
     *
     * TODO use constants
     */
    private $searchMode;

    /**
     * Constructs form.
     *
     * @param string     $mode Selects between variations of the form.
     * @param array|null $options Zend_Form options
     */
    public function __construct($mode = 'advanced', $options = null)
    {
        $this->searchMode = $mode;
        parent::__construct($options);
    }

    /**
     * Initializes the form and its elements.
     *
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this->setDecorators(
            [
                'FormElements',
                'Form',
                ['HtmlTag', ['tag' => 'div', 'class' => 'form-wrapper']],
            ]
        );

        $this->setAttrib('class', 'opus_form'); // TODO with underline, change?

        $searchFields = [
            'author',
            'title',
            'persons',
            'referee',
            'abstract',
            'fulltext',
        ];

        if ($this->searchMode !== 'authorsearch') {
            $searchFields[] = 'year';
        }

        $this->addSearchFields($searchFields);

        $this->addElement($this->createSearchButton());
        $this->addElement($this->createResetButton());

        $this->addElement('hidden', self::ELEMENT_SEARCHTYPE, ['value' => 'advanced']);
        $this->addElement('hidden', self::ELEMENT_START, ['value' => 0]);
        $this->addElement('hidden', self::ELEMENT_SORTFIELD, ['value' => 'score']);
        $this->addElement('hidden', self::ELEMENT_SORTORDER, ['value' => 'desc']);
    }

    /**
     * Adds multiple search fields in a display group to the form.
     *
     * @param array $searchFields Array with names of search fields
     * @throws Zend_Form_Exception
     */
    public function addSearchFields($searchFields)
    {
        $elements = [];

        foreach ($searchFields as $name) {
            $this->addSearchField($name);
            $elements[] = $name . 'modifier';
            $elements[] = $name;
            if ($name === 'author') {
                $this->getElement($name)->setAttrib('autofocus', true);
            }
        }

        $this->addDisplayGroup(
            $elements,
            self::GROUP_SEARCHFIELDS
        );

        $fieldGroup = $this->getDisplayGroup(self::GROUP_SEARCHFIELDS);

        $fieldGroup->setDecorators([
            'FormElements',
            ['HtmlTag', ['tag' => 'table', 'class' => 'search-form-table']],
        ]);
    }

    /**
     * Adds a single search field.
     *
     * The search fields are rendern in a table. Each field with two elements in a single table row.
     *
     * @param string $name Name of the search field
     * @throws Zend_Form_Exception
     */
    public function addSearchField($name)
    {
        $modifier = $this->createElement('SearchFieldModifier', $name . 'modifier');
        $modifier->setLabel("solrsearch_advancedsearch_field_$name");
        $modifier->setDecorators([
            'ViewHelper',
            'Errors',
            'Description',
            [['selectWrapper' => 'HtmlTag'], ['tag' => 'td']],
            [['labelClose' => 'HtmlTag'], ['tag' => 'td', 'closeOnly' => true]],
            'Label',
            [['labelOpen' => 'HtmlTag'], ['tag' => 'td', 'openOnly' => true]],
            [['rowOpen' => 'HtmlTag'], ['tag' => 'row', 'openOnly' => true]],
        ]);

        if ($name === 'fulltext') {
            $modifier->removeMultiOption(Query::SEARCH_MODIFIER_CONTAINS_ANY);
        }
        $this->addElement($modifier);

        $value = $this->createElement('Text', $name);
        $value->setAttrib('title', $value->getTranslator()->translate("solrsearch_advancedsearch_tooltip_$name"));
        $value->setDecorators([
            'ViewHelper',
            'Errors',
            'Description',
            [['cellWrapper' => 'HtmlTag'], ['tag' => 'td']],
            [['rowClose' => 'HtmlTag'], ['tag' => 'tr', 'closeOnly' => true]],
        ]);
        $this->addElement($value);
    }

    /**
     * Creates the search button with the necessary decorators.
     *
     * @return Zend_Form_Element
     */
    public function createSearchButton()
    {
        $submit = $this->createElement('submit', self::ELEMENT_SEARCH, [
            'id'    => 'edit-submit-advanced-search',
            'class' => 'form-submit',
            'label' => 'advanced_search_form_search_action',
        ]);

        $submit->setDecorators([
            'ViewHelper',
            [['submit-wrapper' => 'HtmlTag'], ['tag' => 'span', 'class' => 'form-submit-wrapper']],
            [
                ['form-item' => 'HtmlTag'],
                [
                    'tag'   => 'div',
                    'id'    => 'edit-submit-advanced-search-wrapper',
                    'class' => 'form-item',
                ],
            ],
        ]);

        return $submit;
    }

    /**
     * Creates the reset button to form.
     *
     * The button uses Javascript to clear the form without submitting a request, if jQuery is present.
     * The necessary decorators are set for the button.
     *
     * @return Zend_Form_Element
     */
    public function createResetButton()
    {
        $button = $this->createElement('submit', self::ELEMENT_RESET, [
            'id'    => 'edit-reset-advanced-search',
            'class' => 'form-submit',
            'label' => 'advanced_search_form_reset_action',
        ]);

        $helper = new Application_View_Helper_JQueryEnabled();

        if ($helper->jQueryEnabled()) {
            $button->setAttrib('onclick', 'return resetAdvancedSearchForm();');
        }

        $button->setDecorators([
            'ViewHelper',
            [['submit-wrapper' => 'HtmlTag'], ['tag' => 'span', 'class' => 'form-submit-wrapper']],
            [
                ['form-item' => 'HtmlTag'],
                [
                    'tag'   => 'div',
                    'id'    => 'edit-reset-advanced-search-wrapper',
                    'class' => 'form-item',
                ],
            ],
        ]);

        return $button;
    }
}
