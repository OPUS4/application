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
 * @category    TODO
 * @package     TODO
 * @author      Edouard Simon (edouard.simon@zib.de)
 * @copyright   Copyright (c) 2008-2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * 
 */
class Setup_Model_HelpPage extends Setup_Model_Abstract {

    /**
     *  base path for content files
     */
    protected $contentBasepath = '';

    /**
     * Path to directory containing content files. 
     * 
     * @param string $basePath name of directory used to read / write page content
     */
    public function setContentBasepath($basePath) {
        $path = realpath($basePath);
        $this->contentBasepath = $path;
    }

    /**
     * @see Description in abstract base class
     */
    public function toArray() {
        $resultArray = array();
        $translationUnits = $this->getTranslation();
        if ($translationUnits === false) // error reading files, this should not happen
            throw new Setup_Model_Exception('No tmx data found.');
        foreach ($translationUnits as $translationUnit => $variants) {
            $resultArray[$translationUnit] = array();
            foreach ($variants as $language => $text) {
                if (substr($text, -4) == '.txt') {
                    $resultArray[$translationUnit][$language] = array();
                    $this->addContentSource($this->contentBasepath . DIRECTORY_SEPARATOR . $text);
                    $resultArray[$translationUnit][$language]['filename'] = $text;
                    $resultArray[$translationUnit][$language]['contents'] = $this->getContent($this->contentBasepath . DIRECTORY_SEPARATOR . $text);
                } else {
                    $resultArray[$translationUnit][$language] = $text;
                }
            }
        }
        return $resultArray;
    }

    /**
     * @see Description in abstract base class
     */
    public function fromArray(array $data) {
        $resultData = array();
        $resultTmx = new Util_TmxFile();

        foreach ($data as $translationUnit => $variants) {
            foreach ($variants as $language => $contents) {
                if (is_array($contents)
                        && isset($contents['filename'])
                        && isset($contents['contents'])) {
                    $filePath = $this->contentBasepath . DIRECTORY_SEPARATOR . $contents['filename'];
                    $resultData[$filePath] = $contents['contents'];
                    $contents = $contents['filename'];
                }
                $resultTmx->setVariantSegment($translationUnit, $language, $contents);
            }
        }
        $this->setContent($resultData);
        $this->setTranslation($resultTmx->toArray());
    }

}
