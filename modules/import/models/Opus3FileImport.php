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
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Opus3FileImport 
{
    /**
     * Holds the path to the fulltexts in Opus3
     *
     * @var string  Defaults to null.
     */
    protected $_path = null;

    /**
     * Do some initialization on startup of every action
     *
     * @param string $fulltextPath Path to the Opus3-fulltexts
     * @return void
     */
    public function __construct($fulltextPath)
    {
        // Initialize member variables.
        $this->_path = $fulltextPath;
    }
    
    /**
     * Loads an old Opus ID
     *
     * @param string $opusId Id of the document in the old Opus-system
     * @return void
     */
    public function loadFiles($opusId)
    {
        // Search the ID-directory in fulltext tree
        echo $this->searchDir($this->_path, $opusId);
        // if you got it, build a Opus_File-Object
        // look if there are other files
        // return all files in an array
    }
    
    private function getFiles($path) 
    {
        if (false === is_dir($path))
            return false;

        $dirs = array($path);
        while (NULL !== ($dir = array_pop($dirs)))
        {
            if ($dh = opendir($dir))
            {
                while (false !== ($file = readdir($dh)))
                {
                    if( $file == '.' || $file == '..')
                        continue;
                    $path = $dir . '/' . $file;
                    if (is_dir($path) && $path === $search)
                        return $path;
                }
                closedir($dh);
            }
        }
        return false;    	
    }
    
    private function searchDir($from, $search)
    {
        if (false === is_dir($from))
            return false;

        $dirs = array($from);
        while (NULL !== ($dir = array_pop($dirs)))
        {
            if ($dh = opendir($dir))
            {
                while (false !== ($file = readdir($dh)))
                {
                    if( $file == '.' || $file == '..')
                        continue;
                    $path = $dir . '/' . $file;
                    if (is_dir($path) && $path === $search)
                        return $path;
                }
                closedir($dh);
            }
        }
        return false;
    } 
}