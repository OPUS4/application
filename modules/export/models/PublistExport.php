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
 * @package     Module_Export
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2021, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Collection;
use Opus\CollectionRole;
use Opus\Common\Config;

/**
 * Export plugin for exporting collections based on role name and collection number.
 *
 * This plugin is used to export publication lists for authors managed as collections
 * in a collection role. An XSLT is applied to format the output.
 *
 * TODO stylesheet stuff moves to XsltExport
 * TODO output is not xml, but rather text/html (DIV, HTML snippet)
 */
class Export_Model_PublistExport extends Export_Model_XsltExport
{

    /**
     * File mime types that are allowed for publication list.
     * @var
     */
    private $_allowedMimeTypes;

    /**
     * @var Array containing instances of plugin
     */
    private static $_instances;

    public function __construct($name)
    {
        $this->setName($name);
        self::registerInstance($this);
    }

    /**
     * @throws Application_Exception if parameters are not sufficient
     */
    public function execute()
    {
        $config = $this->getConfig();
        $request = $this->getRequest();
        $view = $this->getView();
        $logger = $this->getLogger();

        // TODO Xslt stuff
        if (isset($config->stylesheetDirectory)) {
            $stylesheetDirectory = $config->stylesheetDirectory;
        } else {
            $logger->debug(\Zend_Debug::dump($config->toArray(), 'no stylesheet directory specified'));
        }

        if (isset($config->stylesheet)) {
            $stylesheet = $config->stylesheet;
        }

        if (! is_null($request->getParam('stylesheet'))) {
            $stylesheet = $request->getParam('stylesheet');
        }

        // TODO params
        $roleParam = $request->getParam('role');
        if (is_null($roleParam)) {
            throw new Application_Exception('role is not specified');
        }

        $numberParam = $request->getParam('number');
        if (is_null($numberParam)) {
            throw new Application_Exception('number is not specified');
        }

        // TODO config
        $groupBy = 'publishedYear';
        // FIXME OPUSVIER-4130 config does not make sense - completely ignores value of setting
        if (isset($config->groupby->completedyear)) {
            $groupBy = 'completedYear';
        }

        $collection = $this->mapQuery($roleParam, $numberParam);
        $request->setParam('searchtype', 'collection');
        $request->setParam('id', $collection->getId());
        $request->setParam('export', 'xml');
        $this->_proc->setParameter('', 'collName', $collection->getName());

        $this->_proc->registerPHPFunctions('max');
        $this->_proc->registerPHPFunctions('urlencode');
        $this->_proc->registerPHPFunctions('Export_Model_PublistExport::getMimeTypeDisplayName');

        // TODO find way to allow instance to add new helpers without modifying code here
        Application_Xslt::registerViewHelper($this->_proc, [
            'embargoHasPassed'
        ]);
        $this->_proc->setParameter('', 'fullUrl', $view->fullUrl());
        $this->_proc->setParameter('', 'groupBy', $groupBy);
        $this->_proc->setParameter('', 'pluginName', $this->getName());

        $urnResolverUrl = Config::get()->urn->resolverUrl;
        $this->_proc->setParameter('', 'urnResolverUrl', $urnResolverUrl);

        $this->loadStyleSheet($this->buildStylesheetPath($stylesheet, $view->getScriptPath('') . $stylesheetDirectory));

        $this->prepareXml();
    }

    public function setMimeTypes($mimeTypes)
    {
        $this->_allowedMimeTypes = $mimeTypes;
    }

    /**
     * Initialize the mime types from configuration
     */
    public function getMimeTypes()
    {
        if (is_null($this->_allowedMimeTypes)) {
            $config = $this->getConfig();
            $this->_allowedMimeTypes =
                isset($config->file->allow->mimetype) ? $config->file->allow->mimetype->toArray() : [];
        }

        return $this->_allowedMimeTypes;
    }

    /**
     * Registers instances of plugin by name.
     * @param $instance
     */
    private static function registerInstance($instance)
    {
        self::$_instances[$instance->getName()] = $instance;
    }

    /**
     * Gets instances of plugin by name.
     * @param $pluginName
     * @return null
     */
    public static function getInstance($pluginName)
    {
        return isset(self::$_instances[$pluginName]) ? self::$_instances[$pluginName] : null;
    }

    /**
     * Return the display name as configured for a specific mime type
     * @param string $mimeType Mime type to get display name for.
     * If mime type is not configured, an empty string is returned.
     *
     * @result string display name for mime type
     */
    public static function getMimeTypeDisplayName($pluginName, $mimeType)
    {
        $instance = self::getInstance($pluginName);
        $mimeTypes = $instance->getMimeTypes();
        return isset($mimeTypes[$mimeType]) ? $mimeTypes[$mimeType] : '';
    }

    /**
     * Maps query for publist action.
     */
    public function mapQuery($roleParam, $numberParam)
    {
        if (is_null(CollectionRole::fetchByName($roleParam))) {
            throw new Application_Exception('specified role does not exist');
        }

        $role = CollectionRole::fetchByName($roleParam);
        if ($role->getVisible() != '1') {
            throw new Application_Exception('specified role is invisible');
        }

        if (count(Collection::fetchCollectionsByRoleNumber($role->getId(), $numberParam)) == 0) {
            throw new Application_Exception('specified number does not exist for specified role');
        }

        $collection = null;
        foreach (Collection::fetchCollectionsByRoleNumber($role->getId(), $numberParam) as $coll) {
            if ($coll->getVisible() == '1' && is_null($collection)) {
                $collection = $coll;
            }
        }

        if (is_null($collection)) {
            throw new Application_Exception('specified collection is invisible');
        }

        return $collection;
    }
}
