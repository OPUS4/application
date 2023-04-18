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

use Opus\Common\DocumentInterface;
use Opus\Common\FileInterface;

class Admin_Form_Document_Files extends Admin_Form_AbstractDocumentSubForm
{
    /** @var array */
    private $header = [
        ['label' => null, 'class' => 'file'],
        ['label' => 'files_column_size', 'class' => 'size'],
        ['label' => 'files_column_mimetype', 'class' => 'mimetype'],
        ['label' => 'files_column_language', 'class' => 'language'],
        ['label' => 'files_column_frontdoor', 'class' => 'visiblefrontdoor'],
        ['label' => 'files_column_oai', 'class' => 'visibleoai'],
    ];

    public function init()
    {
        parent::init();

        $this->setLegend('admin_document_section_files');
        $this->setDisableTranslator(true); // so legend won't be translated twice

        $header = new Application_Form_TableHeader($this->header);

        $this->addSubForm($header, 'Header');

        $this->setDecorators(
            [
                'FormElements',
                [['table' => 'HtmlTag'], ['tag' => 'table']],
                [['fieldsWrapper' => 'HtmlTag'], ['tag' => 'div', 'class' => 'fields-wrapper']],
                'Fieldset',
                [['divWrapper' => 'HtmlTag'], ['tag' => 'div', 'class' => 'subform']],
            ]
        );
    }

    /**
     * @param DocumentInterface $document
     */
    public function populateFromModel($document)
    {
        foreach ($document->getFile() as $file) {
            $this->addFileSubForm($file);
        }
    }

    /**
     * @param FileInterface $file
     */
    protected function addFileSubForm($file)
    {
        $form = new Admin_Form_Document_File();
        $form->populateFromModel($file);
        $index = count($this->getSubForms()) - 1;
        $form->setOrder($index + 1);
        $this->addSubForm($form, 'File' . $index);
    }
}
