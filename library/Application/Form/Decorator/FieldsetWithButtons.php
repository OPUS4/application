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
 * Fieldset mit Buttons im Legend Tag.
 *
 * Wird im Metadaten-Formular und Filemanager verwendet, um die Add- und Import Buttons richtig zu positionieren.
 */
class Application_Form_Decorator_FieldsetWithButtons extends Zend_Form_Decorator_Fieldset
{
    /** @var Zend_Form_Element|Zend_Form_Element[]|null */
    protected $legendButtons;

    /**
     * @param string $content
     * @return string
     */
    public function render($content)
    {
        $this->setOption('escape', false);
        $this->getElement()->setDisableTranslator(true); // legend is translated before set
        return parent::render($content);
    }

    /**
     * @param string|null $buttons
     */
    public function setLegendButtons($buttons)
    {
        $this->legendButtons = $buttons;
    }

    /**
     * @return Zend_Form_Element|Zend_Form_Element[]|null
     */
    public function getLegendButtons()
    {
        $buttons = $this->legendButtons;

        if ((null === $buttons) && (null !== ($element = $this->getElement()))) {
            if (method_exists($element, 'getLegendButtons')) {
                $buttons = $element->getLegendButtons();
                $this->setLegendButtons($buttons);
            }
        }

        if ((null === $buttons) && (null !== ($buttons = $this->getOption('legendButtons')))) {
            $this->setLegendButtons($buttons);
            $this->removeOption('legendButtons');
        }

        return $buttons;
    }

    /**
     * @return string
     */
    public function getLegend()
    {
        $legend = parent::getLegend();

        $element = $this->getElement();
        $view    = $element->getView();

        $buttons = $this->getLegendButtons();

        if ($buttons !== null && ! is_array($buttons)) {
            $buttons = [$buttons];
        }

        $markup = '';

        if ($buttons !== null && count($buttons) >= 1) {
            $markup .= '<span class="button-group">';
            foreach ($buttons as $button) {
                $button = $element->getElement($button);
                if ($button !== null) {
                    $markup .= $this->renderButton($button);
                }
            }
            $markup .= '</span>';
        }

        return $legend . $markup;
    }

    /**
     * @param Zend_Form_Element $button
     * @return string
     */
    protected function renderButton($button)
    {
        $name      = $button->getName();
        $elementId = $button->getId();
        $decorator = new Zend_Form_Decorator_ViewHelper();
        $decorator->setElement($button);
        return "<span class=\"data-wrapper $name-data\">"
            . "<span class=\"field\" id=\"$elementId-element\">"
            . $decorator->render(null)
            . '</span></span>';
    }
}
