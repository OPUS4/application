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
 * @copyright   Copyright (c) 2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Translate\TranslateException;
use Opus\Common\Translate\UnknownTranslationKeyException;

/**
 * Form element for editing translations.
 *
 * The form element provides input fields for all supported languages, normally English and German.
 *
 * This is a special form element. Most forms
 *
 * TODO get value (?)
 * TODO validation
 * TODO should translation functions be added at model level (in framework)?
 */
class Application_Form_Element_Translation extends Zend_Form_Element_Multi
{
    /** @var string */
    public $helper = 'formTranslation';

    /**
     * @var bool
     * @phpcs:disable
     */
    protected $_isArray = false;
    // @phpcs:enable

    /** @var string */
    private $key;

    public function init()
    {
        parent::init();
        $this->addPrefixPath('Application_Form_Decorator', 'Application/Form/Decorator', Zend_Form::DECORATOR);
        $this->setRegisterInArrayValidator(false);
        $this->loadDefaultOptions();
    }

    public function loadDefaultOptions()
    {
        $languages = Application_Configuration::getInstance()->getSupportedLanguages();

        $options = [];

        foreach ($languages as $language) {
            $options[$language] = null;
        }

        $this->setMultiOptions($options);
    }

    public function loadDefaultDecorators()
    {
        if (! $this->loadDefaultDecoratorsIsDisabled() && count($this->getDecorators()) === 0) {
            $this->setDecorators([
                'ViewHelper',
                'ElementHtmlTag',
                [
                    'LabelNotEmpty',
                    [
                        'tag'        => 'div',
                        'tagClass'   => 'label',
                        'placement'  => 'prepend',
                        'disableFor' => true,
                    ],
                ],
                [
                    ['dataWrapper' => 'HtmlTagWithId'],
                    [
                        'tag'   => 'div',
                        'class' => 'data-wrapper',
                    ],
                ],
            ]);
        }
    }

    /**
     * @param string $key
     */
    public function populateFromTranslations($key)
    {
        $manager = new Application_Translate_TranslationManager();

        try {
            $translation = $manager->getTranslation($key);
        } catch (UnknownTranslationKeyException $ex) {
            $translation = null;
        }

        if (isset($translation['translations'])) {
            $this->setValue($translation['translations']);
        }
    }

    /**
     * @param string      $key
     * @param string|null $module
     * @param string|null $oldKey
     * @throws TranslateException
     */
    public function updateTranslations($key, $module = null, $oldKey = null)
    {
        $manager = new Application_Translate_TranslationManager();

        $old = null;

        if ($oldKey !== null && $key !== $oldKey) {
            $manager->delete($oldKey, $module);
        } else {
            try {
                $translation = $manager->getTranslation($key);
                $old         = $translation['translations'];
            } catch (UnknownTranslationKeyException $ex) {
            }
        }

        if ($module === null && isset($translation['module'])) {
            $module = $translation['module'];
        }

        $new = $this->getValue();

        if ($new !== $old) {
            if ($new !== null) {
                $manager->setTranslation($key, $new, $module);
            } else {
                $manager->delete($key, $module);
            }
            $manager->clearCache();
        }
    }

    /**
     * @param string|string[] $value
     * @return $this
     */
    public function setValue($value)
    {
        parent::setValue($value);
        if (is_array($value)) {
            $this->setMultiOptions($value);
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @param string $value
     * @return string
     * @phpcs:disable PSR2.Methods.MethodDeclaration
     */
    protected function _translateValue($value)
    {
        // @phpcs:enable
        return $value;
    }
}
