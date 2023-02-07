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

use Opus\Common\DocumentInterface;

/**
 * Helper für das Setzen von dynamischen Breadcrumbs.
 */
class Application_Controller_Action_Helper_Breadcrumbs extends Application_Controller_Action_Helper_Abstract
{
    /**
     * TODO centralize
     */
    public const PARAM_DOCUMENT_ID = 'id';

    public const TITLE_MAX_LENGTH = 40;

    public const TITLE_SHORT_SUFFIX = ' ...';

    /** @var mixed */
    private $navigation;

    public function init()
    {
        parent::init();
    }

    /**
     * @param string|null $label
     * @param array|null  $parameters
     * @return $this
     */
    public function direct($label = null, $parameters = null)
    {
        if ($label !== null && is_array($parameters)) {
            $this->setParameters($label, $parameters);
        }
        return $this;
    }

    /**
     * @param DocumentInterface $document
     *
     * TODO shorten title
     * TODO log page misses
     */
    public function setDocumentBreadcrumb($document)
    {
        if ($document !== null) {
            $title = $this->getDocumentTitle($document);
            $page  = $this->getNavigation()->findOneBy('label', 'admin_document_index');
            if ($page !== null) {
                $page->setLabel($title);
                $page->setParam(self::PARAM_DOCUMENT_ID, $document->getId());
            } else {
                $this->getLogger()->err(__METHOD__ . " Page with label 'admin_document_index' not found.");
            }
        } else {
            $this->getLogger()->err(__METHOD__ . " No document provided.");
        }
    }

    /**
     * Setzt das Label eines Breadcrumbs auf den Wert von $value.
     *
     * @param string $label
     * @param string $value
     */
    public function setLabelFor($label, $value)
    {
        $page = $this->getNavigation()->findOneBy('label', $label);
        $page->setLabel($value);
    }

    /**
     * Setzt Parameter fuer einen Breadcrumb.
     *
     * @param string $label
     * @param array  $parameters
     */
    public function setParameters($label, $parameters)
    {
        $page = $this->getNavigation()->findOneBy('label', $label);
        if ($page !== null) {
            foreach ($parameters as $key => $value) {
                $page->setParam($key, $value);
            }
        } else {
            $this->getLogger()->err(__METHOD__ . " Page with label '$label' not found.");
        }
    }

    /**
     * @return Zend_View_Helper_Navigation
     */
    public function getNavigation()
    {
        if ($this->navigation === null) {
            $this->navigation = $this->getActionController()->view->navigation();
        }

        return $this->navigation;
    }

    /**
     * @param mixed $navigation
     */
    public function setNavigation($navigation)
    {
        $this->navigation = $navigation;
    }

    /**
     * @param DocumentInterface $document
     * @return string
     */
    public function getDocumentTitle($document)
    {
        $helper = new Application_Util_DocumentAdapter($this->getView(), $document);
        $title  = $helper->getMainTitle();
        return mb_strlen($title) > self::TITLE_MAX_LENGTH ? mb_substr($title, 0, self::TITLE_MAX_LENGTH)
            . self::TITLE_SHORT_SUFFIX : $title;
    }
}
