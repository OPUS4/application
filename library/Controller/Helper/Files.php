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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id: RoleController.php 7862 2011-04-13 11:33:16Z tklein $
 */

/**
 * Helper for basic file and folder operations.
 *
 * Can be used to list files in a directory matching a pattern.
 *
 * TODO Implement/improve functionality
 */
class Controller_Helper_Files extends Zend_Controller_Action_Helper_Abstract {
    
    /**
     * Lists files in import folder.
     */
    static public function listFiles($folder) {
        $files = array();

        $files = scandir($folder);

        $files = array_filter($files, 'Controller_Helper_Files::filterCallback');

        return $files;
    }


   static public function getAllowedFileTypes() {
       $config = Zend_Registry::get('Zend_Config');

       if (isset($config->publish->filetypes->allowed)) {
           $allowed = explode(',', $config->publish->filetypes->allowed);
           Util_Array::trim($allowed);
           return $allowed;
       }
       else {
           return null;
       }
   }

   static public function filterCallback($filename) {
       $allowed = Controller_Helper_Files::getAllowedFileTypes();

       // filter hidden files
       if (strpos($filename, '.') === 0) {
           return false;
       }

       // TODO filter links

       // TODO filter directories

       if (!empty($allowed)) {
           foreach ($allowed as $fileType) {
               if (fnmatch('*.' . $fileType, $filename)) {
                   return true;
               }
           }

           return false;
       }

       return true;
   }

}

?>
