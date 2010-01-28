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
 * @package     Module_Search
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * form to show the search mask
 */
class MetadataSearch extends Zend_Form
{
	/**
	 * number of fields that should be shown in the form
	 */
	private $_queryFieldNumber = 1;

	public function init() {
	    $this->setupForm();
	}

	/**
     * Build extended search form
     */
    protected function setupForm() {
		// decorate form
		$this->clearDecorators();
		$decorators = array(
		    array('ViewHelper'),
		    array('Errors'),
		    array('Label', array(
		        'requiredSuffix' => ' *',
		        'class' => 'leftalign'
		    )),
		    array('HtmlTag', array('tag' => 'p')),
		);
		$fieldDecorators = array(
		    array('ViewHelper'),
		    array('Errors'),
		    array('Label', array(
		        'requiredSuffix' => ' *',
		        'class' => 'leftalign'
		    )),
		    array('HtmlTag', array(
                'tag' => 'div',
                'class' => 'fieldsearch'
		    )),
		);
		$searchtermDecorators = array(
		    array('ViewHelper'),
		    array('Errors'),
		    array('Label', array(
		        'requiredSuffix' => ' *',
		        'class' => 'leftalign'
		    )),
		    array('HtmlTag', array(
                'tag' => 'div',
                'class' => 'queryterm'
		    )),
		);

		// Create and configure query field elements:
		$truncation = new Zend_Form_Element_Select('searchtype');
		$truncation->addMultiOptions(array('exact' => 'exact_search', 'truncated' => 'truncated_search'));

		$hitsPerPage = new Zend_Form_Element_Select('hitsPerPage');
		$hitsPerPage->addMultiOptions(array('0' => 'all_hits', '10' => 10, '20' => 20, '25' => 25, '50' => 50));
		$hitsPerPage->setValue('10');
		$hitsPerPage->setLabel('search_hitsPerPage');
		$hitsPerPage->setDecorators($decorators);

		$sort = new Zend_Form_Element_Select('sort');
		$sort->addMultiOptions(array('relevance' => 'search_sort_relevance', 'yat' => 'search_sort_yearandtitle', 'year' => 'search_sort_year', 'title' => 'search_sort_title', 'author' => 'search_sort_author', 'relevance_asc' => 'search_sort_relevance_asc', 'yat_desc' => 'search_sort_yearandtitle_desc', 'year_desc' => 'search_sort_year_desc', 'title_desc' => 'search_sort_title_desc', 'author_desc' => 'search_sort_author_desc'));
		$sort->setLabel('search_sort');
		$sort->setDecorators($decorators);

        $languageList = new Zend_Form_Element_Select('language');
        $langs = Zend_Registry::get('Available_Languages');
        $languageList->setLabel('Language')
            ->setMultiOptions(array('0' => 'all_hits'));
        $languageList->addMultiOptions($langs);
        $languageList->setDecorators($decorators);

        $doctypeList = new Zend_Form_Element_Select('doctype');
        $doctypes = BrowsingList::getDocumentTypeList();
        $doctypeList->setLabel('searchfield_doctype')
            ->setMultiOptions(array('0' => 'all_hits'));
        $doctypeList->addMultiOptions($doctypes);
        $doctypeList->setDecorators($decorators);

		$query = array();
		$field = array();
		$boolean = array();
		for ($n = 0; $n < $this->_queryFieldNumber; $n++)
		{
		    $field[$n] = new Zend_Form_Element_Select('field[' . $n . ']');
		    $field[$n]->addMultiOptions($this->listSearchFields());
		    $field[$n]->setDecorators($fieldDecorators);

		    $query[$n] = new Zend_Form_Element_Text('query[' . $n . ']');
		    $query[$n]->addValidator('stringLength', false, array(3, 100));
		    $query[$n]->setDecorators($searchtermDecorators);

		    if ($n < ($this->_queryFieldNumber-1))
		    {
		        $boolean[$n] = new Zend_Form_Element_Select('boolean[' . $n . ']');
		        $boolean[$n]->addMultiOptions(array('and' => 'boolean_and', 'or' => 'boolean_or', 'not' => 'boolean_not'));
		    }
		}
        $addElement = new Zend_Form_Element_Submit('add');
        $addElement->setLabel('add_searchfield');
        $addElement->setAttrib('name', 'Action');

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('search_searchaction');

        // Add elements to form:
		$this->addElements(array($truncation, $hitsPerPage, $sort, $languageList, $doctypeList));
		for ($n = 0; $n < $this->_queryFieldNumber; $n++)
		{
		    $this->addElements(array($field[$n], $query[$n]));
		    if ($n < ($this->_queryFieldNumber-1))
		    {
		        $this->addElement($boolean[$n]);
		    }
		}
		$this->addElement($addElement);
		$this->addElement($submit);
    }

    public function addSearchfield() {
        $this->_queryFieldNumber++;
        $this->setupForm();
    }

    /**
     * Build list of possible search fields
     */
    private function listSearchFields()
    {
    	return $this->retrieveSearchFields();
    }

    /**
     * Retrieve a list of possible search fields from outside
     */
    public static function retrieveSearchFields()
    {
    	// aus Opus3:
    	// Titel, Person, Freitext, Schlagwort, Körperschaft, Fakultät, Institut, Abstract
    	// Dokumentart, Quelle, Jahr, verf. Klassifikationen
    	// Opus4: Personen differenzieren, Quelle raus (?)
    	$fields = array(
            'title' => 'searchfield_title',
            'author' => 'searchfield_author',
            'persons' => 'searchfield_persons',
            'fulltext' => 'searchfield_fulltext',
            'abstract' => 'searchfield_abstract',
            'subject' => 'searchfield_subject',
            'year' => 'searchfield_year',
            'institute' => 'searchfield_institute',
            'urn' => 'searchfield_urn',
            'isbn' => 'searchfield_isbn'
            );
    	return $fields;
    }

    /**
     * Retrieve a list of possible search fields
     */
    public static function retrieveInternalSearchFields()
    {
    	// aus Opus3:
    	// Titel, Person, Freitext, Schlagwort, Körperschaft, Fakultät, Institut, Abstract
    	// Dokumentart, Quelle, Jahr, verf. Klassifikationen
    	// Opus4: Personen differenzieren, Quelle raus (?)
    	$fields = array(
            'title' => 'searchfield_title',
            'author' => 'searchfield_author',
            'persons' => 'searchfield_persons',
            'fulltext' => 'searchfield_fulltext',
            'abstract' => 'searchfield_abstract',
            'subject' => 'searchfield_subject',
            'year' => 'searchfield_year',
            'institute' => 'searchfield_institute',
            'urn' => 'searchfield_urn',
            'isbn' => 'searchfield_isbn',
            'doctype' => 'searchfield_doctype'
            );
    	return $fields;
    }
}