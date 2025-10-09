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
 * TODO override setLabel for more robust translation
 */
class Application_Form_Element_DocumentType extends Application_Form_Element_Select
{
    public function init()
    {
        parent::init();

        $this->setLabel($this->getView()->translate($this->getName()));
        $this->setRequired(true);
        $this->setDisableTranslator(true);

        $options = $this->getSortedOptions();

        $this->setMultiOptions($options);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setValue($value)
    {
        $option = $this->getMultiOption($value);

        $translator = Application_Translate::getInstance();

        if ($translator !== null && $translator->isTranslated($value)) {
            $label = $translator->translate($value);
        } else {
            $label = $value;
        }

        if ($option === null) {
            $this->addMultiOption($value, $label);
        }

        return parent::setValue($value);
    }

    /**
     * Returns document type options sorted by label.
     *
     * @return array
     */
    public function getSortedOptions()
    {
        $docTypeHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('DocumentTypes');

        $options = $docTypeHelper->getDocumentTypes();

        $translator = Application_Translate::getInstance();

        $translatedOptions = [];

        foreach ($options as $value => $type) {
            if ($translator !== null && $translator->isTranslated($value)) {
                $label = $translator->translate($value);
            } else {
                $label = $value;
            }
            $translatedOptions[$value] = $label;
        }

        // TODO move language dependent sorting to separate class
        $replace = [
            'Ä' => 'Ae',
            'ä' => 'ae',
            'Ö' => 'Oe',
            'ö' => 'oe',
            'Ü' => 'Ue',
            'ü' => 'ue',
            'ß' => 'ss',
        ];

        uasort($translatedOptions, function ($a, $b) use ($replace) {
            $a = mb_strtolower($a, 'UTF-8');
            $b = mb_strtolower($b, 'UTF-8');
            $a = strtr($a, $replace);
            $b = strtr($b, $replace);
            return strcmp($a, $b);
        });

        return $translatedOptions;
    }
}
