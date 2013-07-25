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
 * @category    Application
 * @package     Form_Decorator
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Gibt ein Formularelement als statischen Text aus, anstelle eines INPUT-Tags.
 */
class Form_Decorator_StaticView extends Zend_Form_Decorator_Abstract {

    /**
     * Gibt Formularelement mit Label und Wert aus.
     *
     * Die Ausgabe erfolgt in der selben DIV Structure wie im Formular, nur das statt eines INPUT Tags einfach nur der
     * Wert des Elements ausgegeben wird.
     *
     * @param string $content
     * @return string
     */
    public function render($content) {
        $element = $this->getElement();

        $output = '<div class="data-wrapper ' . $element->getName() . '-data">';

        $label = $this->getLabel();
        if (!is_null($label)) {
            $output .= sprintf('<div class="%1$s">%2$s</div>', $element->isRequired() ? 'label required' : 'label',
                htmlspecialchars($label));
        }

        return $output . sprintf('<div id="%1$s" class="field">%2$s</div></div>', $element->getId(), $this->getValue());
    }

    public function getLabel() {
        $label = $this->getElement()->getLabel();
        return (strlen(trim($label)) > 0) ? $label : null;
    }

    public function getValue() {
        return  $this->getElement()->getTranslator()->translate($this->getElement()->getValue());
    }

}