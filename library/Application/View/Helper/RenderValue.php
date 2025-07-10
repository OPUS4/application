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
 * @copyright   Copyright (c) 2025, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\LoggingTrait;

/**
 * View helper for rendering enrichment values.
 *
 * TODO expand to render any model field (default fields and enrichments)
 *      Problem is that enrichment keys currently can be identical to field names ('Type')
 */
class Application_View_Helper_RenderValue extends Application_View_Helper_Abstract
{
    use LoggingTrait;

    /** @var Zend_Config */
    private $modelConfig;

    /**
     * @param string $value
     * @param string $fieldName
     * @return string
     */
    public function renderValue($value, $fieldName = null)
    {
        $fieldConfig = $this->getFieldConfig($fieldName);

        $output     = $value;
        $viewHelper = null;

        if (isset($fieldConfig->viewHelper)) {
            $viewHelperName = $fieldConfig->viewHelper;
            $viewHelper     = $this->getViewHelper($viewHelperName);
            if ($viewHelper !== null) {
                $output = $viewHelper->$viewHelperName($value);
            }
        }

        if (
            isset($fieldConfig->escape) && filter_var($fieldConfig->escape, FILTER_VALIDATE_BOOLEAN)
            || ! isset($fieldConfig->escape) && $viewHelper === null
        ) {
            return htmlspecialchars($output);
        } else {
            return $output;
        }
    }

    /**
     * @param string $fieldName
     * @return Zend_Config|null
     * @throws Zend_Config_Exception
     */
    public function getFieldConfig($fieldName)
    {
        if ($fieldName === null) {
            return null;
        }

        $modelConfig = $this->getModelConfig();

        if (! isset($modelConfig->model->enrichment->$fieldName)) {
            return null;
        }

        $fieldConfig = $modelConfig->model->enrichment->$fieldName;

        if (! isset($fieldConfig->type)) {
            return $fieldConfig;
        }

        $fieldType = $fieldConfig->type;

        if (! isset($modelConfig->model->type->$fieldType)) {
            return $fieldConfig;
        }

        $typeConfig   = $modelConfig->model->type->$fieldType;
        $mergedConfig = array_merge($typeConfig->toArray(), $fieldConfig->toArray());

        return new Zend_Config($mergedConfig);
    }

    /**
     * @return Zend_Config
     * @throws Zend_Config_Exception
     */
    public function getModelConfig()
    {
        if (null === $this->modelConfig) {
            $this->modelConfig = new Zend_Config_Ini(APPLICATION_PATH . '/application/configs/model.ini');
        }

        return $this->modelConfig;
    }

    /**
     * @param Zend_Config $modelConfig
     * @return $this
     */
    public function setModelConfig($modelConfig)
    {
        $this->modelConfig = $modelConfig;
        return $this;
    }

    /**
     * @param string $name
     * @return Zend_View_Helper_Interface
     * @throws Zend_Exception
     */
    public function getViewHelper($name)
    {
        try {
            $viewHelper = Zend_Registry::get('Opus_View')->getHelper($name);
        } catch (Zend_Loader_PluginLoader_Exception $ex) {
            return null;
        }

        return $viewHelper;
    }
}
