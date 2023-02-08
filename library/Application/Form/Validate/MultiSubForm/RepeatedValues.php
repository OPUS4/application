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

class Application_Form_Validate_MultiSubForm_RepeatedValues implements Application_Form_Validate_MultiSubFormInterface
{
    /** @var string */
    private $elementName;

    /** @var string */
    private $message;

    /** @var Zend_Form_Element[]|null  */
    private $otherElements;

    /**
     * @param string     $elementName
     * @param string     $message
     * @param array|null $otherElements
     * @throws Application_Exception
     */
    public function __construct($elementName, $message, $otherElements = null)
    {
        if ($elementName === null || strlen(trim($elementName)) === 0) {
            throw new Application_Exception(__METHOD__ . ' #1 argument must not be null or empty.');
        }

        if ($message === null || strlen(trim($message)) === 0) {
            throw new Application_Exception(__METHOD__ . ' #2 argument must not be null or empty.');
        }

        if ($otherElements !== null && ! is_array($otherElements)) {
            $otherElements = [$otherElements];
        }

        $this->elementName   = $elementName;
        $this->otherElements = $otherElements;

        $translator = Application_Translate::getInstance();

        if ($translator->isTranslated($message)) {
            $this->message = $translator->translate($message);
        } else {
            $this->message = $message;
        }
    }

    /**
     * @param array      $data
     * @param array|null $context
     * @return true
     */
    public function isValid($data, $context = null)
    {
        return true;
    }

    /**
     * @param Zend_Form  $form
     * @param array      $data
     * @param null|array $context
     */
    public function prepareValidation($form, $data, $context = null)
    {
        $position = 0;

        $values = $this->getValues($this->elementName, $data);

        foreach ($form->getSubForms() as $name => $subform) {
            if (array_key_exists($name, $data)) {
                $element = $subform->getElement($this->elementName);
                if ($element !== null) {
                    if ($this->otherElements === null) {
                        $element->addValidator(
                            new Application_Form_Validate_DuplicateValue(
                                $values,
                                $position++,
                                $this->message
                            )
                        );
                    } else {
                        $element->addValidator(
                            new Application_Form_Validate_DuplicateMultiValue(
                                $values,
                                $position++,
                                $this->message,
                                $this->otherElements
                            )
                        );
                    }
                }
            }
        }
    }

    /**
     * @param string $name
     * @param array  $context
     * @return array
     */
    public function getValues($name, $context)
    {
        $values = [];

        foreach ($context as $index => $subform) {
            $value = null;

            if ($this->otherElements === null) {
                // einfache Werte aus einem Feld
                if (isset($subform[$name])) {
                    $value = $subform[$name];
                }
            } else {
                // komplexe Werte aus mehreren Feldern
                $value = [];

                foreach ($this->otherElements as $element) {
                    if (isset($subform[$element])) {
                        $value[] = $subform[$element];
                    }
                }

                if (isset($subform[$name])) {
                    $value[] = $subform[$name];
                }
            }

            if ($value !== null) {
                $values[] = $value;
            }
        }

        return $values;
    }

    /**
     * @return string
     */
    public function getElementName()
    {
        return $this->elementName;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return Zend_Form_Element[]|null
     */
    public function getOtherElements()
    {
        return $this->otherElements;
    }
}
