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
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * TODO should translation be handled here or outside? Maybe view helper for facet (like form elements)
 */
class Application_Search_Facet
{
    /** @var string */
    private $name;

    /** @var string|null */
    private $selected;

    /** @var bool */
    private $open;

    /** @var bool */
    private $showFacetExtender;

    /** @var array */
    private $values;

    /** @var bool */
    private $translated = false;

    /** @var string|null */
    private $translationPrefix;

    /** @var string|null */
    private $accessResource;

    /** @var string */
    private $heading;

    /** @var int */
    private $limit;

    /** @var string */
    private $sort;

    /** @var string|null */
    private $indexField;

    /**
     * @param string     $name
     * @param null|array $options
     */
    public function __construct($name, $options = null)
    {
        $this->name = $name;
        $this->setOptions($options);
        $this->init();
    }

    /**
     * @param array $options
     */
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

    /**
     * @return bool
     */
    public function isSelected()
    {
        return $this->selected !== null && strlen(trim($this->selected)) > 0;
    }

    /**
     * @return string
     */
    public function getSelected()
    {
        return $this->selected;
    }

    /**
     * @param string|null $value
     */
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
        if ($this->heading !== null) {
            return $this->heading;
        }

        $name = $this->getName();

        if (substr($name, 0, strlen('enrichment_')) === 'enrichment_') {
            $enrichment      = substr($name, strlen('enrichment_'));
            $facetHeadingKey = "Enrichment$enrichment";
        } else {
            $facetHeadingKey = "{$name}_facet_heading";
        }

        return $facetHeadingKey;
    }

    /**
     * @param string $heading
     */
    public function setHeading($heading)
    {
        $this->heading = $heading;
    }

    public function getItems()
    {
    }

    /**
     * @return bool
     */
    public function isShowFacetExtender()
    {
        return $this->showFacetExtender;
    }

    /**
     * @param bool $show
     */
    public function setShowFacetExtender($show)
    {
        $this->showFacetExtender = $show;
    }

    /**
     * @return bool
     */
    public function isOpen()
    {
        return $this->open;
    }

    /**
     * @param bool $open
     */
    public function setOpen($open)
    {
        $this->open = $open;
    }

    /**
     * @param array $values
     */
    public function setValues($values)
    {
        $this->values = $values;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return count($this->values);
    }

    /**
     * TODO Should probably move into Opus\Search\Result\Facet(Item)
     * TODO $facetValue = $this->translate('Document_ServerState_Value_' . ucfirst($facetValue));
     *
     * @param string $value
     * @return string
     */
    public function getLabel($value)
    {
        if ($this->isTranslated()) {
            $prefix = $this->getTranslationPrefix();
            if ($prefix !== null) {
                $value = $prefix . ucfirst($value); // TODO ucfirst should not be needed (migth not always apply)
            }
            return $this->getTranslator()->translate($value);
        } else {
            return $value;
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isTranslated()
    {
        return $this->translated;
    }

    /**
     * @param string|bool|int $translated
     */
    public function setTranslated($translated)
    {
        $this->translated = filter_var($translated, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return Application_Translate
     */
    public function getTranslator()
    {
        return Application_Translate::getInstance();
    }

    /**
     * @param string $prefix
     */
    public function setTranslationPrefix($prefix)
    {
        $this->translationPrefix = $prefix;
    }

    /**
     * @return string|null
     */
    public function getTranslationPrefix()
    {
        return $this->translationPrefix;
    }

    /**
     * @param string $resource
     */
    public function setAccessResource($resource)
    {
        $this->accessResource = $resource;
    }

    /**
     * @return string|null
     */
    public function getAccessResource()
    {
        return $this->accessResource;
    }

    /**
     * @return bool
     * @throws Application_Exception
     *
     * TODO Refactor so there is a class for facets that answers this question
     */
    public function isAllowed()
    {
        $resource = $this->getAccessResource();

        if ($resource !== null) {
            $accessControl = new Application_Controller_Action_Helper_AccessControl();
            return $accessControl->accessAllowed('documents');
        }

        return true;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * @return string
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @param string $sort
     */
    public function setSort($sort)
    {
        $this->sort = $sort;
    }

    /**
     * @return string
     */
    public function getIndexField()
    {
        if ($this->indexField !== null) {
            return $this->indexField;
        } else {
            return $this->getName();
        }
    }

    /**
     * @param string|null $indexField
     */
    public function setIndexField($indexField)
    {
        $this->indexField = $indexField;
    }
}
