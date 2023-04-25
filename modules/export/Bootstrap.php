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
 * @copyright   Copyright (c) 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Config;
use Opus\Common\Log;

/**
 * @phpcs:disable PSR2.Methods.MethodDeclaration
 */
class Export_Bootstrap extends Zend_Application_Module_Bootstrap
{
    protected function _initExport()
    {
        $updateInProgress = Application_Configuration::isUpdateInProgress();

        if (! Zend_Registry::isRegistered('Opus_Exporter')) {
            if (! $updateInProgress) {
                 Log::get()->warn(__METHOD__ . ' exporter not found');
            }
            return;
        }

        $exporter = Zend_Registry::get('Opus_Exporter');

        if ($exporter === null) {
            if (! $updateInProgress) {
                 Log::get()->warn(__METHOD__ . ' exporter not found');
            }
            return;
        }

        $config = Config::get();

        // only add XML export if user has access and stylesheet is configured
        if (isset($config->export->stylesheet->frontdoor)) {
            $exporter->addFormats([
                'xml' => [
                    'name'        => 'XML',
                    'description' => 'Export XML', // TODO frontdoor_export_xml
                    'module'      => 'export',
                    'controller'  => 'index',
                    'action'      => 'index',
                    'search'      => false,
                    'params'      => [
                        'export'     => 'xml',
                        'searchtype' => 'id',
                        'stylesheet' => $config->export->stylesheet->frontdoor,
                    ],
                ],
            ]);
        }

        if (isset($config->export->stylesheet->search)) {
            $exporter->addFormats([
                'xml2' => [
                    'name'        => 'XML',
                    'description' => 'Export XML',
                    'module'      => 'export',
                    'controller'  => 'index',
                    'action'      => 'index',
                    'frontdoor'   => false,
                    'params'      => [
                        'export'     => 'xml',
                        'stylesheet' => $config->export->stylesheet->search,
                    ],
                ],
            ]);
        }

        $exporter->addFormats([
            'csv' => [
                'name'        => 'CSV',
                'description' => 'Export CSV',
                'module'      => 'export',
                'controller'  => 'index',
                'action'      => 'csv',
                'frontdoor'   => false,
            ],
        ]);
    }
}
