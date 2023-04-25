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

use Opus\Common\Config;
use Opus\Common\Document;
use Opus\Common\DocumentInterface;

class Publish_View_Helper_FileOverview extends Zend_View_Helper_Abstract
{
    /** @var Zend_View_Interface */
    public $view;

    /** @var Zend_Session_Namespace */
    public $session;

    /** @var DocumentInterface */
    public $document;

    /**
     * method to render specific elements of an form
     *
     * @return string Element to render in view
     */
    public function fileOverview()
    {
        $config = Config::get();
        if (
            ! isset($config->form->first->enable_upload) ||
            (! filter_var($config->form->first->enable_upload, FILTER_VALIDATE_BOOLEAN))
        ) {
            return '';
        }

        $this->session = new Zend_Session_Namespace('Publish');

        $fieldsetStart = "<fieldset><legend>" . $this->view->translate('already_uploaded_files')
            . "</legend>\n\t\t\n\t\t";
        $fieldsetEnd   = "</fieldset>";

        if ($this->session->documentId === '') {
            return '';
        }

        $this->document = Document::get($this->session->documentId);
        $files          = $this->document->getFile();

        if (empty($files)) {
            return $fieldsetStart . '<b>' . $this->view->translate('no_uploaded_files') . '</b>' . $fieldsetEnd;
        }

        $overview = '';

        if ($this->view->uploadSuccess === false) {
            $overview .= "<div class='form-errors'><ul><li>" . $this->view->translate('error_uploaded_files')
                . "</li></ul></div>";
        }

        foreach ($files as $file) {
            $overview .= '<p>' . $this->view->translate('name') . ': <b>' . htmlspecialchars($file->getPathName())
                . '</b><br/>' . $this->view->translate('type') . ': ' . htmlspecialchars($file->getMimeType())
                . '<br/>' . $this->view->translate('size') . ': ' . htmlspecialchars($file->getFileSize())
                . ' ' . $this->view->translate('bytes')
                . '<br />' . $this->view->translate('uploadComment') . ': ' . htmlspecialchars($file->getComment())
                . '</p>';
        }

        return $fieldsetStart . $overview . $fieldsetEnd;
    }
}
