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
 * @author      Felix Ostrowski <ostrowski@hbz-nrw.de>
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Builds the main navigation menu.
 *
 * As soon as navigation gets more complex, it should probably either be
 * implemented as a controller or a plugin.
 *
 * @category    Application
 * @package     View
 */
class View_Helper_Menu
{
    /**
     * Defines which menu should be rendered.
     *
     * @var string  Defaults to null.
     */
    protected $_type = null;

    /**
     * Holds the current view object.
     *
     * @var Zend_View_Interface
     */
    protected $_view = null;

    public function setView(Zend_View_Interface $view) {
        $this->_view = $view;
    }

    /**
     * Build an array representation of the menu.
     *
     * @return array (Nested) array containing menu items.
     */
    protected function _buildMenu()
    {
        $menus['home']['index'] = array(
            'module' => 'home',
            'controller' => 'index',
            'action' => 'index'
            );
        $homedir = Zend_Controller_Front::getInstance()->getControllerDirectory('home');
        foreach (glob($homedir . '/../views/scripts/index/*') as $filename) {
            $filename = basename($filename, '.phtml');
            if ($filename === 'index') continue;
            $menus['home'][$filename] = array(
                'module' => 'home',
                'controller' => 'index',
                'action' => $filename
                );
        }
        $menus['primary'] = array(
                'Publish' => array(
                    'module' => 'publish',
                    ),
                'Search' => array(
                    'module' => 'search',
                    ),
                );
        $menus['secondary'] = array(
                'Admin' => array(
                    'module' => 'admin',
                    ),
                );
        if (is_array($menus[$this->_type])) return $menus[$this->_type];
        else throw new Exception('Menu not found');
    }

    /**
     * Build an unordered list representing the menu.
     *
     * TODO: I don't really like the idea of concatenating Html here. We should
     * consider using a view partial to render the menu. I just don't know how
     * to do it (yet).
     *
     * @return String
     */
    protected function _generateHtmlMenu($menu)
    {
        $fc = Zend_Controller_Front::getInstance();
        $activeModule = $fc->getRequest()->getModuleName();
        $activeController = $fc->getRequest()->getControllerName();
        $activeAction = $fc->getRequest()->getActionName();
        $html = '';
        if (is_array($menu)) {
            foreach ($menu as $label => $entry) {
                // Beware! Hardcoding of expected translation string postfix.
                $transKey = '';
                $transPrefix = '';
                if (array_key_exists('module', $entry)) {
                    $transKey = $entry['module'];
                    $transPrefix = '_modulename';
                }
                if (array_key_exists('controller', $entry)) {
                    $transKey .= '_' . $entry['controller'];
                    $transPrefix = '_controllername';
                }
                if (array_key_exists('action', $entry)) {
                    $transKey .= '_' . $entry['action'];
                    $transPrefix = '_actionname';
                }
                $label = $this->_view->translate($transKey . $transPrefix);
                if (array_key_exists('module', $entry)
                    and array_key_exists('controller', $entry)
                    and array_key_exists('action', $entry)) {
                    $url = $this->_view->url(array(
                                'module' => $entry['module'],
                                'controller' => $entry['controller'],
                                'action' => $entry['action'],
                                ),
                            null, true);
                    $link = '<a href="' . $url . '">' . $label . '</a>';
                    if ($entry['action'] === $activeAction
                        and $entry['controller'] === $activeController
                        and $entry['module'] === $activeModule) {
                        $html .= '<li class="active">' . $link . '</li>';
                    } else {
                        $html .= '<li>' . $link . '</li>';
                    }
                } else if (array_key_exists('module', $entry)
                    and array_key_exists('controller', $entry)) {
                    $url = $this->_view->url(array(
                                'module' => $entry['module'],
                                'controller' => $entry['controller'],
                                ),
                            null, true);
                    $link = '<a href="' . $url . '">' . $label . '</a>';
                    if ($entry['controller'] === $activeController
                        and $entry['module'] === $activeModule) {
                        $html .= '<li class="active">' . $link . '</li>';
                    } else {
                        $html .= '<li>' . $link . '</li>';
                    }
                } else if (array_key_exists('module', $entry)) {
                    $url = $this->_view->url(array(
                                'module' => $entry['module'],
                                ),
                            null, true);
                    $link = '<a href="' . $url . '">' . $label . '</a>';
                    if ($entry['module'] === $activeModule) {
                        $html .= '<li class="active">' . $link . '</li>';
                    } else {
                        $html .= '<li>' . $link . '</li>';
                    }
                }
            }
        }
        return $html;
    }

    /**
     * Return an instance of the view helper.
     *
     * @param  String  $type
     * @return Opus_View_Helper_Menu
     */
    public function menu($type) {
        $this->_type = $type;
        return $this;
    }

    /**
     * Return view helper output.
     *
     * @return String
     */
    public function __toString() {
        return '<ul class="navigation ' . $this->_type . '">' .
            $this->_generateHtmlMenu($this->_buildMenu()) . '</ul>';
    }
}
