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
 * @copyright   Copyright (c) 2023, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Log;
use Opus\Common\Security\Realm;

/**
 * View helper for rendering metadata formats list.
 */
class Oai_View_Helper_ListMetadataFormats extends Application_View_Helper_Abstract
{
    /**
     * Returns XML for rendering the metadata formats list.
     *
     * @return string
     */
    public function listMetadataFormats()
    {
        $output = '';

        $serverFactory = new Oai_Model_ServerFactory();
        $oaiConfig     = Oai_Model_OaiConfig::getInstance();
        $formats       = $oaiConfig->getFormats();

        if ($formats) {
            $output = PHP_EOL . '    <ListMetadataFormats>';
            foreach ($formats as $formatPrefix) {
                $server               = $serverFactory->create($formatPrefix);
                $prefix               = $server->getPrefixLabel() ?: $formatPrefix;
                $schemaUrl            = $server->getSchemaUrl();
                $metadataNamespaceUrl = $server->getMetadataNamespaceUrl();

                if ($server->isVisible() && (! $server->isAdminOnly() || Realm::getInstance()->checkModule('admin'))) {
                    if ($prefix) {
                        if (empty($schemaUrl)) {
                            $message = 'No schema url set.';
                            Log::get()->err($message);
                            throw new Exception($message);
                        }

                        if (empty($metadataNamespaceUrl)) {
                            $message = 'No metadata namespace url set.';
                            Log::get()->err($message);
                            throw new Exception($message);
                        }

                        $output .= PHP_EOL;
                        $output .= '      <metadataFormat>' . PHP_EOL;
                        $output .= "        <metadataPrefix>$prefix</metadataPrefix>" . PHP_EOL;
                        $output .= "        <schema>$schemaUrl</schema>" . PHP_EOL;
                        $output .= "        <metadataNamespace>$metadataNamespaceUrl</metadataNamespace>" . PHP_EOL;
                        $output .= '      </metadataFormat>';
                    }
                }
            }

            $output .= PHP_EOL;
            $output .= '    </ListMetadataFormats>';
            $output .= PHP_EOL . '  ';
        }

        return $output;
    }
}
