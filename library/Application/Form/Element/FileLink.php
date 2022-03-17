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
 */

use Opus\File;
use Opus\Model\NotFoundException;

/**
 * Formularelement für die Anzeige eines Download Links für ein File Objekt.
 *
 * Das Formularelement kann nur mit gültigen IDs für File verwendet werden.
 *
 * @category    Application
 * @package     Form_Element
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */
class Application_Form_Element_FileLink extends Application_Form_Element_Text
{

    public function loadDefaultDecorators()
    {
        parent::loadDefaultDecorators();

        $label = $this->getDecorator('LabelNotEmpty');

        if ($label !== false) {
            $label->setOption('disableFor', true);
        }
    }

    public function getStaticViewHelper()
    {
        return 'fileLink';
    }

    public function setValue($file)
    {
        if (is_null($file)) {
            throw new Application_Exception(__METHOD__ . " Value must not be null.");
        }

        if (! $file instanceof File) {
            try {
                $file = new File($file);
            } catch (NotFoundException $omnfe) {
                throw new Application_Exception("File with ID = $file not found.");
            }
        }

        if (! $file->exists()) {
            $this->addError('admin_filemanager_file_does_not_exist');
        }
        parent::setValue($file);
    }

    /**
     * Validierung ist erfolgreich, wenn File mit ID existiert.
     *
     * Wenn die ID nicht existiert wird in setValue eine Application_Exception geworfen.
     *
     * @param mixed $value
     * @return bool
     */
    public function isValid($value)
    {
        $this->setValue($value);
        $file = $this->getValue();

        if ($file instanceof File) {
            return true;
        } else {
            return false;
        }
    }
}
