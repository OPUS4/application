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
 * @package     Module_Publish
 * @author      Henning Gerhardt (henning.gerhardt@slub-dresden.de)
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Shows available document types
 *
 * @category    Application
 * @package     Module_Publish
 */
class Overview extends Zend_Form {

    /**
     * Looks in a specific directory for xml files.
     *
     * @return array
     */
    protected function _getXmlDocTypeFiles() {
        // TODO Do not use a hardcoded path to xml files
        $xml_path = '../config/xmldoctypes/';
        $result = array();
        if ($dirhandle = opendir($xml_path)) {
            while (false !== ($file = readdir($dirhandle))) {
                $path_parts = pathinfo($file);
                $filename = $path_parts['filename'];
                $basename = $path_parts['basename'];
                $extension = $path_parts['extension'];
                if (($basename === '.') or ($basename === '..') or ($extension !== 'xml')) {
                    continue;
                }
                $result[$filename] = $filename;
            }
            closedir($dirhandle);
            asort($result);
        }
        return $result;
    }

    /**
     * Build a simple select form for document types
     *
     * @return void
     */
    public function init() {
        $listOptions = $this->_getXmlDocTypeFiles();
        $select = new Zend_Form_Element_Select('selecttype');
        $select->setLabel('selecttype')
            ->setMultiOptions($listOptions)
            ->addValidator('NotEmpty');

        $gpgkeyavailable = new Zend_Form_Element_Radio('gpgkey');
        $gpgkeyavailable->setLabel('gpgkeyavailable')
            ->setMultiOptions(array('0' => 'answer_no', '1' => 'answer_yes'))
            ->setValue('0');

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('process');

        $this->addElements(array($select, $gpgkeyavailable, $submit));
    }

}