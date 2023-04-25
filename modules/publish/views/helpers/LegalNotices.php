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
class Publish_View_Helper_LegalNotices extends Zend_View_Helper_Abstract
{
    /** @var Zend_View_Interface */
    public $view;

    /**
     * Method informs the user if the current document belongs to bibliography.
     * The view helper can be used anywhere in the publish process: on index, specific form or check page.
     *
     * @param Zend_Form $form
     * @return string (html output)
     */
    public function legalNotices($form)
    {
        $session = new Zend_Session_Namespace('Publish');

        if ($form->getElement('LegalNotices') !== null) {
            $fieldset       = new Publish_View_Helper_Element();
            $fieldset->view = $this->view;
            $elementData    = $form->getElementAttributes('LegalNotices');
            return $fieldset->element($elementData);
        }

        $element = $form->createElement('checkbox', 'LegalNotices');
        $element->setRequired(true)
                ->setChecked(false)
                ->setDisableTranslator(true);
        $form->addElement($element);

        $elementData = [
            'id'          => 'LegalNotices',
            'label'       => 'LegalNotices',
            'req'         => 'required',
            'type'        => 'Zend_Form_Element_Checkbox',
            'createType'  => 'checkbox',
            'header'      => 'header_LegalNotices',
            'value'       => '1',
            'check'       => '',
            'disabled'    => '0',
            'error'       => [],
            'DT_external' => true,
        ];

        $session->DT_externals['LegalNotices'] = $elementData;

        $fieldset       = new Publish_View_Helper_Element();
        $fieldset->view = $this->view;
        return $fieldset->element($elementData);
    }
}
