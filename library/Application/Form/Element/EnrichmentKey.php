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
 * @package     Form_Element
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Formularelement für die Auswahl eines EnrichmentKeys.
 */
class Application_Form_Element_EnrichmentKey extends Application_Form_Element_Select
{

    public function init()
    {
        parent::init();

        // load enrichment keys only once in order to save database queries
        $options = Opus_EnrichmentKey::getAll(false);

        $this->setDisableTranslator(true); // keys are translated below if possible

        foreach ($options as $index => $option) {
            $keyName = $option->getName();

            // die folgenden beiden Enrichments sollen indirekt über Checkboxen
            // im Abschnitt DOI / URN verwaltet werden
            if ($keyName == 'opus.doi.autoCreate' || $keyName == 'opus.urn.autoCreate') {
                continue;
            }

            $this->addKeyNameAsOption($keyName);
        }

        $this->resetValidator();
    }

    private function addKeyNameAsOption($keyName)
    {
        $translationKey = 'Enrichment' . $keyName;

        $translator = Zend_Registry::get('Zend_Translate');
        if (! is_null($translator) && ($translator->isTranslated($translationKey))) {
            $this->addMultiOption($keyName, $translator->translate($translationKey));
        } else {
            $this->addMultiOption($keyName, $keyName);
        }
    }

    private function resetValidator()
    {
        $translator = Zend_Registry::get('Zend_Translate');
        $message = 'validation_error_unknown_enrichmentkey';
        if (! is_null($translator) && $translator->isTranslated($message)) {
            $message = $translator->translate($message);
        }

        $validator = new Zend_Validate_InArray(array_keys($this->getMultiOptions()));
        $validator->setMessage($message);
        $this->addValidator($validator);
    }

    /**
     * Prüft, ob der übergebene EnrichmentKey-Name bereits in der Auswahl existiert
     *
     * @param $keyName
     */
    public function hasKeyName($keyName)
    {
        return array_key_exists($keyName, $this->getMultiOptions());
    }

    /**
     * Fügt den übergebenen EnrichmentKey-Namen zur Auswahl hinzu, sofern es nicht bereits existiert.
     *
     * @param $keyName
     */
    public function addKeyNameIfMissing($keyName)
    {
        if (! $this->hasKeyName($keyName)) {
            $this->addKeyNameAsOption($keyName);
            $this->resetValidator();
        }
    }
}
