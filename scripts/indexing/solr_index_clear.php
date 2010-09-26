<?php
/**
 * LICENCE
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @category    Application
 * @author      Henning Gerhardt <henning.gerhardt@slub-dresden.de>
 * @copyright   Copyright (c) 2010
 *              Saechsische Landesbibliothek - Staats- und Universitaetsbibliothek Dresden (SLUB)
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

define('APPLICATION_ENV', 'production');

// basic bootstrapping
require_once dirname(__FILE__) . '/../common/bootstrap.php';

$config = Zend_Registry::get('Zend_Config');

$host = $config->searchengine->solr->host;
$port = $config->searchengine->solr->port;
$baseUri = $config->searchengine->solr->path;
$EOL = "\n";

$solr = new Apache_Solr_Service($host, $port, $baseUri);

if (false === $solr->ping()) {
    echo 'Could not connect to solr service.' . $EOL;
    return;
}

$solr->deleteByQuery('*:*');
$solr->commit();
$solr->optimize();

echo 'Cleaning solr index on "' . $host . ':' . $port . $baseUri  . '" done.' . $EOL;

