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

use Opus\Common\FileInterface;

/**
 * View Helper fuer Link zu Datei.
 *
 * Wird in der Dateientabelle in der Metadaten-Übersicht verwendet.
 */
class Application_View_Helper_FileLink extends Zend_View_Helper_Abstract
{
    /**
     * Rendert Link fuer Datei.
     *
     * @param string        $name
     * @param FileInterface $file
     * @param array|null    $options
     * @return string HTML output
     */
    public function fileLink($name, $file, $options = null)
    {
        // TODO unit test 'cover=false' URL parameter which should suppress PDF cover generation on file download

        if ($file === null) {
            throw new Application_Exception(__METHOD__ . 'Parameter $file must not be null (for ' . $name . ').');
        }

        $fileName = $file->getPathName();

        if (isset($options['useFileLabel']) && $options['useFileLabel']) {
            $fileName = $file->getLabel();
        }

        $fileName = $fileName === null || strlen(trim($fileName)) === 0 ? $file->getPathName() : $fileName;
        $fileUrl  = $this->view->serverUrl() . $this->view->baseUrl() . '/files/' . $file->getParentId()
                . '/' . urlencode($file->getPathName()) . '?cover=false';

        return '<a href="' . $fileUrl . '" class="filelink">' . htmlspecialchars($fileName) . '</a>'
            . $this->view->formHidden($name, $file->getId(), null);
    }
}
