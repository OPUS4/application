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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * form to show the search mask
 */
class Search_Form_FulltextSearch extends Zend_Form
{
    /**
     * Build easy search form
     *
     * @return void
     */
    public function init() {
		// Create and configure query field element:
		$hitsPerPage = new Zend_Form_Element_Select('hitsPerPage');
		$hitsPerPage->addMultiOptions(array('0' => 'all_hits', '10' => 10, '20' => 20, '25' => 25, '50' => 50));
		$hitsPerPage->setValue('10');
		$hitsPerPage->setLabel('search_hitsPerPage');

		$sort = new Zend_Form_Element_Select('sort');
		$sort->addMultiOptions(array('relevance' => 'search_sort_relevance', 'yat' => 'search_sort_yearandtitle', 'year' => 'search_sort_year', 'title' => 'search_sort_title', 'author' => 'search_sort_author', 'relevance_asc' => 'search_sort_relevance_asc', 'yat_desc' => 'search_sort_yearandtitle_desc', 'year_desc' => 'search_sort_year_desc', 'title_desc' => 'search_sort_title_desc', 'author_desc' => 'search_sort_author_desc'));
		$sort->setLabel('search_sort');

		$query = new Zend_Form_Element_Text('query');
		$query->addValidator('stringLength', false, array(3, 100))
         		->setRequired(true);

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('search_searchaction');

		// Add elements to form:
		$this->addElements(array($hitsPerPage, $sort, $query, $submit));
    }
}