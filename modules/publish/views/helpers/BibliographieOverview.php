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

class Publish_View_Helper_BibliographieOverview extends Zend_View_Helper_Abstract
{
    /** @var Zend_View_Interface */
    public $view;

    /** @var Zend_Session_Namespace */
    public $session;

    /** @var DocumentInterface */
    public $document;

    /**
     * Method informs the user if the current document belongs to bibliography.
     * The view helper can be used anywhere in the publish process: on index, specific form or check page.
     *
     * @return string (html output)
     */
    public function bibliographieOverview()
    {
        $config = Config::get();
        if (
            ! isset($config->form->first->bibliographie) ||
            (! filter_var($config->form->first->bibliographie, FILTER_VALIDATE_BOOLEAN))
        ) {
            return '';
        }

        $this->session = new Zend_Session_Namespace('Publish');

        $fieldsetStart = '<fieldset><legend>' . $this->view->translate('header_bibliographie')
            . "</legend>\n\t\t\n\t\t";
        $fieldsetEnd   = "</fieldset>";

        if ($this->session->documentId === '') {
            return "";
        }

        $this->document = Document::get($this->session->documentId);
        $bib            = $this->document->getBelongsToBibliography();

        if (empty($bib)) {
            return $fieldsetStart . $this->view->translate('notBelongsToBibliographie') . $fieldsetEnd;
        }

        $overview = $this->view->translate('belongsToBibliographie');

        return $fieldsetStart . $overview . $fieldsetEnd;
    }
}
