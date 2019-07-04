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
 * @package     Module_Setup
 * @author      Edouard Simon (edouard.simon@zib.de)
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * TODO use languages actually defined as supported
 */
class Setup_Form_HomePage extends Zend_Form_SubForm
{

    public function init()
    {
        foreach (['de', 'en'] as $lang) {
            $langForm = new Zend_Form_SubForm();
            $langForm->setLegend("setup_language_$lang");
            $keyForm = new Zend_Form_SubForm();

            $keyForm->addElement(
                'text', 'home_index_index_pagetitle', [
                    'label' => 'setup_home_index_index_pagetitle',
                    'attribs' => ['size' => 90]
                ]
            );
            $keyForm->addElement(
                'text', 'home_index_index_title', [
                    'label' => 'setup_home_index_index_title',
                    'attribs' => ['size' => 90]
                ]
            );
            $keyForm->addElement(
                'textarea', 'home_index_index_welcome', [
                    'label' => 'setup_home_index_index_welcome',
                    'attribs' => ['size' => 90]
                ]
            );
            $keyForm->addElement(
                'textarea', 'home_index_index_instructions', [
                    'label' => 'setup_home_index_index_instructions',
                    'attribs' => ['size' => 90]
                ]
            );
            $langForm->addSubForm($keyForm, 'key');
            $this->addSubForm($langForm, $lang);
        }
    }
}
