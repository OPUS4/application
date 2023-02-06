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

use Opus\Common\Collection;
use Opus\Common\CollectionRole;
use Opus\Common\CollectionRoleInterface;
use Opus\Common\Config;
use Opus\Common\DnbInstitute;
use Opus\Common\Licence;
use Opus\Common\Model\NotFoundException;
use Opus\Common\Series;

class Publish_Model_Validation
{
    /** @var string */
    public $datatype;

    /** @var array */
    public $validator = [];

    /** @var array */
    public $institutes = [];

    /** @var array */
    public $projects = [];

    /** @var array */
    public $licences = [];

    /** @var array */
    public $series = [];

    /** @var array */
    public $languages = [];

    /** @var array */
    public $listOptions = [];

    /** @var CollectionRoleInterface */
    public $collectionRole;

    /** @var Zend_View */
    private $view;

    /** @var Zend_Session_Namespace */
    private $session;

    /**
     * @param string                       $datatype
     * @param Zend_Session_Namespace       $session
     * @param CollectionRoleInterface|null $collectionRole
     * @param array|null                   $options
     * @param Zend_View|null               $view
     */
    public function __construct($datatype, $session, $collectionRole = null, $options = null, $view = null)
    {
        if (isset($options) && ! empty($options)) {
            $this->listOptions = $options;
            $this->datatype    = 'List';
        }

        if (isset($collectionRole)) {
            $this->collectionRole = $collectionRole;
        } else {
            $this->datatype = $datatype;
        }

        $this->view    = $view;
        $this->session = $session;
    }

    public function validate()
    {
        $this->datatypeValidation();
    }

    private function datatypeValidation()
    {
        switch ($this->datatype) {
            case 'Date':
                $this->validator = $this->validateDate();
                break;

            case 'Email':
                $this->validator = $this->validateEmail();
                break;

            case 'Integer':
                $this->validator = $this->validateInteger();
                break;

            case 'Language':
                $this->validator = $this->validateLanguage();
                break;

            case 'Licence':
                $this->validator = $this->validateLicence();
                break;

            case 'List':
                $this->validator = $this->validateList();
                break;

            case 'Series':
                $this->validator = $this->validateSeries();
                break;

            case 'ThesisGrantor':
                $this->validator = $this->validateThesis(true);
                break;

            case 'ThesisPublisher':
                $this->validator = $this->validateThesis();
                break;

            case 'Year':
                $this->validator = $this->validateYear();
                break;

            case 'Collection':
            case 'CollectionLeaf':
            case 'Enrichment':
            case 'SeriesNumber': //internal datatype, do not use in documenttypes!
            case 'Subject':
            case 'Reference':
            case 'Person':
            case 'Note':
            case 'Text':
            case 'Title':
                $this->validator = null;
                break;

            default:
                //else no datatype required!
                break;
        }
    }

    /**
     * @return array|null
     */
    private function validateDate()
    {
        if (! isset($this->session->language)) {
            return null;
        }

        $lang       = $this->session->language;
        $validators = [];

        $validator = new Application_Form_Validate_Date();
        $validator->setLocale($lang);

        $messages = [
            Zend_Validate_Date::INVALID      => $this->translate('publish_validation_error_date_invalid'),
            Zend_Validate_Date::INVALID_DATE => $this->translate('publish_validation_error_date_invaliddate'),
            Zend_Validate_Date::FALSEFORMAT  => $this->translate('publish_validation_error_date_falseformat'),
        ];
        $validator->setMessages($messages);

        $validators[] = $validator;
        return $validators;
    }

    /**
     * @return array
     */
    private function validateEmail()
    {
        $validators = [];
        $validator  = new Publish_Model_ValidationEmail();

        $commonErrorMessage = 'publish_validation_error_email_invalid';

        $messages = [
            Zend_Validate_EmailAddress::INVALID            => $this->translate($commonErrorMessage),
            Zend_Validate_EmailAddress::INVALID_FORMAT     => $this->translate($commonErrorMessage),
            Zend_Validate_EmailAddress::INVALID_HOSTNAME   => $this->translate($commonErrorMessage),
            Zend_Validate_EmailAddress::INVALID_LOCAL_PART => $this->translate($commonErrorMessage),
            Zend_Validate_EmailAddress::INVALID_MX_RECORD  => $this->translate($commonErrorMessage),
            Zend_Validate_EmailAddress::INVALID_SEGMENT    => $this->translate($commonErrorMessage),
            Zend_Validate_EmailAddress::LENGTH_EXCEEDED    => $this->translate($commonErrorMessage),
            Zend_Validate_EmailAddress::QUOTED_STRING      => $this->translate($commonErrorMessage),
            Zend_Validate_EmailAddress::DOT_ATOM           => $this->translate($commonErrorMessage),
        ];
        $validator->setMessages($messages);

        $validators[] = $validator;
        return $validators;
    }

    /**
     * @return array
     * @throws Zend_Validate_Exception
     */
    private function validateInteger()
    {
        $validators = [];
        $validator  = new Zend_Validate_Int();
        $validator->setMessage($this->translate('publish_validation_error_int'), Zend_Validate_Int::NOT_INT);

        $validators[] = $validator;
        return $validators;
    }

    /**
     * @return array|null
     */
    private function validateLanguage()
    {
        $languages = array_keys($this->getLanguages());

        if ($languages === null) {
            return null;
        }

        return $this->validateSelect($languages);
    }

    /**
     * @param array $set
     * @return array
     * @throws Zend_Validate_Exception
     */
    private function validateSelect($set)
    {
        $validator = new Zend_Validate_InArray($set);
        $messages  = [
            Zend_Validate_InArray::NOT_IN_ARRAY => $this->translate('publish_validation_error_inarray_notinarray'),
        ];
        $validator->setMessages($messages);

        $validators[] = $validator;
        return $validators;
    }

    /**
     * @return array|null
     * @throws Zend_Validate_Exception
     */
    private function validateLicence()
    {
        $licences = array_keys($this->getLicences());
        if ($licences === null) {
            return null;
        }

        return $this->validateSelect($licences);
    }

    /**
     * @return array|null
     * @throws Zend_Validate_Exception
     */
    private function validateSeries()
    {
        $series = array_keys($this->getSeries());
        if ($series === null) {
            return null;
        }

        return $this->validateSelect($series);
    }

    /**
     * @return array
     * @throws Zend_Validate_Exception
     */
    private function validateList()
    {
        foreach ($this->listOptions as $option) {
            $this->listOptions[$option] = $option;
        }

        return $this->validateSelect($this->listOptions);
    }

    /**
     * @param array|null $grantors
     * @return array|null
     * @throws Zend_Validate_Exception
     */
    private function validateThesis($grantors = null)
    {
        $thesisGrantors = $this->getThesis($grantors);
        if ($thesisGrantors !== null) {
            $thesises = array_keys($thesisGrantors);
            if ($thesises === null) {
                return null;
            }
            return $this->validateSelect($thesises);
        }

        return null;
    }

    /**
     * @return array
     * @throws Zend_Validate_Exception
     */
    private function validateYear()
    {
        $validators = [];

        $greaterThan = new Zend_Validate_GreaterThan('0000');
        $greaterThan->setMessage(
            $this->translate('publish_validation_error_year_greaterthan'),
            Zend_Validate_GreaterThan::NOT_GREATER
        );
        $validators[] = $greaterThan;

        $validInt = new Zend_Validate_Int();
        $messages = [
            Zend_Validate_Int::INVALID => $this->translate('publish_validation_error_year_intinvalid'),
            Zend_Validate_Int::NOT_INT => $this->translate('publish_validation_error_year_notint'),
        ];
        $validInt->setMessages($messages);
        $validators[] = $validInt;

        return $validators;
    }

    /**
     * @param string|null $datatype
     * @return array|null
     */
    public function selectOptions($datatype = null)
    {
        if (isset($datatype)) {
            $switchVar = $datatype;
        } else {
            $switchVar = $this->datatype;
        }

        switch ($switchVar) {
            case 'Collection':
            case 'CollectionLeaf':
                return $this->collectionSelect();

            case 'Language':
                return $this->languageSelect();

            case 'Licence':
                return $this->licenceSelect();

            case 'List':
                return $this->listOptions;

            case 'ThesisGrantor':
                return $this->thesisSelect(true);

            case 'ThesisPublisher':
                return $this->thesisSelect();

            case 'Series':
                return $this->seriesSelect();

            default:
                //else no select options required
                break;
        }

        return null;
    }

    /**
     * @return array|null
     * @throws NotFoundException
     */
    private function collectionSelect()
    {
        $collectionRole = CollectionRole::fetchByName($this->collectionRole);
        if ($collectionRole === null || $collectionRole->getRootCollection() === null) {
            return null;
        }

        if (
            $collectionRole->getVisible() && $collectionRole->getRootCollection()->getVisiblePublish()
            && $this->hasVisiblePublishChildren($collectionRole)
        ) {
            $children     = [];
            $collectionId = $collectionRole->getRootCollection()->getId();
            $collection   = Collection::get($collectionId);

            $colls = $collection->getVisiblePublishChildren();

            foreach ($colls as $coll) {
                $children[$coll->getId()] = $coll->getDisplayNameForBrowsingContext($collectionRole);
            }
            return $children;
        }
        return null;
    }

    /**
     * @return array|null
     */
    private function languageSelect()
    {
        $languages = $this->getLanguages();
        if (isset($languages) || count($languages) >= 1) {
            asort($languages);
            return $languages;
        }
        return null;
    }

    /**
     * TODO REFACTOR: Function still needed since it does not sort anymore?
     *
     * @return array|null
     */
    private function licenceSelect()
    {
        $licences = $this->getLicences();
        if (isset($licences) && count($licences) >= 1) {
            return $licences;
        }
        return null;
    }

    /**
     * @return array|null
     */
    private function seriesSelect()
    {
        $sets = $this->getSeries();
        if (isset($sets) && count($sets) >= 1) {
            return $sets;
        }
        return null;
    }

    /**
     * @param null|array $grantors
     * @return array|null
     */
    private function thesisSelect($grantors = null)
    {
        $thesisList = $this->getThesis($grantors);
        if ($thesisList !== null) {
            asort($thesisList);
        }
        return $thesisList;
    }

    /**
     * return the available languages from registry, database or chache
     *
     * @return string[] languages
     */
    private function getLanguages()
    {
        return Application_Form_Element_Language::getLanguageList();
    }

    /**
     * return the available licences from registry, database or chache
     *
     * @return array languages
     */
    private function getLicences()
    {
        $licences = [];
        if (empty($this->licences)) {
            foreach ($dbLicences = Licence::getAll() as $lic) {
                if ($lic->getActive()) {
                    $name          = $lic->getDisplayName();
                    $id            = $lic->getId();
                    $licences[$id] = $name;
                }
            }
            $this->licences = $licences;
            return $licences;
        } else {
            return $this->licences;
        }
    }

    /**
     * return all visible series from database or cache
     *
     * @return array sets
     */
    private function getSeries()
    {
        $sets = [];
        if (empty($this->series)) {
            foreach ($dbSeries = Series::getAllSortedBySortKey() as $set) {
                if ($set->getVisible()) {
                    $title     = $set->getTitle();
                    $id        = $set->getId();
                    $sets[$id] = $title;
                }
            }

            $config = Config::get();

            if (
                isset($config->browsing->series->sortByTitle) &&
                filter_var($config->browsing->series->sortByTitle, FILTER_VALIDATE_BOOLEAN)
            ) {
                uasort($sets, function ($value1, $value2) {
                    return strnatcmp($value1, $value2);
                });
            }

            $this->series = $sets;
            return $sets;
        } else {
            return $this->series;
        }
    }

    /**
     * Retrieves all available ThesisGrantors or ThesisPublishers in a array.
     * Used for generating a select box.
     *
     * @param bool|null $grantors true -> get thesis grantors; null -> get thesis publishers TODO BUG null
     * @return array|null of Dnb_Institutes Objects
     */
    private function getThesis($grantors = null)
    {
        $thesises = [];
        if ($grantors === true) {
            $thesises = DnbInstitute::getGrantors();
        } elseif ($grantors === null) {
            $thesises = DnbInstitute::getPublishers();
        }
        if (empty($thesises)) {
            return null;
        }

        $thesisList = [];
        foreach ($thesises as $thesis) {
            $thesisList[$thesis->getId()] = $thesis->getDisplayName();
        }
        return $thesisList;
    }

    /**
     * @param string $key
     * @return string
     */
    private function translate($key)
    {
        if ($this->view === null) {
            return $key;
        }
        return $this->view->translate($key);
    }

    /**
     * code taken from Solrsearch_Model_CollectionRoles()
     *
     * @param CollectionRoleInterface $collectionRole
     * @return bool
     */
    private function hasVisiblePublishChildren($collectionRole)
    {
        $rootCollection = $collectionRole->getRootCollection();
        if ($rootCollection === null) {
            return false;
        }
        return $rootCollection->hasVisiblePublishChildren();
    }
}
