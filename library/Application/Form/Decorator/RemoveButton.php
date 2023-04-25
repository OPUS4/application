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
 * Decorator fuer die Ausgabe eines Remove-Buttons fuer ein Unterformular.
 *
 * Der Decorator wird dem Formular zugewiesen und er sorgt dafür, dass ein
 * Input-Feld fuer den Remove-Button ausgegeben wird.
 *
 * TODO find better solution that is more generic
 */

class Application_Form_Decorator_RemoveButton extends Zend_Form_Decorator_Abstract
{
    /** @var Zend_Form_Element|null */
    private $secondElement;

    /**
     * @param string $content
     * @return string
     */
    public function render($content)
    {
        $button = $this->getElement();

        if ($button instanceof Zend_Form) {
            $button = $button->getElement(Admin_Form_Document_MultiSubForm::ELEMENT_REMOVE);
        }

        if ($button === null) {
            return $content;
        }

        $view = $button->getView();

        if (! $view instanceof Zend_View_Interface) {
            return $content;
        }

        $markup = $this->renderElement($button);

        if ($this->getSecondElement() !== null) {
            $markup = $this->renderElement($this->getSecondElement(), 'hidden') . $markup;
        }

        return $content . $markup;
    }

    /**
     * @param Zend_Form_Element $element
     * @param string            $type
     * @return string
     */
    public function renderElement($element, $type = 'submit')
    {
        $buttonId       = $element->getId();
        $buttonFullName = $element->getFullyQualifiedName();
        $buttonName     = $element->getName();

        // TODO hack (find transparent solution)
        $value = $type === 'submit' ? $element->getLabel() : null;

        if ($value === null || strlen(trim($value)) === 0) {
            $value = $element->getValue();
        }

        return "<input type=\"$type\" name=\"$buttonFullName\" id=\"$buttonId\" value=\"$value\" />";
    }

    /**
     * @param Zend_Form_Element|null $element
     */
    public function setSecondElement($element)
    {
        $this->secondElement = $element;
    }

    /**
     * @return Zend_Form_Element|null
     */
    public function getSecondElement()
    {
        $element = $this->getOption('element');

        if ($element !== null) {
            $this->removeOption('element');
            $this->secondElement = $element;
        }

        return $this->secondElement;
    }
}
