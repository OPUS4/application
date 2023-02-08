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
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Form for editing help.ini to modify FAQ page structure.
 *
 * TODO add header to form
 * TODO add description to form
 */
class Setup_Form_HelpConfig extends Application_Form_Abstract
{
    public const ELEMENT_HTML_TITLE = 'Title';

    public const ELEMENT_HTML_DESCRIPTION = 'Description';

    public const ELEMENT_STRUCTURE = 'Structure';

    public const ELEMENT_SAVE = 'Save';

    public const ELEMENT_CANCEL = 'Cancel';

    public const RESULT_SAVE = 'Save';

    public const RESULT_CANCEL = 'Cancel';

    public function init()
    {
        parent::init();

        $this->setDecorators([
            'FormElements',
            'Form',
        ]);

        // TODO add translation key
        // TODO add wrapping HTML like H1 or <p> or <div class="">
        $this->addElement('html', self::ELEMENT_HTML_TITLE, [
            'content' => 'setup_helppage_structure_title',
            'tag'     => 'h1',
        ]);
        $this->addElement('html', self::ELEMENT_HTML_DESCRIPTION, [
            'content'  => 'setup_helppage_structure_description',
            'cssClass' => 'description',
        ]);

        $this->addElement('textarea', self::ELEMENT_STRUCTURE, [
            'cols' => 80,
            'rows' => 20,
        ]);

        $this->addElement('submit', self::ELEMENT_SAVE, [
            'decorators' => [
                'ViewHelper',
                [['liWrapper' => 'HtmlTag'], ['tag' => 'li', 'class' => 'save-element']],
            ],
        ]);

        $this->addElement('submit', self::ELEMENT_CANCEL, [
            'decorators' => [
                'ViewHelper',
                [['liWrapper' => 'HtmlTag'], ['tag' => 'li', 'class' => 'cancel-element']],
            ],
        ]);

        $this->addDisplayGroup(
            [self::ELEMENT_SAVE, self::ELEMENT_CANCEL],
            'actions',
            [
                'order'      => 1000,
                'decorators' => [
                    'FormElements',
                    [['ulWrapper' => 'HtmlTag'], ['tag' => 'ul', 'class' => 'form-action']],
                    [['divWrapper' => 'HtmlTag'], ['id' => 'form-action']],
                ],
            ]
        );
    }

    /**
     * @param array $post
     * @return string|null
     */
    public function processPost($post)
    {
        if (isset($post[self::ELEMENT_SAVE])) {
            return self::RESULT_SAVE;
        } elseif (isset($post[self::ELEMENT_CANCEL])) {
            return self::RESULT_CANCEL;
        } else {
            return null;
        }
    }
}
