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
 * Gibt Formularelement als DIV-Tags aus.
 *
 * Ersetzt den normalen ViewHelper Dekorator, um statt INPUT-Elementen, einfach nur den Wert des Formularelements in
 * einem DIV auszugeben. Das wird für die statische Ansicht von Formularen, z.B. Metadaten-Übersicht verwendet.
 */
class Application_Form_Decorator_ViewHelper extends Zend_Form_Decorator_ViewHelper
{
    /** @var bool */
    private $viewOnlyEnabled = false;

    /**
     * @return string
     */
    public function getHelper()
    {
        if ($this->isViewOnlyEnabled()) {
            $element = $this->getElement();

            if (method_exists($element, 'getStaticViewHelper')) {
                $helper = $element->getStaticViewHelper();
            } else {
                $type = $element->getType();
                if ($pos = strrpos($type, '_')) {
                    $type = substr($type, $pos + 1);
                }
                $helper = 'viewForm' . ucfirst($type);
                try {
                    $element->getView()->getHelper($helper);
                } catch (Zend_Loader_PluginLoader_Exception $zlpe) {
                    $helper = 'viewFormDefault';
                }
            }
            $this->setHelper($helper);
            return $this->_helper;
        } else {
            return parent::getHelper();
        }
    }

    /**
     * @param bool $enabled
     * @return $this
     */
    public function setViewOnlyEnabled($enabled)
    {
        $this->viewOnlyEnabled = $enabled;
        return $this;
    }

    /**
     * @return bool
     */
    public function isViewOnlyEnabled()
    {
        $enabled = $this->getOption('viewOnlyEnabled');

        if ($enabled !== null) {
            $this->removeOption('viewOnlyEnabled');
            $this->viewOnlyEnabled = $enabled;
        }

        return $this->viewOnlyEnabled;
    }
}
