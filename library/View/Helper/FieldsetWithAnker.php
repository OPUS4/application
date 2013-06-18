<?php
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
 * View Helper für die Ausgabe eines Fieldset mit Anker.
 * 
 * Wenn im Formular das Attribut 'ankerName' gesetzt ist, wird der Wert für das erzeugen eines Ankers im Element 
 * Legend verwendet.
 */
class View_Helper_FieldsetWithAnker extends Zend_View_Helper_Fieldset {
    
    public function fieldset($name, $content, $attribs = null)
    {
        $info = $this->_getInfo($name, $content, $attribs);
        extract($info);

        // get legend
        $legend = '';
        if (isset($attribs['legend'])) {
            $legendString = trim($attribs['legend']);
            if (!empty($legendString)) {
                $legend = '<legend>';
                if (isset($attribs['ankerName'])) {
                    $ankerName = trim($attribs['ankerName']);
                    $legend .= '<a name="' . $ankerName . '">' 
                            . (($escape) ? $this->view->escape($legendString) : $legendString) . '</a>';
                }
                else {
                    $legend .= (($escape) ? $this->view->escape($legendString) : $legendString);
                }
                $legend .= '</legend>' . PHP_EOL;
            }
            unset($attribs['legend']);
        }

        // get id
        if (!empty($id)) {
            $id = ' id="' . $this->view->escape($id) . '"';
        } else {
            $id = '';
        }

        // render fieldset
        $xhtml = '<fieldset'
               . $id
               . $this->_htmlAttribs($attribs)
               . '>'
               . $legend
               . $content
               . '</fieldset>';

        return $xhtml;
    }
    
}
