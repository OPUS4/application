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
 * @package     Application_Search
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Class Application_Search_Facet
 *
 * TODO should translation be handled here or outside? Maybe view helper for facet (like form elements)
 */
class Application_Search_Facet
{

    private $name;

    private $selected;

    private $open;

    private $showFacetExtender;

    private $values;

    private $translated = false;

    private $translationPrefix;

    private $accessResource;

    private $heading;

    private $limit;

    private $sort;

    private $indexField;

    public function __construct($name, $options = null)
    {
        $this->name = $name;
        $this->setOptions($options);
        $this->init();
    }

    protected function setOptions($options)
    {
        if (! is_array($options)) {
            return;
        }

        foreach ($options as $name => $value) {
            $methodName = 'set' . ucfirst($name);
            if (method_exists($this, $methodName)) {
                $this->$methodName($value);
            }
        }
    }

    public function init()
    {
    }

    public function isSelected()
    {
        return strlen(trim($this->selected)) > 0;
    }

    public function getSelected()
    {
        return $this->selected;
    }

    public function setSelected($value)
    {
        $this->selected = $value;
    }

    /**
     * @return string
     *
     * TODO move enrichment handling into subclass
     */
    public function getHeading()
    {
        if (! is_null($this->heading)) {
            return $this->heading;
        }

        $name = $this->getName();

        if (substr($name, 0, strlen('enrichment_')) === 'enrichment_') {
            $enrichment = substr($name, strlen('enrichment_'));
            $facetHeadingKey = "Enrichment$enrichment";
        } else {
            $facetHeadingKey = "{$name}_facet_heading";
        }

        return $facetHeadingKey;
    }

    public function setHeading($heading)
    {
        $this->heading = $heading;
    }

    public function getItems()
    {
    }

    public function isShowFacetExtender()
    {
        return $this->showFacetExtender;
    }

    public function setShowFacetExtender($show)
    {
        $this->showFacetExtender = $show;
    }

    public function isOpen()
    {
        return $this->open;
    }

    public function setOpen($open)
    {
        $this->open = $open;
    }

    public function setValues($values)
    {
        $this->values = $values;
    }

    public function getValues()
    {
        return $this->values;
    }

    public function getSize()
    {
        return sizeof($this->values);
    }

    /**
     * TODO Should probably move into Opus\Search\Result\Facet(Item)
     * TODO $facetValue = $this->translate('Document_ServerState_Value_' . ucfirst($facetValue));
     *
     */
    public function getLabel($value)
    {
        if ($this->isTranslated()) {
            $prefix = $this->getTranslationPrefix();
            if (! is_null($prefix)) {
                $value = $prefix . ucfirst($value); // TODO ucfirst should not be needed (migth not always apply)
            }
            return $this->getTranslator()->translate($value);
        } else {
            return $value;
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function isTranslated()
    {
        return $this->translated;
    }

    public function setTranslated($translated)
    {
        $this->translated = filter_var($translated, FILTER_VALIDATE_BOOLEAN);
    }

    public function getTranslator()
    {
        return \Zend_Registry::get('Zend_Translate');
    }

    public function setTranslationPrefix($prefix)
    {
        $this->translationPrefix = $prefix;
    }

    public function getTranslationPrefix()
    {
        return $this->translationPrefix;
    }

    public function setAccessResource($resource)
    {
        $this->accessResource = $resource;
    }

    public function getAccessResource()
    {
        return $this->accessResource;
    }

    /**
     * @param $key
     * @return bool
     * @throws Application_Exception
     *
     * TODO Refactor so there is a class for facets that answers this question
     */
    public function isAllowed()
    {
        $resource = $this->getAccessResource();

        if (! is_null($resource)) {
            $accessControl = new Application_Controller_Action_Helper_AccessControl();
            return $accessControl->accessAllowed('documents');
        }

        return true;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    public function getSort()
    {
        return $this->sort;
    }

    public function setSort($sort)
    {
        $this->sort = $sort;
    }

    public function getIndexField()
    {
        if (! is_null($this->indexField)) {
            return $this->indexField;
        } else {
            return $this->getName();
        }
    }

    public function setIndexField($indexField)
    {
        $this->indexField = $indexField;
    }
}
