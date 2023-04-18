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
 * Dekorator für die Ausgabe eines Tabellenkopfes.
 */
class Application_Form_Decorator_TableHeader extends Zend_Form_Decorator_Abstract
{
    /** @var array */
    private $columns;

    /**
     * @param string $content
     * @return string
     */
    public function render($content)
    {
        // Zeige Tabellenkopf nur wenn es Einträge (Unterformulare) gibt
        if (count($this->getElement()->getSubForms()) === 0) {
            return $content;
        }

        $view = $this->getElement()->getView();

        if (! $view instanceof Zend_View_Interface) {
            return $content;
        }

        $markup = '<thead><tr>';

        foreach ($this->getColumns() as $column) {
            $label    = isset($column['label']) ? $view->escape($view->translate($column['label'])) : '&nbsp;';
            $cssClass = $column['class'] ?? null;
            $markup  .= "<th class=\"$cssClass\">" . $label . "</th>";
        }

        $markup .= '</tr></thead>';

        return $markup . $content;
    }

    /**
     * @param array $columns
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;
    }

    /**
     * @return array|null
     *
     * TODO WHAT does it do? Explain!
     */
    public function getColumns()
    {
        $columns = $this->getOption('columns');

        if ($columns !== null) {
            $this->removeOption('columns');
            $this->columns = $columns;
        } else {
            $element = $this->getElement();
            if ($element !== null && method_exists($element, 'getColumns')) {
                $this->columns = $element->getColumns();
            }
        }

        return $this->columns;
    }
}
