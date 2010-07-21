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
 * @package     Module_SocialBookmarking
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * form to show the login mask for Bibsonomy
 */
class BibsonomyBookmarkForm extends Zend_Form
{
    /**
     * Build easy search form
     *
     * @return void
     */
    public function init() {
		// Create and configure query field element:
		$connotea = new Zend_Session_Namespace('bibsonomy');
		$doc = new Opus_Document($connotea->docId);
  		
		$userTags = new Zend_Form_Element_Text('user_tags');
		$userTags->setRequired(false);
		$userTags->setLabel('bibsonomy_usertags');

		$title = new Zend_Form_Element_Text('usertitle');
		$title->setRequired(true);
		$title->setValue($doc->getTitleMain(0)->getValue());
		$title->setLabel('bibsonomy_usertitle');

		$description = new Zend_Form_Element_Textarea('userdescription');
		$description->setValue($doc->getTitleAbstract(0)->getValue());
		$description->setLabel('bibsonomy_userdescription');

        $submit = new Zend_Form_Element_Submit('bibsonomy_bm');
        $submit->setLabel('bibsonomy_bookmark');

		// Add elements to form:
		$this->addElements(array($userTags, $title, $description, $submit));
    }
}
