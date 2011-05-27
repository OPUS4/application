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
 * @package     View_Helper
 * @author      Susanne Gottwald <gottwald@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class View_Helper_CollectionOverview extends Zend_View_Helper_Abstract {

    public $view;
    public $session;
    public $document;

    /**
     * method to render specific elements of an form
     * @param <type> $type element type that has to rendered
     * @param <type> $value value of element or Zend_Form_Element
     * @param <type> $name name of possible hidden element
     * @return element to render in view
     */
    public function collectionOverview() {
        $this->session = new Zend_Session_Namespace('Publish');
        $log = Zend_Registry::get('Zend_Log');

        //already choosen collection
        $fieldset_start1 = "<fieldset><legend>" . $this->view->translate('collections_choosen') . "</legend>\n\t\t\n\t\t";
        $fieldset_end1 = "</fieldset>";

        //current collection hierachie
        $fieldset_start2 = "<fieldset><legend>" . $this->view->translate('collections_current_choice') . "</legend>\n\t\t\n\t\t";
        $fieldset_end2 = "</fieldset>";

        if (is_null($this->session->documentId)) {
            return "";
        }
        //$this->document = new Opus_Document($this->session->documentId);

        $step = $this->session->step;
        $overview2 = "";

        if ($step > 1) {
            $overview2 .= '<ul class="nav browsing">';
            for ($i = 1; $i <= $step - 1; $i++) {
                $overview2 .= '<li>' . htmlspecialchars($this->view->translate($this->session->collection['collection' . $i . 'Name'])) . '</li>';
            }
            $overview2 .= '</ul>';
        }        

        $collections = Opus_Collection::fetchCollectionIdsByDocumentId($this->session->documentId);

        if (empty($collections)) {
            $empty = $fieldset_start1 . "<b>" . $this->view->translate('no_collections_choosen') . "</b>" . $fieldset_end1;
            if ($overview2 !== "")
                return $empty . $fieldset_start2 . $overview2 . $fieldset_end2;

            return $empty;
        }

        $overview = "";
        foreach ($collections as $collId) {
            $coll = new Opus_Collection($collId);
            $role = new Opus_CollectionRole($coll->getRoleId());
            $overview .= "<p>" . $this->view->translate('collections_choosen_root') . ": <b>" .
                    $this->view->translate(htmlspecialchars($role->getDisplayName())) .
                    "</b><br/>" . $this->view->translate('collections_choosen_entry') . ": " . htmlspecialchars($coll->getDisplayName());
        }



        if ($overview2 !== "")
            return $fieldset_start1 . $overview . $fieldset_end1 . $fieldset_start2 . $overview2 . $fieldset_end2;

        return $fieldset_start1 . $overview . $fieldset_end1;
    }

}
?>
