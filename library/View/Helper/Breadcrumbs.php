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
 * @package     View
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * View Helper um Breadcrumbs in Administration zu rendern.
 */
class View_Helper_Breadcrumbs extends Zend_View_Helper_Navigation_Breadcrumbs {
    
    private $suffixSeparatorDisabled = false;
    
    private $suffix = null;
    
    private $replacement = null; 
    
    public function setSuffix($suffix) {
        $this->suffix = $suffix;
        return $this;
    }
    
    public function setSuffixSeparatorDisabled($disabled) {
        $this->suffixSeparatorDisabled = $disabled;
        return $this;
    }
    
    public function setReplacement($replacement) {
        $this->replacement = $replacement;
        return $this;
    }
    
    /**
     * Rendert den kompletten Breadcrumbs Pfad für die aktuelle Seite.
     * 
     * @param Zend_Navigation_Container $container
     * @return string
     */
    public function renderStraight(Zend_Navigation_Container $container = null) {
        $helpPage = true; // TODO determine based on navigation-modules.xml

        $html = '<div class="breadcrumbsContainer"><div class="wrapper">';

        if ($helpPage) {
            $iconUrl = $this->view->layoutPath() . '/img/theme/admin/ic_help.png';
            $pageUrl = 'http://opus4.kobv.de'; // TODO get from navigation-modules.xml
            $html .= '<a href="'
                . $pageUrl
                . '" class="admin-help"><img src="'
                . $iconUrl
                . '" width="25" height="20" alt="Help" title="Help"/></a>';
        }

        if (is_null($this->replacement)) {
            $html .= parent::renderStraight($container);
        }
        else {
            $html .= $this->replacement;
        }

        if (!is_null($this->suffix)) {
            if ($this->suffixSeparatorDisabled !== true) {
                $html .= ' ' . $this->getSeparator() . ' '; 
            }
            $html .= $this->suffix;
        }

        $html .= '</div></div>';
        
        return strlen($html) ? $this->getIndent() . $html : '';
    }
    
}


