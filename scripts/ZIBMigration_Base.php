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
 * @package     Module_Import
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2009, 2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id: Opus3Migration_Base.php 5890 2010-09-26 17:13:48Z tklein $
 */

require_once 'ZIBFileImport.php';
require_once 'ZIBSignatureImport.php';

class ZIBMigration_Base {

    protected $importfile;
    protected $path;
    protected $format = 'mysqldump';
    protected $magicPath = '/usr/share/file/magic'; # on Ubuntu-Systems this should be the magic path
    protected $stylesheet;
    protected $xslt;
    protected $docStack = array();
    protected $signaturePath;
    protected $signaturePassword;
    protected $startAtId = null;
    protected $stopAtId = null;

    protected function setStylesheet() {
        $this->stylesheet = '../import/stylesheets';
        $this->xslt = 'zib_opus3.xslt';
    }

    protected function loadImportFile() {
        $importData = new DOMDocument;
        $importData->load($this->importfile);
        return $importData;
    }

    protected function autosign($pass) {
        $gpg = new Opus_GPG();
        $docList = Opus_Document::getAllIds();
        foreach ($docList as $id) {
            $doc = new Opus_Document($id);
            foreach ($doc->getFile() as $file) {
                echo "Signing " . $file->getPathName();
                try {
                    $gpg->signPublicationFile($file, $pass);
                    echo "... done!\n";
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            }
        }
    }


    protected function importFiles($ipstart = null, $ipend = null) {
        echo "Importing files\n";
        $iprange = null;
        $rolename = null;
        $accessRole = null;
        // create IP-range for these documents
        if (empty($ipstart) !== true) {
            $rolename = $ipstart;
            if (empty($ipend) === true) {
                $ipend = $ipstart;
            } else {
                $rolename .= '-' . $ipend;
            }
            $iprange = new Opus_Iprange();
            $iprange->setStartingip($ipstart);
            $iprange->setEndingip($ipend);
            $iprange->setName('IP-' . $rolename);
            $ipid = $iprange->store();
        }

        if (empty($iprange) === false) {
            $accessRole = new Opus_Role();
            $accessRole->setName('IP-' . $rolename);
            $accessRole->store();
            $iprange->addRole($accessRole);
            $iprange->store();
        } else {
            $guestId = 0;
            // open document to the great bad internet
            // by assigning it to guest role
            $roles = Opus_Role::getAll();
            foreach ($roles as $role) {
                if ($role->getDisplayName() === 'guest') {
                    $guestId = $role->getId();
                }
            }
            if ($guestId > 0) {
                $accessRole = new Opus_Role($guestId);
            }
            if ($accessRole === null) {
                echo "Warning: no guest user has been found in database! Documents without IP-range will be imported without access permissions, so only the admin can view them!";
            }
        }
        
        $fileImporter = new ZIBFileImport($this->path, $this->magicPath, $accessRole);
        $doclist = Opus_Document::getAllIds();
        foreach ($doclist as $id) {
            $doc = new Opus_Document($id);
            //$opus3Id = $doc->getIdentifierOpus3()->getValue();
            $numberOfFiles = $fileImporter->loadFiles($id);
            //echo $numberOfFiles."\n";

            $mem_now = round(memory_get_usage() / 1024 / 1024);
            $mem_peak = round(memory_get_peak_usage() / 1024 / 1024);

            if ($numberOfFiles > 0) {
                echo $numberOfFiles . " file(s) have been imported successfully for document ID " . $doc->getId() . " -- memory $mem_now MB, peak memory $mem_peak (MB)\n";
            }
            unset($doc);
            unset($numberOfFiles);
        }
        unset($fileImporter);
        echo "finished!\n";
    }

    protected function importSignatures() {
        echo "Importing signatures\n";
  
        $sigImporter = new SignatureImport($this->signaturePath);
        $docList = Opus_Document::getAllIds();
        foreach ($docList as $id) {
            $doc = new Opus_Document($id);
            echo ".";
            $sigImporter->loadSignatureFiles($doc->getId());
            unset($doc);
        }
        echo "finished!";
    }

    /**
     * Removes all Mapping files needed for Import
     */
    protected function cleanup() {
        $filereader = opendir('../workspace/tmp/');
        while (false !== ($file = readdir($filereader))) {
            if (substr($file, -4) === '.map') {
                unlink('../workspace/tmp/' . $file);
            }
        }
    }

}