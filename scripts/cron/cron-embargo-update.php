<?php

/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the North Rhine-Westphalian Library Service Center,
 * the Cooperative Library Network Berlin-Brandenburg, the Saarland University
 * and State Library, the Saxon State Library - Dresden State and University
 * Library, the Bielefeld University Library and the University Library of
 * Hamburg University of Technology with funding from the German Research
 * Foundation and the European Regional Development Fund.
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
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2011-2016, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

require_once dirname(__FILE__) . '/../common/bootstrap.php';

/*
 * This cron job must be used if embargo dates are used in repository.
 *
 * This script finds documents with expired embargo date that have to been
 * updated after the expiration (ServerDateModified < EmbargoDate) and sets
 * ServerDateModified to the current time.
 *
 * The expiration of an embargo date does not change the document. Until the
 * date is expired access to the files of the document is blocked. After the
 * expiration access to the files is possible. However the document will not
 * be harvested again automatically. In order for the document to be included
 * in the next harvesting ServerDateModified needs to be updated.
 */

$docfinder = new Opus_DocumentFinder();

$now = new Opus_Date();
$now->setNow();

// Find documents with expired EmbargoDate and ServerDateModified < EmbargoDate
$docfinder->setEmbargoDateBeforeNotModifiedAfter(date('Y-m-d', time()));

$foundIds = $docfinder->ids();

// Update ServerDateModified for all found documents
Opus_Document::setServerDateModifiedByIds($now, $foundIds);
