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
 * @package     View_Helper
 * @author      Susanne Gottwald <gottwald@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Description of RenderForm
 *
 */
class View_Helper_Element extends Zend_View_Helper_Abstract {

    public $view;

    /**
     * method to render specific elements of an form
     * @param <type> $type element type that has to rendered
     * @param <type> $value value of element or Zend_Form_Element
     * @param <type> $name name of possible hidden element
     * @return element to render in view
     */
    public function element($value, $options = null, $type=null, $name = null) {

        if ($name == null && $value == null) {
            $error_message = $this->view->translate('template_error_unknown_field');
            return "<br/><div style='width: 400px; color:red;'>" . $error_message . "</div><br/><br/>";
        } else {
            $method = "_render" . $type;
            if (method_exists($this, $method) === true) {
                $result = $this->$method($value, $options, $name);
            } else {
                $result = $this->_renderElement($value, $options, $name);
            }
            return $result;
        }
    }

    /**
     *
     * @param <type> $element
     */
    protected function _renderElement($element, $options=null, $name=null) {
        $elementfield = "<fieldset class='fieldset'><legend class='legend'>" . $this->view->translate($element["label"]) . "</legend>\n\t\t\n\t\t";
        $elementfield .= "<table width='100%' class='table'>\n\t<tr>\n\t\t<td width='25%'>\n\t\t\t";
        $elementfield .= "<label for='" . $element["id"] . "'>" . $element["label"] . "</label></td><td>";

        switch ($element["type"]) {
            case "Zend_Form_Element_Textarea" :
                $elementfield .= "\n\t\t\t<textarea name='" . $element["id"] . "' id='" . $element["id"] . "' ";
                if ($options !== null)
                    $elementfield .= $options . " ";
                else
                    $elementfield .= "cols='30' rows='10' ";
                $elementfield .= " title='" . $this->view->translate($element["hint"]) . "'>";
                $elementfield .= $element["value"] . "</textarea>";

                if ($element["req"] === 'required')
                    $elementfield .= $this->_getRequiredSign();
                $elementfield .= "\n\t\t</td>\n\t</tr>\n\t";
                $elementfield .= "<tr>\n\t\t<td colspan='2'>";

                if ($element["error"] != null) {
                    $elementfield .= "<ul class='errors'>";
                    foreach ($element["error"] AS $err)
                        $elementfield .= "\n\t\t\t<li>" . $err . "</li>";
                    $elementfield .= "\n\t\t</ul>";
                }
                break;

            case "Zend_Form_Element_Text" :
                $elementfield .= "\n\t\t\t<input type='text' name='" . $element["id"] . "' id='" . $element["id"] . "'";
                if ($options !== null)
                    $elementfield .= $options . " ";
                else
                    $elementfield .= "size='30' ";
                $elementfield .= " title='" . $this->view->translate($element["hint"]) . "' ";
                if ($element["disabled"] === true) {
                    $elementfield .= " disabled='1' ";
                    $disable = true;
                }
                $elementfield .= "value='" . $element["value"] . "' />\n\t\t";
                if (isset($element["desc"]))
                    $elementfield .= "<p class='description'>" . $this->view->translate($element["desc"]) . "</p>";

                if ($element["req"] === 'required')
                    $elementfield .= $this->_getRequiredSign();
                $elementfield .= "</td>\n\t</tr>\n\t";
                $elementfield .= "<tr>\n\t\t<td colspan='2'>";
                if ($element["error"] != null) {
                    $elementfield .= "<ul class='errors'>";
                    foreach ($element["error"] AS $err)
                        $elementfield .= "\n\t\t\t<li>" . $err . "</li>";
                    $elementfield .= "\n\t\t</ul>";
                }
                break;

            case "Zend_Form_Element_Select":
                $elementfield .= "\n\t\t\t<select name='" . $element["id"] . "' id='" . $element["id"] . "'>\n\t\t\t\t";
                foreach ($element["options"] AS $key => $option) {
                    $elementfield .= "<option value='" . $key . "' label='" . $option . "'";
                    $elementfield .= " title='" . $this->view->translate($element["hint"]) . "' ";

                    if ($option === $element["value"] || $key === $element["value"])
                        $elementfield .= " selected='selected'>";
                    else
                        $elementfield .= ">";
                    $elementfield .= $option . "</option>\n\t\t\t\t";
                }
                $elementfield .= "</select>";
                if ($element["req"] === 'required')
                    $elementfield .= $this->_getRequiredSign();
                $elementfield .= "</td>\n\t</tr>\n\t<tr>\n\t\t<td colspan='2'>";
                if ($element["error"] != null) {
                    $elementfield .= "<ul class='errors'>";
                    foreach ($element["error"] AS $err)
                        $elementfield .= "\n\t\t\t<li>" . $err . "</li>";
                    $elementfield .= "\n\t\t</ul>";
                }
                break;
        }
        $elementfield .= "</td>\n\t</tr>\n</table></fieldset>\n\n";
        return $elementfield;
    }

    /**
     *
     * @param <type> $name 
     */
    protected function _renderSubmit($value, $options=null, $name=null) {
        $submit = "\n\t\t<input type='submit' name='" . $name . "' id='" . $name . "' value='" . $this->view->translate($value) . "'/>";
        return $submit;
    }

    /**
     *
     * @param <type> $value
     * @param <type> $name
     * @return string 
     */
    protected function _renderHidden($value, $options = null, $name=null) {
        $hiddenfield = "<input type='hidden' name='" . $name . "' id='" . $name . "' value='" . $value . "' />";
        return $hiddenfield;
    }

    /**
     * returns the hint string for a required element
     * @return <type> 
     */
    protected function _getRequiredSign() {
        return "<span class='required'>*</span>";
    }

}

?>
