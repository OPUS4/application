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
 * @package     View
 * @author      Ralf Claussnitzer (ralf.claussnitzer@slub-dresden.de)
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * 
 * This helper class defines only one method baseUrl() to retrieve the
 * application base url for absolute urls in views.
 * 
 */
class View_Helper_DocumentUrl extends Zend_View_Helper_Abstract {

    public function documentUrl($docId) {
        return $this->frontdoor($docId);
    }

    public function frontdoor($docId) {
        $url = array(
            'module'     => 'frontdoor',
            'controller' => 'index',
            'action'     => 'index',
            'docId'      => $docId,
        );
        return $this->view->url($url, 'default', true);
    }

    public function adminShow($docId) {
        $url = array(
            'module'     => 'admin',
            'controller' => 'document',
            'action'     => 'index',
            'id'         => $docId,
        );
        return $this->view->url($url, 'default', true);
    }

    public function adminFileManager($docId) {
        $url = array(
            'module'     => 'admin',
            'controller' => 'filemanager',
            'action'     => 'index',
            'docId'      => $docId,
        );
        return $this->view->url($url, 'default', true);
    }

    public function adminAccessManager($docId) {
        $url = array(
            'module'     => 'admin',
            'controller' => 'access',
            'action'     => 'listrole',
            'docid'      => $docId,
        );
        return $this->view->url($url, 'default', true);
    }


    public function adminOldEdit($docId) {
        $url = array(
            'module'     => 'admin',
            'controller' => 'documents',
            'action'     => 'edit',
            'id'         => $docId,
        );
        return $this->view->url($url, 'default', true);
    }

    public function adminPublish($docId) {
        $url = array(
            'module'     => 'admin',
            'controller' => 'document',
            'action'     => 'publish',
            'docId'      => $docId,
        );
        return $this->view->url($url, 'default', true);
    }

    public function adminUnpublish($docId) {
        $url = array(
            'module'     => 'admin',
            'controller' => 'document',
            'action'     => 'unpublish',
            'docId'      => $docId,
        );
        return $this->view->url($url, 'default', true);
    }

    public function adminDelete($docId) {
        $url = array(
            'module'     => 'admin',
            'controller' => 'document',
            'action'     => 'delete',
            'docId'      => $docId,
        );
        return $this->view->url($url, 'default', true);
    }

    public function adminDeletePermanent($docId) {
        $url = array(
            'module'     => 'admin',
            'controller' => 'document',
            'action'     => 'permanentdelete',
            'docId'      => $docId,
        );
        return $this->view->url($url, 'default', true);
    }

    public function adminUndelete($docId) {
        $url = array(
            'module'     => 'admin',
            'controller' => 'document',
            'action'     => 'publish',
            'docId'      => $docId,
        );
        return $this->view->url($url, 'default', true);
    }

    
}