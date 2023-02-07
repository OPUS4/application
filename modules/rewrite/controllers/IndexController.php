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

use Opus\Common\Repository;

class Rewrite_IndexController extends Application_Controller_Action
{
    /**
     * maps arbitrary OPUS document indentifiers to the
     * corresponding internal document id, e.g., this function provides
     * a means to map OPUS 3.x document ids to OPUS 4 document ids
     */
    public function idAction()
    {
        $type  = $this->getRequest()->getParam('type');
        $value = $this->getRequest()->getParam('value');
        if (empty($type) || empty($value)) {
            $this->_helper->Redirector->redirectToAndExit(
                'index',
                ['failure' => 'missing argument'],
                'index',
                'home'
            );
            return;
        }
        $finder = Repository::getInstance()->getDocumentFinder();
        $ids    = $finder->setIdentifierValue($type, $value)->getIds();
        if (count($ids) < 1) {
            $this->_helper->Redirector->redirectToAndExit(
                'index',
                ['failure' => 'given id is unknown'],
                'index',
                'home'
            );
            return;
        }
        if (count($ids) > 1) {
            $this->_helper->Redirector->redirectToAndExit(
                'index',
                ['failure' => 'given id is not unique'],
                'index',
                'home'
            );
            return;
        }

        $this->_helper->Redirector->redirectToAndExit(
            'index',
            '',
            'index',
            'frontdoor',
            ['docId' => $ids[0]]
        );
    }

    /**
     * redirects OPUS 3.x file names to the corresponding OPUS 4.0 file names
     * in addition it returns HTTP code 301 (moved permanently)
     */
    public function opus3fileAction()
    {
        $docid    = $this->getRequest()->getParam('opus3id');
        $filename = $this->getRequest()->getParam('filename');
        if (empty($docid) || empty($filename)) {
            $this->_helper->Redirector->redirectToAndExit(
                'index',
                ['failure' => 'missing argument'],
                'index',
                'home'
            );
            return;
        }
        $finder = Repository::getInstance()->getDocumentFinder();
        $ids    = $finder->setIdentifierValue('opus3-id', $docid)->getIds();
        if (count($ids) < 1) {
            $this->_helper->Redirector->redirectToAndExit(
                'index',
                ['failure' => 'given opus3id is unknown'],
                'index',
                'home'
            );
            return;
        }
        if (count($ids) > 1) {
            $this->_helper->Redirector->redirectToAndExit(
                'index',
                ['failure' => 'given opus3id is not unique'],
                'index',
                'home'
            );
            return;
        }

        $this->_redirect(
            $this->getRequest()->getBaseUrl() . '/files/' . $ids[0] . '/' . $filename,
            [
                'prependBase' => false,
                'code'        => 301,
            ]
        );
    }
}
