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
 * @package     Module_Matheon
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Matheon_CheckDataController extends Controller_Action {

    public function init() {
        parent::init();

        $this->requirePrivilege('administrate');
    }

    public function indexAction() {
        $f = new Opus_DocumentFinder();
        $f->setServerState('published');

        $this->view->years = $f->groupedServerYearPublished();
        sort($this->view->years);
        $this->view->stats = array();
    }

    public function listAction() {
        $this->indexAction();

        $year = trim($this->_getParam('year'));
        if (preg_match('/^\d{4}$/', $year) < 1) {
            return $this->render('index');
        }

        $stats = array();
        $email2docIds = array();

        $f = new Opus_DocumentFinder();
        $f->setServerState('published');
//        $f->setServerDatePublishedRange($year, $year+1);

        foreach ($f->ids() AS $docId) {
            $document = new Opus_Document($docId);

            $docStats = array(
                'id'             => $docId,
                'authorEmails'   => $this->_getAuthorEmails($document),

                'errors'         => 0,
                'missingProject' => '',
                'missingMsc'     => '',
                'missingReferee' => '',
            );

            if ($this->_countCollectionRole($document, 'projects') === 0) {
                $docStats['errors']++;
                $docStats['missingProject'] = true;
            }

            if ($this->_countCollectionRole($document, 'msc') === 0) {
                $docStats['errors']++;
                $docStats['missingMsc'] = true;
            }

            if (count($document->getPersonReferee()) === 0) {
                $docStats['errors']++;
                $docStats['missingReferee'] = true;
            }

            if ($docStats['errors'] > 0) {
                foreach ($docStats['authorEmails'] AS $author) {
                    $email = trim($author['email']);
                    $name = trim($author['name']);

                    $key = $name;
                    if (!empty($email)) {
                        $key .= " <$email>";
                    }

                    if (!array_key_exists($key, $email2docIds)) {
                        $email2docIds[$key] = array(
                            'email' => $email,
                            'name' => $name,
                            'ids'  => array(),
                        );
                    }
                    $email2docIds[$key]['ids'][] = $docId;
                    $email2docIds[$key]['ids'] = array_unique($email2docIds[$key]['ids']);
                    sort($email2docIds[$key]['ids']);
                }
            }

            $stats[] = $docStats;
        }

        $this->view->stats = $stats;
        $this->view->email2docid = $email2docIds;
        return $this->render('index');
    }

    private function _countCollectionRole($document, $name) {
        $count = 0;
        foreach ($document->getCollection() AS $collection) {
            if ($collection->getRoleName() == $name) {
                $count++;
            }
        }
        return $count;
    }

    private function _getAuthorEmails($document) {
        $addresses = array();

        foreach ($document->getPersonAuthor() as $author) {
            $addresses[] = array(
                'name' => trim($author->getFirstName()) . " " . trim($author->getLastName()),
                'email' => trim($author->getEmail()),
            );
        }

        return $addresses;
    }

}
