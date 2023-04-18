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
 * @copyright   Copyright (c) 2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\DocumentInterface;

class Admin_Form_CopyDocument extends Zend_Form
{
    public const ELEMENT_COPY       = 'Copy';
    public const ELEMENT_CANCEL     = 'Cancel';
    public const ELEMENT_COPY_FILES = 'CopyFiles';

    public const SUBFORM_DOCUMENT = 'document';

    public const RESULT_COPY   = 'copy';
    public const RESULT_CANCEL = 'cancel';

    public function init()
    {
        $infoBox = new Admin_Form_InfoBox();

        $this->addSubForm($infoBox, self::SUBFORM_DOCUMENT);

        $this->addElement('checkbox', self::ELEMENT_COPY_FILES);
        $this->addElement('submit', self::ELEMENT_COPY);
        $this->addElement('submit', self::ELEMENT_CANCEL);
    }

    /**
     * @param DocumentInterface $document
     */
    public function populateFromModel($document)
    {
        $this->getSubForm(self::SUBFORM_DOCUMENT)->populateFromModel($document);
    }

    /**
     * @param array $post
     * @param array $context
     * @return string|null
     */
    public function processPost($post, $context)
    {
        if (array_key_exists(self::ELEMENT_COPY, $post)) {
            return self::RESULT_COPY;
        } elseif (array_key_exists(self::ELEMENT_CANCEL, $post)) {
            return self::RESULT_CANCEL;
        }

        return null;
    }
}
