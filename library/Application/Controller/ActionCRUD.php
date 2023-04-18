<?php

/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the North Rhine-Westphalian Library Service Center,
 * the Cooperative Library Network Berlin-Brandenburg, the Saarland University
 * and State Library, the Saxon State Library - Dresden State and University
 * Library, the Bielefeld University Library and the University Library of
 * Hamburg University of Technology with funding from the German Research
 * Foundation and the European Regional Development Fund.
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
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Model\ModelException;
use Opus\Common\Model\ModelInterface;
use Opus\Common\Model\NotFoundException;
use Opus\Common\Model\PersistableInterface;

/**
 * CRUD Controller for Opus Application.
 *
 * Dieser Controller implementiert für Opus Modelle die grundlegenden Funktionen C(reate) R(ead) U(pdate) D(elete). Um
 * ihn zu nutzen muss er mit einer konkreten Klasse erweitert werden, in der die Member-Variable $formClass gesetzt
 * wird, zum Beispiel auf 'Admin_Form_Licence' wie im LicenceController im Admin Modul.
 *
 * Actions:
 * index        GET Zeige alle Modelle
 * show         GET Zeige Model
 * new          GET/POST Zeige neues Formular/Speichere neues Model
 * edit         GET/POST Zeige Model im Formular/Speichere Model
 * delete       GET/POST Zeige Bestätigungsformular/Lösche Model
 *
 * Mögliche Ergebnisse:
 * - Redirect Aufgrund invalider ID
 * - Redirect zu Index (nach success)
 * - Redirect mit Exception beim Delete
 * - Redirect mit Exception beim Speichern)
 * - Formular anzeigen
 */
class Application_Controller_ActionCRUD extends Application_Controller_Action
{
    /**
     * Message-Key für erfolgreiches Abspeichern.
     */
    public const SAVE_SUCCESS = 'saveSuccess';

    /**
     * Message-Key für fehlgeschlagenes Abspeichern.
     */
    public const SAVE_FAILURE = 'saveFailure';

    /**
     * Message-Key für erfolgreiches Löschen.
     */
    public const DELETE_SUCCESS = 'deleteSuccess';

    /**
     * Message-Key für fehlgeschlagenes Löschen.
     */
    public const DELETE_FAILURE = 'deleteFailure';

    /**
     * Message-Key für invalide oder fehlende Model-ID.
     */
    public const INVALID_ID = 'invalidId';

    /**
     * Message-Key für Versuche ein geschütztes Model zu editieren.
     */
    public const MODEL_NOT_MODIFIABLE = 'modelNotModifiable';

    /**
     * Message-Key für Versuche ein Model zu löschen, dass nicht gelöscht werden darf.
     */
    public const MODEL_CANNOT_DELETE = 'modelCannotDelete';

    /**
     * Nachrichten für die verschiedenen Ereignisse.
     *
     * @var Application_Controller_MessageTemplates
     */
    private $messageTemplates;

    /**
     * Default Messages für die verschiedenen Ereignisse.
     *
     * @var array
     */
    private $defaultMessageTemplates = [
        self::SAVE_SUCCESS         => 'controller_crud_save_success',
        self::SAVE_FAILURE         => ['failure' => 'controller_crud_save_failure'],
        self::DELETE_SUCCESS       => 'controller_crud_delete_success',
        self::DELETE_FAILURE       => ['failure' => 'controller_crud_delete_failure'],
        self::INVALID_ID           => ['failure' => 'controller_crud_invalid_id'],
        self::MODEL_NOT_MODIFIABLE => ['failure' => 'controller_crud_model_not_modifiable'],
        self::MODEL_CANNOT_DELETE  => ['failure' => 'controller_crud_model_cannot_delete'],
    ];

    /**
     * Name von Parameter für Model-ID.
     */
    public const PARAM_MODEL_ID = 'id';

    /**
     * Klasse für Model-Formular.
     *
     * @var Application_Form_ModelFormInterface
     */
    private $formClass;

    /**
     * Klasse für OPUS Model.
     *
     * @var ModelInterface
     */
    private $modelClass;

    /**
     * Name of function for retrieving all models.
     *
     * @var string
     */
    private $functionNameForGettingModels = 'getAll';

    /**
     * Most model IDs are numeric, but for exceptions this can be set to false.
     *
     * @var bool
     */
    private $verifyModelIdIsNumeric = true;

    /**
     * Enables link for model entries to show action.
     *
     * @var bool
     */
    private $showActionEnabled = true;

    /**
     * Initialisiert den Controller.
     */
    public function init()
    {
        parent::init();
        $this->loadDefaultMessages();
    }

    /**
     * List all available model instances.
     */
    public function indexAction()
    {
        $this->renderForm($this->getIndexForm());
    }

    /**
     * Erzeugt das Formular für die Darstellung der Modeltabelle auf der Indexseite.
     *
     * @return Application_Form_Model_Table
     * TODO Konfigurierbare Tabelle mit Links für Editing/Deleting
     */
    public function getIndexForm()
    {
        $form = new Application_Form_Model_Table();
        $form->setModels($this->getAllModels());
        $form->setColumns([['label' => $this->mapToOldModelClass($this->getModelClass())]]);
        $form->setController($this);
        return $form;
    }

    /**
     * Maps new model class names to old names for translation.
     *
     * @param string $modelClass
     * @return string
     *
     * TODO TRANSLATION The translation keys need to be class independent.
     */
    public function mapToOldModelClass($modelClass)
    {
        $pos = strrpos($modelClass, '\\');
        return $pos ? substr($modelClass, $pos + 1) : $modelClass;
    }

    /**
     * Zeigt das Model an.
     *
     * Für die Anzeige wird das Model-Formular im "View"-Modus verwendet.
     */
    public function showAction()
    {
        if ($this->getShowActionEnabled()) {
            $model = $this->getModel($this->getRequest()->getParam(self::PARAM_MODEL_ID));

            if ($model !== null) {
                $form = $this->getEditModelForm($model);
                $form->prepareRenderingAsView();
                $result = $form;
            } else {
                $result = $this->createInvalidIdResult();
            }
        } else {
            $result = [];
        }

        $this->renderResult($result);
    }

    /**
     * Zeigt Formular für neues Model und erzeugt neues Model.
     */
    public function newAction()
    {
        if ($this->getRequest()->isPost()) {
            // Formular POST verarbeiten
            $result = $this->handleModelPost();
        } else {
            // Neues Formular anlegen
            $form = $this->getNewModelForm();
            $form->setAction($this->view->url(['action' => 'new']));
            $result = $form;
        }

        $this->renderResult($result);
    }

    /**
     * Edits a model instance
     */
    public function editAction()
    {
        if ($this->getRequest()->isPost()) {
            // Formular POST verarbeiten
            $result = $this->handleModelPost();
        } else {
            // Neues Formular anzeigen
            $model = $this->getModel($this->getRequest()->getParam(self::PARAM_MODEL_ID));

            if ($model !== null) {
                if ($this->isModifiable($model)) {
                    $form = $this->getEditModelForm($model);
                    $form->setAction($this->view->url(['action' => 'edit']));
                    $result = $form;
                } else {
                    $result = $this->createNotModifiableResult();
                }
            } else {
                $result = $this->createInvalidIdResult();
            }
        }

        $this->renderResult($result);
    }

    /**
     * Löscht eine Model-Instanz nachdem, die Löschung in einem Formular bestätigt wurde.
     */
    public function deleteAction()
    {
        if ($this->getRequest()->isPost() === true) {
            // Bestätigungsformular POST verarbeiten
            $result = $this->handleConfirmationPost();
        } else {
            // Bestätigungsformular anzeigen
            $model = $this->getModel($this->getRequest()->getParam(self::PARAM_MODEL_ID));
            if ($model !== null) {
                if ($this->isDeletable($model)) {
                    $form   = $this->getConfirmationForm($model);
                    $result = $form;
                } else {
                    $result = $this->createCannotBeDeletedResult();
                }
            } else {
                // Request mit invaliden IDs werden ignoriert und zur Index Seite umgeleitet
                $result = $this->createInvalidIdResult();
            }
        }

        $this->renderResult($result);
    }

    /**
     * Speicher neues/editiertes Model.
     *
     * Ein POST kann nur Save oder Cancel bedeuten.
     *
     * @param array|null $post
     * @return Application_Form_ModelFormInterface|array
     */
    public function handleModelPost($post = null)
    {
        if ($post === null) {
            $post = $this->getRequest()->getPost();
        }

        $form = $this->getModelForm();
        $form->populate($post);

        $result = $form->processPost($post, $post);

        switch ($result) {
            case Application_Form_Model_Abstract::RESULT_SAVE:
                if ($form->isValid($post)) {
                    // Validierung erfolgreich; Hole Model vom Formular
                    try {
                        $model = $form->getModel();
                    } catch (Application_Exception $ae) {
                        $this->getLogger()->err(__METHOD__ . $ae->getMessage());
                        $model = null;
                    }

                    if ($model !== null) {
                        if (! $this->isModifiable($model)) {
                            return ['message' => self::MODEL_NOT_MODIFIABLE];
                        }

                        try {
                            $model->store();
                        } catch (ModelException $ome) {
                            // Speichern fehlgeschlagen
                            return ['message' => self::SAVE_FAILURE];
                        }

                        // Redirect zur Show Action
                        if ($this->getShowActionEnabled()) {
                            return [
                                'action'  => 'show',
                                'message' => self::SAVE_SUCCESS,
                                'params'  => [self::PARAM_MODEL_ID => $model->getId()],
                            ];
                        } else {
                            // return to index page
                            return ['message' => self::SAVE_SUCCESS];
                        }
                    } else {
                        // Formular hat kein Model geliefert - Fehler beim speichern
                        return $this->createInvalidIdResult();
                    }
                } else {
                    // Validierung fehlgeschlagen; zeige Formular wieder an
                    $form->populate($post); // Validierung entfernt invalide Werte
                }
                break;
            case Application_Form_Model_Abstract::RESULT_CANCEL:
            default:
                return [];
        }

        return $form;
    }

    /**
     * Verarbeitet POST-Request vom Bestätigungsformular.
     *
     * @param array|null $post
     * @return array
     * @throws Application_Exception
     */
    public function handleConfirmationPost($post = null)
    {
        if ($post === null) {
            $post = $this->getRequest()->getPost();
        }

        $form = $this->getConfirmationForm();

        if ($form->isConfirmed($post)) {
            // Löschen bestätigt (Ja)
            $modelId = $form->getModelId();
            $model   = $this->getModel($modelId);

            if ($model !== null) {
                if (! $this->isDeletable($model)) {
                    return ['message' => self::MODEL_CANNOT_DELETE];
                }

                // Model löschen
                try {
                    $this->deleteModel($model);
                } catch (ModelException $ome) {
                    $this->getLogger()->err(__METHOD__ . ' ' . $ome->getMessage());
                    return ['message' => self::DELETE_FAILURE];
                }

                return ['message' => self::DELETE_SUCCESS];
            }
        } else {
            // Löschen abgebrochen (Nein) - bzw. Formular nicht valide
            if (! $form->hasErrors()) {
                // keine Validierungsfehler
                return [];
            }
        }

        // ID war invalid oder hat im POST gefehlt (ID in Formular required)
        return $this->createInvalidIdResult();
    }

    /**
     * Setzt das Ergebnis der Verarbeitung um.
     *
     * Es wird entweder ein Formular ausgeben oder ein Redirect veranlasst.
     *
     * @param array $result
     */
    protected function renderResult($result)
    {
        if (is_array($result)) {
            $action = array_key_exists('action', $result) ? $result['action'] : 'index';
            $params = array_key_exists('params', $result) ? $result['params'] : [];

            $messageKey = array_key_exists('message', $result) ? $result['message'] : null;
            $message    = $messageKey !== null ? $this->getMessage($messageKey) : null;

            $this->_helper->Redirector->redirectTo($action, $message, null, null, $params);
        } else {
            // Ergebnis ist Formular
            if ($result !== null && $result instanceof Zend_Form) {
                $this->renderForm($result);
            }
        }
    }

    /**
     * Löscht ein Model.
     *
     * Die Funktion kann überschrieben werden, falls spezielle Schritte beim Löschen notwendig sind.
     *
     * @param ModelInterface $model
     */
    protected function deleteModel($model)
    {
        $model->delete();
    }

    /**
     * Fuehrt Redirect fuer eine ungueltige Model-ID aus.
     *
     * @return array
     */
    public function createInvalidIdResult()
    {
        return ['message' => self::INVALID_ID];
    }

    /**
     * Fuehrt Redirect fuer ein nicht editierbares Model aus.
     *
     * @return array
     */
    public function createNotModifiableResult()
    {
        return ['message' => self::MODEL_NOT_MODIFIABLE];
    }

    /**
     * Fuehrt Redirect fuer ein nicht loeschbares Model aus.
     *
     * @return array
     */
    public function createCannotBeDeletedResult()
    {
        return ['message' => self::MODEL_CANNOT_DELETE];
    }

    /**
     * Erzeugt ein Bestätigunsformular für ein Model.
     *
     * Das Bestätigunsformular ohne Model wird für die Validierung verwendet.
     *
     * @param null|PersistableInterface $model
     * @return Application_Form_Confirmation
     */
    public function getConfirmationForm($model = null)
    {
        $form = new Application_Form_Confirmation($this->getModelClass());

        if (! $this->getVerifyModelIdIsNumeric()) {
            $form->getElement(Application_Form_Confirmation::ELEMENT_MODEL_ID)->removeValidator('int');
        }

        if ($model !== null) {
            $form->setModel($model);
        }

        return $form;
    }

    /**
     * Liefert alle Instanzen der Model-Klasse.
     *
     * @return ModelInterface[]
     */
    public function getAllModels()
    {
        return call_user_func([$this->getModelClass(), $this->functionNameForGettingModels]);
    }

    /**
     * Erzeugt neue Instanz von Model-Klasse.
     *
     * @return ModelInterface
     */
    public function getNewModel()
    {
        $modelClass = $this->getModelClass();
        return $modelClass::new();
    }

    /**
     * Liefert Instanz des Models.
     *
     * @param int|string $modelId
     * @return ModelInterface|null
     *
     * TODO cleanup parameter handling
     */
    public function getModel($modelId)
    {
        if ($modelId === null || is_numeric($modelId) || ! $this->getVerifyModelIdIsNumeric()) {
            $modelClass = $this->getModelClass();

            // TODO LAMINAS is the following check necessary?
            if (is_int($modelId) || ($modelId !== null && strlen(trim($modelId)) !== 0)) {
                try {
                    return $modelClass::get($modelId);
                } catch (NotFoundException $omnfe) {
                    $this->getLogger()->err(__METHOD__ . ':' . $omnfe->getMessage());
                }
            }
        }

        return null; // keine gültige ID
    }

    /**
     * Erzeugt Formular.
     *
     * @return Application_Form_ModelFormInterface
     */
    public function getModelForm()
    {
        $form = new $this->formClass();
        if (! $this->getVerifyModelIdIsNumeric()) {
            $form->getElement(Application_Form_Model_Abstract::ELEMENT_MODEL_ID)->removeValidator('int');
        }
        return $form;
    }

    /**
     * Erzeugt Formular zum Editieren von Model.
     *
     * @param ModelInterface $model
     * @return Application_Form_ModelFormInterface
     */
    public function getEditModelForm($model)
    {
        $form = $this->getModelForm();
        if (! $this->getVerifyModelIdIsNumeric()) {
            $form->getElement(Application_Form_Model_Abstract::ELEMENT_MODEL_ID)->removeValidator('int');
        }
        $form->populateFromModel($model);
        return $form;
    }

    /**
     * Erzeugt Formular zum Hinzufügen eines neuen Models.
     *
     * @return Application_Form_ModelFormInterface
     */
    public function getNewModelForm()
    {
        $model = $this->getNewModel();
        $form  = $this->getModelForm();
        $form->populateFromModel($model); // um evtl. Defaultwerte des Models zu setzen
        return $form;
    }

    /**
     * Liefert Formularklasse für Controller.
     *
     * @return Application_Form_ModelFormInterface|null
     */
    public function getFormClass()
    {
        return $this->formClass;
    }

    /**
     * Setzt die Model-Klasse die verwaltet wird.
     *
     * @param string $formClass Name von Opus Model Klasse
     */
    public function setFormClass($formClass)
    {
        if (! $this->isClassSupported($formClass)) {
            throw new Application_Exception("Class '$formClass' is not instance of Application_Form_IModel.");
        }

        $this->formClass = $formClass;
    }

    /**
     * Liefert die Model-Klasse die verwaltet wird.
     *
     * @return null|string
     */
    public function getModelClass()
    {
        if ($this->modelClass === null) {
            $this->modelClass = $this->getModelForm()->getModelClass();
        }

        return $this->modelClass;
    }

    /**
     * Prüft ob eine Formularklasse vom Controller unterstützt wird.
     *
     * @param string $formClass Name der Formularklasse
     * @return bool TRUE - wenn die Klasse unterstützt wird; FALSE - wenn nicht
     */
    public function isClassSupported($formClass)
    {
        $form = new $formClass();
        return $form instanceof Application_Form_ModelFormInterface ? true : false;
    }

    /**
     * Liefert die konfigurierten Nachrichten.
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->messageTemplates->getMessages();
    }

    /**
     * Setzt die Nachrichten.
     *
     * @param array $messages
     */
    public function setMessages($messages)
    {
        $this->messageTemplates->setMessages($messages);
    }

    /**
     * Liefert die Nachricht für den Schlüssel.
     *
     * @param string $key Nachrichtenschlüssel
     * @return null|string
     */
    public function getMessage($key)
    {
        return $this->messageTemplates->getMessage($key);
    }

    /**
     * Setzt die Nachricht für einen Schlüssel.
     *
     * @param string $key Nachrichtenschlüssel
     * @param string $message Nachricht
     */
    public function setMessage($key, $message)
    {
        $this->messageTemplates->setMessage($key, $message);
    }

    /**
     * Lädt die Standardnachrichten.
     */
    public function loadDefaultMessages()
    {
        $this->messageTemplates = new Application_Controller_MessageTemplates($this->defaultMessageTemplates);
    }

    /**
     * Setzt Namen der Funktion, um alle Modelle zu holen.
     *
     * @param string $name
     */
    public function setFunctionNameForGettingModels($name)
    {
        $this->functionNameForGettingModels = $name ?? 'getAll';
    }

    /**
     * Liefert Namen der Funktion, die für das holen aller Modelle verwendet wird.
     *
     * @return string
     */
    public function getFunctionNameForGettingModels()
    {
        return $this->functionNameForGettingModels;
    }

    /**
     * @param bool $enabled True enabled verification that model ID is numeric value
     */
    public function setVerifyModelIdIsNumeric($enabled)
    {
        $this->verifyModelIdIsNumeric = $enabled;
    }

    /**
     * Returns setting for verification of numeric model IDs.
     *
     * @return bool
     */
    public function getVerifyModelIdIsNumeric()
    {
        return $this->verifyModelIdIsNumeric;
    }

    /**
     * Enables or disables show action.
     *
     * @param bool $enabled true to enable show action
     */
    public function setShowActionEnabled($enabled)
    {
        $this->showActionEnabled = $enabled;
    }

    /**
     * Returns status of show action.
     *
     * @return bool true - enabled; false - disabled
     */
    public function getShowActionEnabled()
    {
        return $this->showActionEnabled;
    }

    /**
     * Determines if a model can be edited.
     *
     * @param ModelInterface $model
     * @return true true if object can be edited; false - object cannot be edited
     */
    public function isModifiable($model)
    {
        return true;
    }

    /**
     * Determines if a model can be deleted.
     *
     * @param ModelInterface $model
     * @return bool true if object can be deleted; false if not
     */
    public function isDeletable($model)
    {
        return $this->isModifiable($model);
    }
}
