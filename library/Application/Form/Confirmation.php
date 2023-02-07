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
use Opus\Common\Model\PersistableInterface;

/**
 * Formular für Bestätigungsabfragen an den Nutzer, z.B. beim Löschen von Modellen.
 *
 * TODO this "form" is actually a Zend_Form_SubForm, it should be a Zend_Form - Fix!
 */
class Application_Form_Confirmation extends Application_Form_Abstract
{
    /**
     * Name von Formularelement für Model-ID.
     */
    public const ELEMENT_MODEL_ID = 'Id';

    /**
     * Name für Button zum Bestätigen der Abfrage.
     */
    public const ELEMENT_YES = 'ConfirmYes';

    /**
     * Name für Button zum Verneinen der Abfrage.
     */
    public const ELEMENT_NO = 'ConfirmNo';

    /**
     * Ergebnis für Bestätigung der Frage (Ja).
     */
    public const RESULT_YES = 'true';

    /**
     * Ergebnis für Abbrechen des Vorgangs (Nein).
     */
    public const RESULT_NO = 'false';

    /**
     * Frage für Bestätigungsformular.
     *
     * @var string
     */
    private $question;

    /**
     * Model auf das sich die Frage bezieht.
     *
     * @var ModelInterface
     */
    private $model;

    /**
     * Klasse für Model.
     *
     * @var ModelInterface
     */
    private $modelClass;

    /**
     * Angepasster Anzeigename für Model;
     *
     * @var string
     */
    private $modelDisplayName;

    /**
     * Konstruiert Formular.
     *
     * @param string     $modelClass Name der Modelklasse
     * @param array|null $options
     * @throws Application_Exception Wenn $modelClass Parameer fehlt.
     */
    public function __construct($modelClass, $options = null)
    {
        if ($modelClass === null || strlen(trim($modelClass)) === 0) {
            throw new Application_Exception(__CLASS__ . 'Attempt to construct without parameter "modelClass".');
        }
        $this->modelClass = $modelClass;
        parent::__construct($options);
    }

    /**
     * Initialisiert die Formularelement.
     */
    public function init()
    {
        parent::init();

        $this->addElement('hidden', self::ELEMENT_MODEL_ID, ['required' => true, 'validators' => ['int']]);
        $this->addElement('submit', self::ELEMENT_YES, ['label' => 'answer_yes']);
        $this->addElement('submit', self::ELEMENT_NO, ['label' => 'answer_no']);

        $this->setLegend($this->getFormLegend());

        $this->setDecorators(
            [
                ['ViewScript', ['viewScript' => 'confirmation.phtml']],
                ['Fieldset', ['class' => 'headline']],
                'Form',
            ]
        );
    }

    /**
     * Erzeugt Text für Überschrift (Legend) des Formulars.
     *
     * @return string
     */
    public function getFormLegend()
    {
        $legend = $this->getTranslator()->translate('confirmation_title_default');
        return sprintf($legend, $this->getModelClassName());
    }

    /**
     * Prüft POST ob Operation bestätigt wurde.
     *
     * Der POST muss gültig sein (mit Model-ID) und der Button ELEMENT_YES muss geklickt worden sein.
     *
     * @param array $post POST array
     * @return bool
     */
    public function isConfirmed($post)
    {
        return $this->isValid($post) && ($this->processPost($post) === self::RESULT_YES) ? true : false;
    }

    /**
     * Verarbeitet POST und stellt fest welcher Button geklickt wurde.
     *
     * @param array $post POST array
     * @return string|null
     */
    public function processPost($post)
    {
        if (array_key_exists(self::ELEMENT_YES, $post)) {
            return self::RESULT_YES;
        } elseif (array_key_exists(self::ELEMENT_NO, $post)) {
            return self::RESULT_NO;
        }

        return null;
    }

    /**
     * Liefert Model-ID, die im Hidden-Feld gespeichert ist.
     *
     * @return string
     */
    public function getModelId()
    {
        return $this->getElement(self::ELEMENT_MODEL_ID)->getValue();
    }

    /**
     * Setzt das Model auf das sich die Abfrage bezieht.
     *
     * Eigentlich wird nur die ID benötigt, aber in abgeleiteten Klassen könnte das Model verwendet werden, um
     * specifischer Informationen anzuzeigen.
     *
     * @param PersistableInterface $model
     * @return $this
     * @throws Application_Exception
     */
    public function setModel($model)
    {
        if ($model !== null && $model instanceof PersistableInterface) {
            $this->model = $model;
            $this->getElement(self::ELEMENT_MODEL_ID)->setValue($model->getId());
        } else {
            if (is_object($model)) {
                throw new Application_Exception(
                    __METHOD__ . ' Parameter ' . get_class($model)
                    . ' not instance of Opus\Model\AbstractDb.'
                );
            } else {
                throw new Application_Exception(__METHOD__ . ' Parameter must be Opus\Model\AbstractDb.');
            }
        }
        return $this;
    }

    /**
     * Liefert Klasse für Model.
     *
     * @return string|null
     */
    public function getModelClass()
    {
        return $this->modelClass;
    }

    /**
     * Liefert Übersetzung für Modelklasse.
     *
     * @return string
     */
    public function getModelClassName()
    {
        if (strpos($this->modelClass, 'Opus\\Common\\') === 0) {
            $modelType      = $this->modelClass::getModelType();
            $translationKey = 'Opus_' . $modelType;
        } else {
            $translationKey = preg_replace('/\\\\/', '_', $this->modelClass);
        }
        return $this->getTranslator()->translate($translationKey);
    }

    /**
     * Liefert den Anzeigenamen für Modelinstanz.
     *
     * @return string
     */
    public function getModelDisplayName()
    {
        if ($this->modelDisplayName !== null) {
            return $this->modelDisplayName;
        } elseif ($this->model !== null) {
            return $this->model->getDisplayName();
        } else {
            return '';
        }
    }

    /**
     * @param string $displayName
     * @return $this
     */
    public function setModelDisplayName($displayName)
    {
        $this->modelDisplayName = $displayName;
        return $this;
    }

    /**
     * Liefert den Fragetext für das Formular.
     *
     * @return string
     */
    public function getQuestion()
    {
        if ($this->question === null) {
            return 'confirmation_question_default';
        } else {
            return $this->question;
        }
    }

    /**
     * Setzt den Fragetext für das Formular.
     *
     * @param string $question
     * @return $this
     */
    public function setQuestion($question)
    {
        $this->question = $question;
        return $this;
    }

    /**
     * Rendert die Frage für die Ausgabe im Formular.
     *
     * Es können zwei Platzhalter (%1$s, %2$s) verwendet werden, mit folgenden Werten:
     * - 1: Übersetzung des Klassennamens
     * - 2: Anzeigename der Modelinstanz
     *
     * @return string
     */
    public function renderQuestion()
    {
        $question = $this->getTranslator()->translate($this->getQuestion());

        return sprintf(
            $question,
            $this->getModelClassName(),
            '<span class="displayname">' . htmlspecialchars($this->getModelDisplayName()) . '</span>'
        );
    }
}
