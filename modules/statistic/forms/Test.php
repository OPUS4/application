<?php

/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the North Rhine-Westphalian Library Service Center,
 * the Cooperative Library Network Berlin-Brandenburg, the Saarland University
 * and State Library, the Saxon State Library - Dresden State and University
 * Library, the Bielefeld University Library and the University Library of
 * Hamburg University of Technology with funding from the German Research
 * Foundation and the European Regional Development Fund.
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
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Statistic_Form_Test extends Zend_Form
{
    /** @var array */
    public $elementDecorators = [
        'ViewHelper',
        'Errors',
        [['data' => 'HtmlTag'], ['tag' => 'td', 'class' => 'element']],
        ['Label', ['tag' => 'td']],
        [['row' => 'HtmlTag'], ['tag' => 'tr']],
    ];

    /** @var array */
    public $buttonDecorators = [
        'ViewHelper',
        [['data' => 'HtmlTag'], ['tag' => 'td', 'class' => 'element']],
        [['label' => 'HtmlTag'], ['tag' => 'td', 'placement' => 'prepend']],
        [['row' => 'HtmlTag'], ['tag' => 'tr']],
    ];

    public function init()
    {
        $this->addElement(
            'text',
            'document_id',
            [
                'decorators' => $this->elementDecorators,
                'label'      => 'Document ID:',
            ]
        );
        $this->addElement(
            'text',
            'file_id',
            [
                'decorators' => $this->elementDecorators,
                'label'      => 'File ID:',
            ]
        );

        $this->addElement(
            'text',
            'ip',
            [
                'decorators' => $this->elementDecorators,
                'label'      => 'IP:',
            ]
        );
        $this->addElement(
            'text',
            'user_agent',
            [
                'decorators' => $this->elementDecorators,
                'label'      => 'User Agent:',
            ]
        );

        $this->addElement(
            'submit',
            'save',
            [
                'decorators' => $this->buttonDecorators,
                'label'      => 'Submit!',
            ]
        );

        $this->setDefaults(
            [
                'document_id' => 1,
                'file_id'     => 1,
                'user_agent'  => '',
                'ip'          => '127.0.0.1',
            ]
        );
    }

    public function loadDefaultDecorators()
    {
        $this->setDecorators(
            [
                'FormElements',
                ['HtmlTag', ['tag' => 'table']],
                'Form',
            ]
        );
    }

    /*public function init() {
        $document_id = new Zend_Form_Element_Text('document_id');
        $document_id->addDecorator(new Zend_Form_Decorator_Label('Document ID:'));
        $this->addElement($document_id);
        $this->addElement('text', 'file_id');
    }*/
}
