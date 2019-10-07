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
 *
 * @category    Application
 * @package     View
 */
class Application_View_Helper_Breadcrumbs extends Zend_View_Helper_Navigation_Breadcrumbs
{

    private $_suffixSeparatorDisabled = false;

    private $_suffix = null;

    private $_replacement = null;

    /**
     * Setze String, der an die Breadcrumbs gehängt wird.
     *
     * @param $suffix
     * @return $this
     */
    public function setSuffix($suffix)
    {
        $this->_suffix = $suffix;
        return $this;
    }

    /**
     * Disable Trennzeichen zum Suffix.
     *
     * @param $disabled
     * @return $this
     */
    public function setSuffixSeparatorDisabled($disabled)
    {
        $this->_suffixSeparatorDisabled = $disabled;
        return $this;
    }

    /**
     * Ersetze den Breadcrumbs Text komplett mit gesetztem Wert.
     * @param $replacement
     * @return $this
     */
    public function setReplacement($replacement)
    {
        $this->_replacement = $replacement;
        return $this;
    }

    /**
     * Rendert den kompletten Breadcrumbs Pfad für die aktuelle Seite.
     *
     * @param Zend_Navigation_Container $container
     * @return string
     */
    public function renderStraight(Zend_Navigation_Container $container = null)
    {
        if (null === $container) {
            $container = $this->getContainer();
        }

        $active = $this->findActive($container, 0);

        if ($active) {
            $page = $active['page'];
            $helpPage = $page->helpUrl;
        } else {
            $helpPage = null;
        }

        $html = '<div class="breadcrumbsContainer"><div class="wrapper">';

        if (! is_null($helpPage)) {
            $title = $this->view->translate('page-help-link-title');

            $iconUrl = $this->view->layoutPath() . '/img/theme/admin/ic_help.png';
            $pageUrl = $helpPage; // TODO evtl. baseUrl verwenden und helpUrl durch helpUri ersetzen
            $html .= '<a href="'
                . $pageUrl
                . '" class="admin-help" target="_blank"><img src="'
                . $iconUrl
                . '" width="25" height="20" alt="' . $title . '" title="' . $title . '"/></a>';
        }


        if (is_null($this->_replacement)) {
            $html .= parent::renderStraight($container);
        } else {
            $html .= $this->_replacement;
        }

        if (! is_null($this->_suffix)) {
            if ($this->_suffixSeparatorDisabled !== true) {
                $html .= $this->getSeparator();
            }
            $html .= $this->_suffix;
        }

        $html .= '</div></div>';

        return strlen($html) ? $this->getIndent() . $html : '';
    }
}
