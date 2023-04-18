<?PHP

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
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Angepasste Form Element Klasse für Submit Buttons.
 */
class Application_Form_Element_Submit extends Zend_Form_Element_Submit
{
    /**
     * Initialisiert Formularelement.
     *
     * Fügt Prefix-Path für Decorator hinzu, damit 'ElementHtmlTag' verwendet werden kann.
     */
    public function init()
    {
        parent::init();

        $this->addPrefixPath('Application_Form_Decorator', 'Application/Form/Decorator', Zend_Form::DECORATOR);
    }

    /**
     * Laedt die Dekoratoren für Button Element.
     *
     * @return $this
     */
    public function loadDefaultDecorators()
    {
        if (! $this->loadDefaultDecoratorsIsDisabled() && count($this->getDecorators()) === 0) {
            $this->setDecorators([
                'ViewHelper',
                'ElementHtmlTag',
                [['dataWrapper' => 'HtmlTagWithId'], ['tag' => 'div', 'class' => 'data-wrapper']],
            ]);
        }
        return $this;
    }
}
