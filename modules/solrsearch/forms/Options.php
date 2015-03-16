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
 * @package     Solrsearch_Form
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2015, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Simple form for search options.
 *
 * Currently only the number of results is part of this form. It is used on the page for showing the latest documents.
 */
class Solrsearch_Form_Options extends Application_Form_Abstract {

    /**
     * Number of results.
     */
    const ELEMENT_HITS_PER_PAGE = 'rows';

    /**
     * Type of search.
     */
    const ELEMENT_SEARCH_TYPE = 'searchtype';

    /**
     * Submit button for searching.
     */
    const ELEMENT_SEARCH = 'search';

    /**
     * Initializes form and creates form elements.
     * @throws Zend_Form_Exception
     */
    public function init() {
        parent::init();

        $this->setDecorators(
            array(
                'FormElements',
                'Form'
            )
        );

        $this->addElement('HitsPerPage', self::ELEMENT_HITS_PER_PAGE);
        $this->addElement('hidden', self::ELEMENT_SEARCH_TYPE, array('value' => 'latest'));
        $this->addElement('submit', self::ELEMENT_SEARCH, array(
            'label' => 'latest_submit',
            'decorators' => array(
                'ViewHelper'
            )
        ));

        $this->getElement(self::ELEMENT_SEARCH)->setAttrib('name', ''); // prevents parameter from showing in URL
        $this->getElement(self::ELEMENT_SEARCH)->setAttrib('id', '');

        $this->addDisplayGroup(
            array(self::ELEMENT_HITS_PER_PAGE, self::ELEMENT_SEARCH),
            'all',
            array('legend' => 'latest_options')
        );

        $displayGroup = $this->getDisplayGroup('all');

        $displayGroup->removeDecorator('HtmlTag');
        $displayGroup->removeDecorator('DtDdWrapper');
    }

}
