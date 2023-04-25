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

class Publish_View_Helper_Fieldset extends Zend_View_Helper_Abstract
{
    /** @var bool */
    protected $disable = false;

    public function fieldset()
    {
    }

    /**
     * @param array  $field
     * @param string $options
     * @return string
     */
    public function renderHtmlText($field, $options)
    {
        $fieldset = "";
        if (! isset($field['isLeaf'])) {
            $fieldset .= "\n\t\t\t\t<input type='text' class='form-textfield' name='" . $field['id'] . "' id='"
                . $field['id'] . "' ";
            if ($options !== null) {
                $fieldset .= $options . " ";
            } else {
                $fieldset .= "size='30' ";
            }

            if ($field['disabled'] === true) {
                $fieldset     .= " disabled='1' ";
                $this->disable = true;
            }

            $fieldset .= " title='" . htmlspecialchars($this->view->translate($field['hint']), ENT_QUOTES) . "' ";

            $value     = $field['value'];
            $value     = $value !== null ? htmlspecialchars($value, ENT_QUOTES) : '';
            $fieldset .= " value='{$value}' />\n";
            if (isset($field['desc'])) {
                $fieldset .= '<div class="description hint">' . $this->view->translate($field['desc']) . '</div>';
            }
        }
        return $fieldset;
    }

    /**
     * @param array  $field
     * @param string $options
     * @return string
     */
    public function renderHtmlTextarea($field, $options)
    {
        $fieldset = "\n\t\t\t\t<textarea name='" . $field['id'] . "' class='form-textarea' ";
        if ($options !== null) {
            $fieldset .= $options . " ";
        } else {
            $fieldset .= "cols='30' rows='5' ";
        }

        if ($field['disabled'] === true) {
            $fieldset     .= " disabled='1' ";
            $this->disable = true;
        }

        $fieldset .= " title='" . htmlspecialchars($this->view->translate($field['hint']), ENT_QUOTES) . "' ";
        $value     = $field['value'];
        $value     = $value !== null ? htmlspecialchars($value, ENT_QUOTES) : '';
        return $fieldset . " id='" . $field['id'] . "'>{$value}</textarea>";
    }

    /**
     * @param array $field
     * @param array $options TODO wofür wird der Parameter $options benötigt?
     * @return string
     */
    public function renderHtmlSelect($field, $options)
    {
        // TODO move style to CSS
        $fieldset  = "\n\t\t\t\t" . '<select style="width:300px" name="' . $field['id']
            . '" class="form-selectfield"  id="' . $field['id'] . '"';
        $fieldset .= ' title="' . htmlspecialchars($this->view->translate($field['hint']), ENT_QUOTES) . '"';
        if ($field['disabled'] === true) {
            $fieldset .= ' disabled="1" ';
            // TODO warum wird hier nicht auch $this->disable = true gesetzt?
        }
        $fieldset .= '>' . "\n\t\t\t\t\t";

        if ($options !== null) {
            $field['options'] = $options;
        }

        foreach ($field['options'] as $key => $option) {
            $fieldset .= '<option value="' . htmlspecialchars($key, ENT_QUOTES) . '" label="'
                . htmlspecialchars($option, ENT_QUOTES) . '"';

            // $key can be int or string, $field['value'] is always a string
            if ($option === $field['value'] || strval($key) === $field['value']) {
                $fieldset .= ' selected="selected"';
            }

            $fieldset .= '>';
            $fieldset .= htmlspecialchars($option, ENT_QUOTES) . '</option>' . "\n\t\t\t\t\t";
        }
        $fieldset .= '</select>' . "\n";

        if (isset($field['desc'])) {
            $fieldset .= '<div class="description hint">' . $this->view->translate($field['desc']) . '</div>';
        }
        return $fieldset;
    }

    /**
     * @param array  $field
     * @param string $options TODO wofür wird der Parameter $options benötigt?
     * @return string
     */
    public function renderHtmlCheckbox($field, $options)
    {
        $fieldset  = "<input type='hidden' name='" . $field['id'] . "' value='0' />";
        $fieldset .= "\n\t\t\t\t<input type='checkbox' class='form-checkbox' name='" . $field['id'] . "' id='"
            . $field['id'] . "' ";
        $fieldset .= "value='" . $field['value'] . "' ";
        if ($field['check'] === 'checked') {
            $fieldset .= " checked='checked' ";
        }

        if ($field['disabled'] === true) {
            $fieldset     .= " disabled='disabled' ";
            $this->disable = true;
        }

        $fieldset .= " />\n";

        return $fieldset;
    }

    /**
     * @param array  $field
     * @param string $options
     * @return string
     */
    public function renderHtmlFile($field, $options)
    {
        $fieldset  = "<input type='file' name='" . $field['id'] . "' id='" . $field['id']
            . "' enctype='multipart/form-data' ";
        $fieldset .= "title='" . htmlspecialchars($this->view->translate($field['hint']), ENT_QUOTES) . "' ";
        if ($options !== null) {
            $fieldset .= $options . " ";
        } else {
            $fieldset .= "size='30'";
        }
        $fieldset .= " />\n";

        return $fieldset;
    }

    /**
     * TODO:
     * Fehlerursache: multiplicity > 1 (Gruppe) aber Aufruf mit element
     * Wenn element aufgerufen wird, es sich aber um eine Gruppe handelt, wird hier eine Exception geworfen und die
     * Gruppe nicht gerendert.
     * Kann man verhindern, wenn man dem Array ein zusätzliches Feld "isGroup" o.ä. mitgibt, das überprüft, ob es
     * sich tatsächlich um eine Gruppe handelt.
     *
     * @param array $field
     * @return string
     */
    public function renderFieldsetErrors($field)
    {
        $fieldset = "";
        if (isset($field) && ! empty($field)) {
            $fieldset .= "<div class='form-errors'><ul>";
            foreach ($field as $err) {
                $fieldset .= "\n<li>" . htmlspecialchars($err, ENT_QUOTES) . "</li>";
            }
            $fieldset .= "\n</ul></div>";
        }
        return $fieldset;
    }

    /**
     * @param array $buttons
     * @return string
     */
    public function renderHtmlButtons($buttons)
    {
        $fieldset = "\n\t\t\t\t<div class='button-wrapper add-delete-wrapper'>";
        foreach ($buttons as $button) {
            if (! $this->disable) {
                $fieldset .= "<input type='submit' ";
                if (strstr($button['id'], 'Down') !== false) {
                    $fieldset .= "class='form-button down-button' ";
                } else {
                    if (strstr($button['id'], 'Up') !== false) {
                        $fieldset .= "class='form-button up-button' ";
                    }
                }

                if (strstr($button['id'], 'add') !== false) {
                    $fieldset .= "class='form-button add-button' ";
                } else {
                    if (strstr($button['id'], 'delete') !== false) {
                        $fieldset .= "class='form-button delete-button' ";
                    }
                }

                $fieldset .= "name='" . $button['id'] . "' id='" . $button['id'] . "' value='"
                    . htmlspecialchars($button['label'], ENT_QUOTES) . "' />&nbsp;";
            }
        }
        $fieldset .= "</div>";

        $this->disable = false;
        return $fieldset;
    }

    /**
     * @param array $hiddens
     * @return string
     */
    public function renderHtmlHidden($hiddens)
    {
        $fieldset = "";
        foreach ($hiddens as $hidden) {
            $fieldset .= "\n<input type='hidden' name='" . $hidden['id'] . "' id='" . $hidden['id'] . "' value='"
                . $hidden['value'] . "' />";
        }
        return $fieldset;
    }

    /**
     * @param string      $value
     * @param string|null $options
     * @param string|null $name
     * @return string
     */
    public function renderSubmit($value, $options = null, $name = null)
    {
        $submit = "\n\t\t<input type='submit' name='" . $name . "' id='" . $name . "' value='"
            . htmlspecialchars($this->view->translate($value), ENT_QUOTES) . "' ";
        if (isset($options)) {
            $submit .= $options . " />";
        }
        return $submit;
    }

    /**
     * @param string      $value
     * @param string|null $options TODO wofür wird der Parameter $options benötigt?
     * @param string|null $name
     * @return string
     */
    public function renderHidden($value, $options = null, $name = null)
    {
        return "<input type='hidden' name='" . $name . "' id='" . $name . "' value='" . $value . "' />";
    }

    /**
     * Returns the hint string for a required element
     *
     * @return string
     */
    public function getRequiredSign()
    {
        return "<span class='required' title='"
            . htmlspecialchars($this->view->translate('required_star_title'), ENT_QUOTES) . "'>*</span>";
    }

    /**
     * Returns a html String for displaying the group hint
     *
     * @param string $name Name of element or group
     * @return string
     */
    public function getFieldsetHint($name)
    {
        return "<div class='description hint'><p>" . $this->view->translate('hint_' . $name) . "</p></div>";
    }

    /**
     * @param string $name
     * @param string $label
     * @param bool   $required
     * @return string
     */
    public function getLabelFor($name, $label, $required)
    {
        $fieldset = "<label for='" . $name . "'>" . htmlspecialchars($this->view->translate($label), ENT_QUOTES);

        if ($required === 'required') {
            $fieldset .= $this->getRequiredSign();
        }

        $fieldset .= "</label>";
        return $fieldset;
    }

    /**
     * @param string $name
     * @return string
     */
    public function getLegendFor($name)
    {
        return "<legend>" . $this->view->translate($name) . "</legend>\n\t\t\n\t\t";
    }
}
