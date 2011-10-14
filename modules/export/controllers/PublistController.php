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
 * @package     Module_Export
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id: PublistController.php 9112 2011-10-13 10:07:40Z gmaiwald $
 */

class Export_PublistController extends Controller_Xml {

    private $log;
    private $config;
    private $exportFile;


    public function init() {
        parent::init();
        $this->log = Zend_Registry::get('Zend_Log');
        $this->config = Zend_Registry::get('Zend_Config');
    }

    public function indexAction() {

        $this->exportFile = $this->config->workspacePath . DIRECTORY_SEPARATOR . "export" . DIRECTORY_SEPARATOR . "export.xml";
        if (!is_readable($this->exportFile)) {
            throw new Application_Exception('exportfile does not exist or is not readable');
        }

        $styleParam = $this->getRequest()->getParam('style');
        if (is_null($styleParam)) {
            throw new Application_Exception('style is not specified');
        }

        if (!is_readable($this->view->getScriptPath('') . 'stylesheets' . DIRECTORY_SEPARATOR . 'style_' . $styleParam . '.xslt')) {
            throw new Application_Exception('style is not supported');
        }

        $authorParam = $this->getRequest()->getParam('author');
        if (is_null($authorParam)) {
            throw new Application_Exception('author is not specified');
        }
        
        $this->_xml->load($this->exportFile);
        
        $this->normalize();
        $this->filter($authorParam );
        $this->export($styleParam);

    }

    private function normalize() {
        /* Normalization  of year for xslt:sort  */
        /* Filter all unpublished documents */
        $this->loadStyleSheet($this->view->getScriptPath('') . 'stylesheets' . DIRECTORY_SEPARATOR . 'normalize.xslt');
    }
    
    private function filter($author) {
        /* Filter all documents that dont belong to specified author  */
        $this->_xml = $this->_proc->transformToDoc($this->_xml);
        $this->_proc->setParameter('', 'author', $author);
        $this->loadStyleSheet($this->view->getScriptPath('') . 'stylesheets' . DIRECTORY_SEPARATOR . 'filter.xslt');
    }

    private function export($style) {
        $this->_xml = $this->_proc->transformToDoc($this->_xml);
        $this->loadStyleSheet($this->view->getScriptPath('') . 'stylesheets' . DIRECTORY_SEPARATOR . 'style_' .$style . '.xslt');
    }


}

?>