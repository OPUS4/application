<?php
/*
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
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Form_Builder_Helper_Abstract {

    private $debug = false;

    protected $log;

    public function __construct() {
        $this->log = Zend_Registry::get('Zend_Log');
    }

    public function buildForm($model) {
    }

    public function populateModel(Opus_Model_Abstract $model, array $data) {
    }

    /**
     * Adds description to a form element.
     * @param <type> $key
     * @param <type> $element
     */
    public function _addDescription($key, $element) {
        $translationKey = 'hint_' . $key;

        $translate = Zend_Registry::get('Zend_Translate');

        $translationContent = null;

        $helpAvailable = false;

        if ($this->_isHelpExists($key) !== false) {
            $helpAvailable = true;
            $pseudoView = new Zend_View;
            $helpUrl = $pseudoView->url(array('module' => 'home', 'controller' => 'index', 'action' => 'help', 'content' => 'help_' .  $key), null, true);
            $translationContent .= '<a href="'.$helpUrl.'" target="_blank">' . $translate->translate('help_formbuilder_field_link') . '</a>';
            // set the decorator and set escaping to false in order to show the link correctly
            $element->addDecorator('Description');
            $element->getDecorator('Description')->setEscape(false);
        }

        if ($this->debug === true) {
            $translationContent .= $translationKey;
        }

        if ($translate->isTranslated($translationKey)) {
            if ($this->debug === true) {
                $translationContent .= ': ' . $translate->translate($translationKey);
            }
            else {
                $translationContent .= $translate->translate($translationKey);
            }

            // set the decorator if that has not been done yet
            if ($helpAvailable === false) {
                $element->addDecorator('Description');
            }
        }

        if ($translationContent !== null) {
            $element->setDescription($translationContent);
        }
    }

    /**
     * Checks if a help key exists in the translation resources.
     * @param string $key
     * @return boolean
     */
    protected function _isHelpExists($key) {
        $translationKey = 'help_' . $key;
        $translate = Zend_Registry::get('Zend_Translate');
        return ($translate->isTranslated($translationKey)) ? true : false;
    }

}

?>
