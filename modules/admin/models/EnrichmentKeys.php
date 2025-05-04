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

use Opus\Common\EnrichmentKey;
use Opus\Translate\Dao;

/**
 * Model for handling operations for enrichment keys.
 *
 * enrichmentkey.protected.modules
 * enrichmentkey.protected.migration
 */
class Admin_Model_EnrichmentKeys extends Application_Model_Abstract
{
    /** @var string[] */
    private $translationKeyPatterns = [
        'hint'         => 'hint_Enrichment%s',
        'header'       => 'header_Enrichment%s',
        'group'        => 'group%s',
        'groupHint'    => 'hint_group%s',
        'buttonAdd'    => 'button_label_add_one_moreEnrichment%s',
        'buttonDelete' => 'button_label_deleteEnrichment%s',
    ];

    /**
     * Enrichment keys that are configured as protected.
     *
     * @var array
     */
    private $protectedKeys;

    /**
     * Reads list of protected enrichment keys from configuration.
     *
     * TODO separate configurations for modules and migration smells funny
     *
     * @return array
     */
    public function getProtectedEnrichmentKeys()
    {
        if ($this->protectedKeys === null) {
            $config = $this->getConfig();

            $protectedKeys = [];

            if (isset($config->enrichmentkey->protected->modules)) {
                $protectedKeys = explode(',', $config->enrichmentkey->protected->modules);
            } else {
                $this->getLogger()->warn(
                    'config key \'enrichmentkey.protected.modules\' is not defined in config file'
                );
            }

            if (isset($config->enrichmentkey->protected->migration)) {
                $protectedKeys = array_merge(
                    $protectedKeys,
                    explode(',', $config->enrichmentkey->protected->migration)
                );
            } else {
                $this->getLogger()->warn(
                    'config key \'enrichmentkey.protected.migration\' is not defined in config file'
                );
            }

            $this->protectedKeys = $protectedKeys;
        }

        return $this->protectedKeys;
    }

    /**
     * Sets list of protected enrichment keys in model.
     *
     * @param array $keys
     */
    public function setProtectedEnrichmentKeys($keys)
    {
        $this->protectedKeys = $keys;
    }

    /**
     * Setup additional translation keys for an enrichment.
     *
     * @param string      $name Name of enrichment
     * @param string|null $oldName Optionally old name if it has been changed
     * @param array|null  $translations
     *
     * TODO create keys if they don't exist
     * TODO what happens if renameKey into keys that already exist?
     * TODO support more languages
     */
    public function createTranslations($name, $oldName = null, $translations = null)
    {
        $patterns = $this->translationKeyPatterns;

        $database = new Dao();
        $manager  = new Application_Translate_TranslationManager();

        if ($translations === null) {
            $translations = [];
        }

        if ($oldName !== null && $name !== $oldName) {
            $patterns['label'] = 'Enrichment%s'; // TODO avoid custom handling for 'label'

            foreach ($patterns as $pattern) {
                $key    = sprintf($pattern, $name);
                $oldKey = sprintf($pattern, $oldName);
                $database->renameKey($oldKey, $key, 'default');
            }
        } else {
            if (isset($translations['label'])) {
                $patterns['label'] = 'Enrichment%s'; // TODO avoid custom handling for 'label'
            }
            foreach ($patterns as $patternName => $pattern) {
                $key = sprintf($pattern, $name);
                if (! $manager->keyExists($key)) {
                    $enValue = $name;
                    if (isset($translations[$patternName]['en'])) {
                        $enValue = $translations[$patternName]['en'];
                    }

                    $deValue = $name;
                    if (isset($translations[$patternName]['de'])) {
                        $deValue = $translations[$patternName]['de'];
                    }

                    $database->setTranslation($key, [
                        'en' => $enValue,
                        'de' => $deValue,
                    ], 'default');
                }
            }
        }
    }

    /**
     * @param string $name Name of enrichment key
     * @return string[]
     *
     * TODO 'label' translation handled separately (here and in admin form) - unify handling?
     */
    public function getTranslations($name)
    {
        $patterns = $this->translationKeyPatterns;

        $patterns['label'] = 'Enrichment%s';

        $manager = new Application_Translate_TranslationManager();

        $translations = [];

        $allTranslations = $manager->getMergedTranslations();

        foreach ($patterns as $patternName => $pattern) {
            $key = sprintf($pattern, $name);
            if (isset($allTranslations[$key]['translations'])) {
                $translations[$patternName] = $allTranslations[$key]['translations'];
            }
        }

        return $translations;
    }

    /**
     * Remove translation keys if enrichment is deleted.
     *
     * @param string $name
     */
    public function removeTranslations($name)
    {
        $patterns = $this->translationKeyPatterns;

        $database = new Dao();

        $patterns['label'] = 'Enrichment%s'; // TODO avoid custom handling for 'label'

        foreach ($patterns as $pattern) {
            $key = sprintf($pattern, $name);
            $database->remove($key, 'default');
        }
    }

    /**
     * @return string[]
     */
    public function getKeyPatterns()
    {
        return $this->translationKeyPatterns;
    }

    /**
     * @param string $name
     * @return string[]
     */
    public function getEnrichmentConfig($name)
    {
        $enrichment       = EnrichmentKey::fetchByName($name);
        $enrichmentConfig = $enrichment->toArray();

        // remove NULL values
        $enrichmentConfig = array_filter($enrichmentConfig, function ($value) {
            return $value !== null;
        });

        // remove 'Type' from type name (TODO this should not be necessary later)
        if (isset($enrichmentConfig['Type'])) {
            $type                     = preg_replace('/Type$/', '', $enrichmentConfig['Type']);
            $enrichmentConfig['Type'] = $type;
        }

        // add translations to configuration
        $translations = $this->getTranslations($name);
        if (count($translations) > 0) {
            $enrichmentConfig['translations'] = $translations;
        }

        // handle options
        if (isset($enrichmentConfig['Options'])) {
            $options                     = json_decode($enrichmentConfig['Options'], true);
            $enrichmentConfig['Options'] = $options;
        }

        // use lowercase keys in yaml
        $enrichmentConfig = array_change_key_case($enrichmentConfig, CASE_LOWER);

        return $enrichmentConfig;
    }

    /**
     * @return string[]
     */
    public function getSupportedKeys()
    {
        return array_keys($this->getKeyPatterns());
    }
}
