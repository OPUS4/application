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
 * @category    TODO
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2008-2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Checks if a enrichmentkey already exists.
 */
class Form_Validate_EnrichmentkeyAvailable extends Zend_Validate_Abstract {

    /**
     * Constants for enrichmentkey not available anymore.
     */
    const NOT_AVAILABLE = 'isAvailable';
    const PROTECT = 'isProtected';

    /**
     * Error messages.
     */
    protected $_messageTemplates = array(
        self::NOT_AVAILABLE => 'admin_enrichmentkey_error_name_exists',
        self::PROTECT => 'admin_enrichmentkey_error_name_protected'
    );

    /**
     * Checks if an enrichmentkey already exists.
     */
    public function isValid($value, $context = null) {

        $value = (string) $value;
        $this->_setValue($value);

        $name = null;

        if (is_array($context)) {
            if (isset($context['oldName'])) {
                $name = $context['oldName'];
            }
        }
        elseif (is_string($context)) {
            $name = $context;
        }

        if (!($name === $value) && $this->_isEnrichmentKeyUsed($value)) {
            $this->_error(self::NOT_AVAILABLE);
            return false;
        }

        if (!($name === $value) && $this->_isEnrichmentKeyProtected($value)) {
            $this->_error(self::PROTECT);
            return false;
        }

        return true;
    }

    /**
     * Checks if a enrichmentkey already used.
     * @param string $login
     * @return boolean
     */
    protected function _isEnrichmentKeyUsed($name) {

        $enrichmentkey = Opus_EnrichmentKey::fetchByName($name);
        if (is_null($enrichmentkey )) {
            return false;
        }
        return true;
    }

    /**
     * Checks if a enrichmentkey already used.
     * @param string $login
     * @return boolean
     */
    protected function _isEnrichmentKeyProtected($name) {

        $config = Zend_Registry::get('Zend_Config');
        $protectedEnrichmentkeys = array();


        if (!isset($config->enrichmentkey->protected->modules)) {
           throw new Opus_Exception("config key 'enrichmentkey.protected.modules' is not defined in config file");
        }

        foreach(explode(',', $config->enrichmentkey->protected->modules) as $protectedEnrichmentkey) {
            array_push($protectedEnrichmentkeys, $protectedEnrichmentkey);

        }

        if (!isset($config->enrichmentkey->protected->migration)) {
           throw new Opus_Exception("config key 'enrichmentkey.protected.migration' is not defined in config file");
        }

        foreach(explode(',', $config->enrichmentkey->protected->migration) as $protectedEnrichmentkey) {
            array_push($protectedEnrichmentkeys, $protectedEnrichmentkey);

        }

        if (!in_array($name, $protectedEnrichmentkeys)) {
            return false;
        }
        
        return true;
    }

}

