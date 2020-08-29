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
 * @package     Module_Admin
 * @subpackage  Model
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2015, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Model for handling operations for enrichment keys.
 *
 * enrichmentkey.protected.modules
 * enrichmentkey.protected.migration
 *
 * @category    Application
 * @package     Module_Admin
 * @subpackage  Model
 */
class Admin_Model_EnrichmentKeys extends Application_Model_Abstract
{

    private $translationKeyPatterns = [
        'hint_Enrichment%s',
        'header_Enrichment%s',
        'group%s',
        'hint_group%s',
        'button_label_add_one_moreEnrichment%s',
        'button_label_deleteEnrichment%s'
    ];

    /**
     * Enrichment keys that are configured as protected.
     * @var array
     */
    private $_protectedKeys;

    /**
     * Reads list of protected enrichment keys from configuration.
     *
     * TODO separate configurations for modules and migration smells funny
     *
     * @return array
     */
    public function getProtectedEnrichmentKeys()
    {
        if (is_null($this->_protectedKeys)) {
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

            $this->_protectedKeys = $protectedKeys;
        }

        return $this->_protectedKeys;
    }

    /**
     * Sets list of protected enrichment keys in model.
     * @param $keys array
     */
    public function setProtectedEnrichmentKeys($keys)
    {
        $this->_protectedKeys = $keys;
    }

    /**
     * Setup additional translation keys for an enrichment.
     * @param $name Name of enrichment
     * @param null $oldName Optionally old name if it has been changed
     *
     * TODO create keys if they don't exist
     */
    public function createTranslations($name, $oldName = null)
    {
        $patterns = $this->translationKeyPatterns;

        $database = new Opus_Translate_Dao();
        $manager = new Application_Translate_TranslationManager();

        if (! is_null($oldName) && $name !== $oldName) {
            foreach ($patterns as $pattern) {
                $key = sprintf($pattern, $name);
                $oldKey = sprintf($pattern, $oldName);
                $database->renameKey($oldKey, $key, 'default');
            }
        } else {
            foreach ($patterns as $pattern) {
                $key = sprintf($pattern, $name);
                if (! $manager->keyExists($key)) {
                    $database->setTranslation($key, [
                        'en' => $name,
                        'de' => $name
                    ], 'default');
                }
            }
        }
    }

    /**
     * Remove translation keys if enrichment is deleted.
     * @param $name
     */
    public function removeTranslations($name)
    {
        $patterns = $this->translationKeyPatterns;

        $database = new Opus_Translate_Dao();

        foreach ($patterns as $pattern) {
            $key = sprintf($pattern, $name);
            $database->remove($key, 'default');
        }
    }

    public function getKeyPatterns()
    {
        return $this->translationKeyPatterns;
    }
}
