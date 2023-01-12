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
 * @copyright Copyright (c) 2008, OPUS 4 development team
 * @license   http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Model\NotFoundException;
use Opus\Model\AbstractDb;

/**
 * Abstrakte Basisklasse für Model-Formulare.
 *
 * Die Model-Formulare können zusammen mit Application_Controller_Action_CRUD fuer die Verwaltung von Modellen eines
 * Typs eingesetzt werden.
 */
abstract class Application_Form_Model_Abstract extends Application_Form_AbstractViewable implements Application_Form_ModelFormInterface
{
    /**
     * Name von Formularelement fuer Model-ID.
     */
    public const ELEMENT_MODEL_ID = 'Id';

    /**
     * Name von Button zum Speichern.
     */
    public const ELEMENT_SAVE = 'Save';

    /**
     * Name von Button zum Abbrechen.
     */
    public const ELEMENT_CANCEL = 'Cancel';

    /**
     * Ergebnis von POST zum Speichern.
     */
    public const RESULT_SAVE = 'save';

    /**
     * Ergebnis von POST zum Abbrechen.
     */
    public const RESULT_CANCEL = 'cancel';

    /**
     * Name der Modelklasse fuer Formular.
     *
     * @var string
     */
    private $modelClass;

    /**
     * Most model IDs are numeric. If not set to false;
     *
     * @var bool
     */
    private $verifyModelIdIsNumeric = true;

    /**
     * Initialisiert die Formularelement und Dekoratoren.
     */
    public function init()
    {
        parent::init();

        $this->setDecorators(
            [
                'FormElements',
                'Form',
            ]
        );

        $this->addElement(
            'hidden',
            self::ELEMENT_MODEL_ID,
            [
                'decorators' => [
                    'ViewHelper',
                    [['liWrapper' => 'HtmlTag'], ['tag' => 'li']],
                ],
            ]
        );

        $this->addElement(
            'submit',
            self::ELEMENT_SAVE,
            [
                'decorators' => [
                    'ViewHelper',
                    [['liWrapper' => 'HtmlTag'], ['tag' => 'li', 'class' => 'save-element']],
                ],
            ]
        );

        $this->addElement(
            'submit',
            self::ELEMENT_CANCEL,
            [
                'decorators' => [
                    'ViewHelper',
                    [['liWrapper' => 'HtmlTag'], ['tag' => 'li', 'class' => 'cancel-element']],
                ],
            ]
        );

        $this->addDisplayGroup(
            [self::ELEMENT_MODEL_ID, self::ELEMENT_SAVE, self::ELEMENT_CANCEL],
            'actions',
            [
                'order'      => 1000,
                'decorators' => [
                    'FormElements',
                    [['ulWrapper' => 'HtmlTag'], ['tag' => 'ul', 'class' => 'form-action']],
                    [['divWrapper' => 'HtmlTag'], ['id' => 'form-action']],
                ],
            ]
        );
    }

    /**
     * Verarbeitet POST.
     *
     * @param array $post POST Daten fuer dieses Formular
     * @param array $context POST Daten fuer gesamten Request
     * @return mixed Ergebnis der POST Verarbeitung
     */
    public function processPost($post, $context)
    {
        if (array_key_exists(self::ELEMENT_SAVE, $post)) {
            return self::RESULT_SAVE;
        } elseif (array_key_exists(self::ELEMENT_CANCEL, $post)) {
            return self::RESULT_CANCEL;
        }
    }

    /**
     * Instanziert und aktualisiert vom Formular angezeigtes Model.
     *
     * @throws Application_Exception
     * @return AbstractDb
     */
    public function getModel()
    {
        $modelClass = $this->getModelClass();

        if ($modelClass === null) {
            throw new Application_Exception(__METHOD__ . ' Model class has not been set.');
        }

        $modelId = $this->getModelId();

        $this->validateModelId($modelId);

        $model = null;

        try {
            $model = $modelClass::get($modelId);
        } catch (NotFoundException $omnfe) {
            $this->getLogger()->err($omnfe->getMessage());
            throw new Application_Exception(__METHOD__ . " Model with ID '$modelId' not found.");
        }

        $this->updateModel($model);

        return $model;
    }

    /**
     * Holt die Model-ID vom Formularelement 'Id';
     *
     * @return mixed
     */
    protected function getModelId()
    {
        return $this->getElementValue(self::ELEMENT_MODEL_ID);
    }

    /**
     * Prueft, ob eine Model-ID im richtigen Format vorliegt.
     *
     * Es wird nicht geprüft, ob fuer die ID ein Model existiert. Es geht darum die Funktion ueberschreiben zu koennen
     * fuer Klassen die nicht numerische Identifier verwenden.
     *
     * TODO support arbitrary validator (Zend) to check model ID
     *
     * @param int|string $modelId
     * @throws Application_Exception
     */
    protected function validateModelId($modelId)
    {
        if ($modelId !== null && ! is_numeric($modelId) && $this->getVerifyModelIdIsNumeric()) {
            throw new Application_Exception(__METHOD__ . ' Model-ID must be numeric.');
        }
    }

    /**
     * Liefert die gesetzte Modelklasse fuer das Formular.
     *
     * @return string
     */
    public function getModelClass()
    {
        return $this->modelClass;
    }

    /**
     * Setzt die Modelklasse fuer das Formular.
     *
     * @param string $modelClass
     */
    public function setModelClass($modelClass)
    {
        $this->modelClass = $modelClass;
    }

    /**
     * Bereitet das Formular fuer die Anzeige als View vor.
     */
    public function prepareRenderingAsView()
    {
        parent::prepareRenderingAsView();
        $this->removeDecorator('Form');
        $this->removeDisplayGroup('actions');

        $modelIdElement = $this->getElement(self::ELEMENT_MODEL_ID);
        if ($modelIdElement !== null) {
            $modelIdElement->removeDecorator('liWrapper');
        }
    }

    /**
     * Set if model ID should be validated as numeric value.
     *
     * @param bool $enabled true to enable, false to disable model ID validation
     */
    public function setVerifyModelIdIsNumeric($enabled)
    {
        $this->verifyModelIdIsNumeric = $enabled;
    }

    /**
     * Return setting for verifying model Ids as numeric values.
     *
     * @return bool true - Model ID must be numeric; false - Model ID not verified
     */
    public function getVerifyModelIdIsNumeric()
    {
        return $this->verifyModelIdIsNumeric;
    }
}
