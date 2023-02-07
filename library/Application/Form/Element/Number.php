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
 * Input field for a number.
 *
 * TODO use type number
 */
class Application_Form_Element_Number extends Application_Form_Element_Text
{
    public function init()
    {
        parent::init();

        if ($this->getAttrib('size') === null) {
            $this->setAttrib('size', 6);
        }

        $validator = new Zend_Validate_Int();
        $validator->setMessage('validation_error_int');
        $this->addValidator($validator);

        $options = [];

        $min = $this->getAttrib('min');
        if ($min === null) {
            $min = 0;
        } else {
            $this->setAttrib('min', null); // remove from rendered attributes
        }
        $options['min'] = $min;

        $max = $this->getAttrib('max');
        if ($max === null) {
            $validator = new Zend_Validate_GreaterThan(['min' => $min - 1]); // inclusive not supported in ZF1
            $validator->setMessage('validation_error_number_tooSmall');
        } else {
            $this->setAttrib('max', null); // remove from rendered attributes
            $options['max'] = $max;

            $validator = new Zend_Validate_Between(['min' => $min, 'max' => $max]);
            $validator->setMessage('validation_error_number_notBetween');
        }

        $this->addValidator($validator);
    }
}
