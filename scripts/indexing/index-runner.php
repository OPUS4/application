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
 * @author      Ralf Claussnitzer <ralf.claussnitzer@slub-dresden.de>
 * @author      Henning Gerhardt <henning.gerhardt@slub-dresden.de>
 * @copyright   Copyright (c) 2010
 *              Saechsische Landesbibliothek - Staats- und Universitaetsbibliothek Dresden (SLUB)
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id: index-runner.php 5765 2010-06-07 14:15:00Z claussni $
 */

define('APPLICATION_ENV', 'production');

// basic bootstrapping
require_once dirname(__FILE__) . '/../common/bootstrap.php';

// set up job runner
$jobrunner = new Opus_Job_Runner;
$jobrunner->setLogger(Zend_Registry::get('Zend_Log'));
// no waiting between jobs
$jobrunner->setDelay(0);
// set a limit of 100 index jobs per run
$jobrunner->setLimit(100);

$indexWorker = new Qucosa_Job_Worker_IndexOpusDocument;
$indexWorker->setIndex(Zend_Registry::get('Qucosa_Search_Index'));
$indexWorker->setFileBasePathPattern(Zend_Registry::get('Zend_Config')->file->destinationPath . '/$documentId');
$jobrunner->registerWorker($indexWorker);

// run processing
$jobrunner->run();

