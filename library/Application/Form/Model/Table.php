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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Model\ModelInterface;

/**
 * Formular für die Anzeige der Model-Tabelle (CRUD - indexAction).
 *
 * TODO class is tied to Application_Controller_ActionCRUD - resolve
 */
class Application_Form_Model_Table extends Application_Form_Abstract
{
    /**
     * Modelle die angezeigt werden sollen.
     *
     * @var array
     */
    private $models;

    /**
     * Konfiguration für Spalten.
     *
     * @var array
     */
    private $columns;

    /**
     * ViewScript for rendering table.
     *
     * @var string
     */
    private $viewScript = 'modeltable.phtml';

    /** @var Zend_Controller_Action */
    private $controller;

    /**
     * Initialisiert Formular.
     *
     * Setzt Decorators so, daß das Rendering in einem View Script erfolgt.
     */
    public function init()
    {
        parent::init();
        $this->initDecorators();
    }

    /**
     * Initialisiert die Decorators für die Tabelle.
     */
    public function initDecorators()
    {
        $this->setDecorators(
            [
                'PrepareElements',
                [
                    'ViewScript',
                    [
                        'viewScript' => $this->getViewScript(),
                    ],
                ],
            ]
        );
    }

    /**
     * Liefert die Spaltenkonfiguration.
     *
     * @return array|null
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Setzt die Spaltenkonfiguration.
     *
     * @param array $columns
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;
    }

    /**
     * Liefert das Label für eine Spalte.
     *
     * @param int $index Index der Spalte angefangen bei 0
     * @return string|null
     */
    public function getColumnLabel($index)
    {
        if (isset($this->columns[$index]['label'])) {
            return $this->columns[$index]['label'];
        } else {
            return null;
        }
    }

    /**
     * Liefert gesetzte Modelle.
     *
     * @return array|null
     */
    public function getModels()
    {
        return $this->models;
    }

    /**
     * Setzt Modelle für Anzeige.
     *
     * @param array $models
     */
    public function setModels($models)
    {
        if ($models !== null && ! is_array($models)) {
            throw new Application_Exception(__METHOD__ . 'Parameter must be array.');
        }
        $this->models = $models;
    }

    /**
     * Setzt ViewScript für die Ausgabe der Modeltabelle.
     *
     * @param string $name
     */
    public function setViewScript($name)
    {
        if ($name !== null) {
            $this->viewScript = $name;
        } else {
            $this->viewScript = 'modeltable.phtml';
        }
        $this->initDecorators();
    }

    /**
     * Liefert Namen des ViewScripts für die Ausgabe der Modeltabelle.
     *
     * @return string
     */
    public function getViewScript()
    {
        return $this->viewScript;
    }

    /**
     * Sets controller object for model table.
     *
     * @param Zend_Controller_Action $controller CRUD Controller object for model
     *
     * TODO make independent from controller class
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }

    /**
     * Determines if a link for the show action should be rendered.
     *
     * @return bool true - link should be rendered
     */
    public function isRenderShowActionLink()
    {
        if ($this->controller !== null) {
            if (! method_exists($this->controller, 'getShowActionEnabled')) {
                $this->getLogger()->debug('The used controller does not have the method getShowActionEnabled.');
            } else {
                return $this->controller->getShowActionEnabled();
            }
        }

        return true;
    }

    /**
     * Determines if an object is modifiable and links for edit and remove should be rendered.
     *
     * @param ModelInterface $model Model object
     * @return bool true - model can be modified and links should be rendered
     */
    public function isModifiable($model)
    {
        if ($this->controller !== null) {
            if (! method_exists($this->controller, 'isModifiable')) {
                $this->getLogger()->debug('The used controller does not have the method isModifiable.');
            } else {
                return $this->controller->isModifiable($model);
            }
        }

        return true;
    }

    /**
     * Determines if an object can be deleted and a link for removing it should be rendered.
     *
     * @param ModelInterface $model Model object
     * @return bool true - model can be deleted and link should be rendered
     */
    public function isDeletable($model)
    {
        if ($this->controller !== null) {
            if (! method_exists($this->controller, 'isDeletable')) {
                $this->getLogger()->debug('The used controller does not have the method isDeletable.');
            } else {
                return $this->controller->isDeletable($model);
            }
        }

        return true;
    }

    /**
     * @param ModelInterface $model
     * @return bool
     * @throws Zend_Exception
     */
    public function isUsed($model)
    {
        if ($this->controller !== null) {
            if (! method_exists($this->controller, 'isUsed')) {
                $this->getLogger()->debug('The used controller does not have the method isUsed.');
            } else {
                return $this->controller->isUsed($model);
            }
        }

        return false;
    }

    /**
     * @param ModelInterface $model
     * @return bool
     * @throws Zend_Exception
     */
    public function isProtected($model)
    {
        if ($this->controller !== null) {
            if (! method_exists($this->controller, 'isProtected')) {
                $this->getLogger()->debug('The used controller does not have the method isProtected.');
            } else {
                return $this->controller->isProtected($model);
            }
        }

        return false;
    }

    /**
     * @param ModelInterface $model
     * @return string|null
     * @throws Zend_Exception
     */
    public function getRowCssClass($model)
    {
        if ($this->controller !== null) {
            if (! method_exists($this->controller, 'getRowCssClass')) {
                $this->getLogger()->debug('The used controller does not have the method getRowCssClass.');
            } else {
                return $this->controller->getRowCssClass($model);
            }
        }

        return null;
    }

    /**
     * @param ModelInterface $model
     * @return string|null
     * @throws Zend_Exception
     */
    public function getRowTooltip($model)
    {
        if ($this->controller !== null) {
            if (! method_exists($this->controller, 'getRowTooltip')) {
                $this->getLogger()->debug('The used controller does not have the method getRowTooltip.');
            } else {
                return $this->controller->getRowTooltip($model);
            }
        }

        return null;
    }
}
