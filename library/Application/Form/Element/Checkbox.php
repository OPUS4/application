<?PHP
/*
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
 * @package     View
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Angepasste Klasse für Checkbox Formularelemente.
 */
class Application_Form_Element_Checkbox extends Zend_Form_Element_Checkbox implements Application_Form_IElement
{

    private $_viewCheckedValue = 'Field_Value_True';

    private $_viewUncheckedValue = 'Field_Value_False';

    public function init()
    {
        $this->addPrefixPath('Application_Form_Decorator', 'Application/Form/Decorator', Zend_Form::DECORATOR);
    }

    public function loadDefaultDecorators()
    {
        if (! $this->loadDefaultDecoratorsIsDisabled() && count($this->getDecorators()) == 0) {
            $this->setDecorators(
                [
                'ViewHelper',
                'Errors',
                'Description',
                'ElementHtmlTag',
                ['Label', ['tag' => 'div', 'tagClass' => 'label', 'placement' => 'prepend']],
                [['dataWrapper' => 'HtmlTagWithId'], ['tag' => 'div', 'class' => 'data-wrapper']]
                ]
            );
        }
    }

    public function prepareRenderingAsView()
    {
        $viewHelper = $this->getDecorator('ViewHelper');
        if ($viewHelper instanceof Application_Form_Decorator_ViewHelper) {
            $viewHelper->setViewOnlyEnabled(true);
        }
        $translator = $this->getTranslator();
        if (! is_null($translator)) {
            $this->setCheckedValue($translator->translate($this->_viewCheckedValue));
            $this->setUncheckedValue($translator->translate($this->_viewUncheckedValue));
        } else {
            $this->setCheckedValue($this->_viewCheckedValue);
            $this->setUncheckedValue($this->_viewUncheckedValue);
        }

        $this->setChecked($this->getValue());
    }

    /**
     * Liefert Hinweis zum Element-Value, z.B. das eine ISBN ungültig ist.
     *
     * Hinweise sind wie Validierungsfehler, die aber das Abspeichern nicht verhindern und schon beim Aufruf des
     * Formulars für existierende Werte berechnet werden.
     *
     * @return string
     */
    public function getHint()
    {
        return null;
    }

    public function getViewCheckedValue()
    {
        return $this->_viewCheckedValue;
    }

    public function setViewCheckedValue($value)
    {
        $this->_viewCheckedValue = $value;
        return $this;
    }

    public function getViewUncheckedValue()
    {
        return $this->_viewUncheckedValue;
    }

    public function setViewUncheckedValue($value)
    {
        $this->_viewUncheckedValue = $value;
        return $this;
    }
}
