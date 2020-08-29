<?php
/*
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
 * @package     View
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2013-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Select Element für Thesis Grantor Institute.
 */
class Application_Form_Element_Grantor extends Application_Form_Element_Select
{

    public function init()
    {
        parent::init();

        $this->setRequired(true);
        $this->setDisableTranslator(true); // Grantor institutes are not translated

        $validator = new Zend_Validate_Int();
        $validator->setMessage('validation_error_int');
        $this->addValidator($validator);

        $options = Opus_DnbInstitute::getGrantors();

        foreach ($options as $option) {
            $this->addMultiOption($option->getId(), $option->getDisplayName());
        }
    }

    /**
     * Set value for Grantor select form element.
     *
     * If $value is a valid DNB institute a corresponding option is added to select if necessary.
     *
     * @param mixed $value
     * @return void|Zend_Form_Element
     * @throws Opus_Model_Exception
     */
    public function setValue($value)
    {
        try {
            $institute = new Opus_DnbInstitute($value);
        } catch (Opus_Model_NotFoundException $omne) {
            parent::setValue($value); // could be blocked, but keeping compatibility just in case
            return;
        }

        $this->addMultiOption($institute->getId(), $institute->getDisplayName());
        parent::setValue($value);
    }
}
