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

/**
 * Dekorator für die Ausgabe eines Tabellenkopfes.
 *
 * @category    Application
 * @package     Application_Form_Decorator
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */
class Application_Form_Decorator_TableHeader extends Zend_Form_Decorator_Abstract
{

    private $_columns = null;

    public function render($content)
    {
        // Zeige Tabellenkopf nur wenn es Einträge (Unterformulare) gibt
        if (count($this->getElement()->getSubForms()) == 0) {
            return $content;
        }

        $view = $this->getElement()->getView();

        if (! $view instanceof Zend_View_Interface) {
            return $content;
        }

        $markup = '<thead><tr>';

        foreach ($this->getColumns() as $column) {
            $label = isset($column['label']) ? $view->escape($view->translate($column['label'])) : '&nbsp;';
            $cssClass = isset($column['class']) ? $column['class'] : null;
            $markup .= "<th class=\"$cssClass\">" . $label . "</th>";
        }

        $markup .= '</tr></thead>';

        return $markup . $content;
    }

    public function setColumns($columns)
    {
        $this->_columns = $columns;
    }

    public function getColumns()
    {
        $columns = $this->getOption('columns');

        if (! is_null($columns)) {
            $this->removeOption('columns');
            $this->_columns = $columns;
        } else {
            if (method_exists($this->getElement(), 'getColumns')) {
                $this->_columns = $this->getElement()->getColumns();
            }
        }

        return $this->_columns;
    }
}
