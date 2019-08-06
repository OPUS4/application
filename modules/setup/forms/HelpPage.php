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
 * @package     Setup
 * @author      Edouard Simon (edouard.simon@zib.de)
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 *
 */
class Setup_Form_HelpPage extends Zend_Form_SubForm
{

    public function buildFromArray(array $array)
    {
        $translator = $this->getTranslator();
        if (isset($array[$this->getName()])) {
            $array = $array[$this->getName()];
        }
        foreach ($array as $translationUnit => $variants) {
            $subForm = new Zend_Form_SubForm();
            $this->addSubForm($subForm, $translationUnit);
            foreach ($variants as $language => $text) {
                if (is_array($text)) {
                    if (! isset($text['filename']) || ! isset($text['contents'])) {
                        throw new Exception('Invalid data structure for HelpPage Form');
                    }
                    $langSubForm = new Zend_Form_SubForm();
                    $langSubForm->addElement('hidden', 'filename');
                    $langSubForm->addElement(
                        'textarea',
                        'contents',
                        ['label' => $translator->translate("setup_language_$language")]
                    );
                    $subForm->addSubForm($langSubForm, $language);
                } else {
                    $subForm->addElement(
                        'text',
                        $language,
                        ['label' => $translator->translate("setup_language_$language"),
                        'attribs' => ['size' => 90]]
                    );
                }
            }
            $subForm->setLegend($translationUnit);
        }
        return $this;
    }

    public function isValid($data)
    {
        $this->buildFromArray($data);
        return parent::isValid($data);
    }

    public function populate(array $values)
    {
        $this->buildFromArray($values);
        parent::populate($values);
    }
}
