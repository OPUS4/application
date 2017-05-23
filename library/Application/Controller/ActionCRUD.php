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
 */

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
 *
 * @category    Application
 * @package     Application_Controller
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2009-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Application_Controller_ActionCRUD extends Application_Controller_Action {

    /**
     * Message-Key für erfolgreiches Abspeichern.
     */
    const SAVE_SUCCESS = 'saveSuccess';

    /**
     * Message-Key für fehlgeschlagenes Abspeichern.
     */
    const SAVE_FAILURE = 'saveFailure';

    /**
     * Message-Key für erfolgreiches Löschen.
     */
    const DELETE_SUCCESS = 'deleteSuccess';

    /**
     * Message-Key für fehlgeschlagenes Löschen.
     */
    const DELETE_FAILURE = 'deleteFailure';

    /**
     * Message-Key für invalide oder fehlende Model-ID.
     */
    const INVALID_ID = 'invalidId';

    /**
     * Message-Key für Versuche ein geschütztes Model zu editieren oder löschen.
     */
    const MODEL_NOT_MODIFIABLE = 'modelNotModifiable';

    /**
     * Nachrichten für die verschiedenen Ereignisse.
     * @var array
     */
    private $_messageTemplates;

    /**
     * Default Messages für die verschiedenen Ereignisse.
     * @var array
     */
    private $_defaultMessageTemplates = array(
        self::SAVE_SUCCESS => 'controller_crud_save_success',
        self::SAVE_FAILURE => array('failure' => 'controller_crud_save_failure'),
        self::DELETE_SUCCESS => 'controller_crud_delete_success',
        self::DELETE_FAILURE => array('failure' => 'controller_crud_delete_failure'),
        self::INVALID_ID => array('failure' => 'controller_crud_invalid_id'),
        self::MODEL_NOT_MODIFIABLE => array('failure' => 'controller_crud_model_not_modifiable')
    );

    /**
     * Name von Parameter für Model-ID.
     */
    const PARAM_MODEL_ID = 'id';

    /**
     * Klasse für Model-Formular.
     * @var \Application_Form_IModel
     */
    private $_formClass = null;

    /**
     * Klasse für OPUS Model.
     * @var \Opus_Model_Abstract
     */
    private $_modelClass = null;

    /**
     * Name of function for retrieving all models.
     * @var string
     */
    private $_functionNameForGettingModels = 'getAll';

    /**
     * Most model IDs are numeric, but for exceptions this can be set to false.
     * @var bool
     */
    private $_verifyModelIdIsNumeric = true;

    /**
     * Enables link for model entries to show action.
     * @var bool
     */
    private $_showActionEnabled = true;

    /**
     * Initialisiert den Controller.
     */
    public function init() {
        parent::init();
        $this->loadDefaultMessages();
    }

    /**
     * List all available model instances.
     *
     * @return void
     *
     */
    public function indexAction() {
        $this->renderForm($this->getIndexForm());
    }

    /**
     * Erzeugt das Formular für die Darstellung der Modeltabelle auf der Indexseite.
     * @return Application_Form_Model_Table
     * TODO Konfigurierbare Tabelle mit Links für Editing/Deleting
     */
    public function getIndexForm() {
        $form = new Application_Form_Model_Table();
        $form->setModels($this->getAllModels());
        $form->setColumns(array(array('label' => $this->getModelClass())));
        $form->setController($this);
        return $form;
    }

    /**
     * Zeigt das Model an.
     *
     * Für die Anzeige wird das Model-Formular im "View"-Modus verwendet.
     *
     * @return void
     */
    public function showAction() {
        if ($this->getShowActionEnabled()) {
            $model = $this->getModel($this->getRequest()->getParam(self::PARAM_MODEL_ID));

            if (!is_null($model)) {
                $form = $this->getEditModelForm($model);
                $form->prepareRenderingAsView();
                $result = $form;
            }
            else {
                $result = $this->createInvalidIdResult();
            }
        }
        else {
            $result = array();
        }

        $this->renderResult($result);
    }

    /**
     * Zeigt Formular für neues Model und erzeugt neues Model.
     *
     * @return void
     */
    public function newAction() {
        if ($this->getRequest()->isPost()) {
            // Formular POST verarbeiten
            $result = $this->handleModelPost();
        }
        else {
            // Neues Formular anlegen
            $form = $this->getNewModelForm();
            $form->setAction($this->view->url(array('action' => 'new')));
            $result = $form;
        }

        $this->renderResult($result);
    }

    /**
     * Edits a model instance
     *
     * @return void
     */
    public function editAction() {
        if ($this->getRequest()->isPost()) {
            // Formular POST verarbeiten
            $result = $this->handleModelPost();
        }
        else {
            // Neues Formular anzeigen
            $model = $this->getModel($this->getRequest()->getParam(self::PARAM_MODEL_ID));

            if (!is_null($model)) {
                if ($this->isModifiable($model)) {
                    $form = $this->getEditModelForm($model);
                    $form->setAction($this->view->url(array('action' => 'edit')));
                    $result = $form;
                }
                else {
                    $result = $this->createNotModifiableResult();
                }
            }
            else {
                $result = $this->createInvalidIdResult();
            }
        }

        $this->renderResult($result);
    }

    /**
     * Löscht eine Model-Instanz nachdem, die Löschung in einem Formular bestätigt wurde.
     */
    public function deleteAction() {
        if ($this->getRequest()->isPost() === true) {
            // Bestätigungsformular POST verarbeiten
            $result = $this->handleConfirmationPost();
        }
        else {
            // Bestätigungsformular anzeigen
            $model = $this->getModel($this->getRequest()->getParam(self::PARAM_MODEL_ID));
            if (!is_null($model)) {
                if ($this->isModifiable($model)) {
                    $form = $this->getConfirmationForm($model);
                    $result = $form;
                }
                else {
                    $result = $this->createNotModifiableResult();
                }
            }
            else {
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
     */
    public function handleModelPost($post = null) {
        if (is_null($post)) {
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
                    }
                    catch (Application_Exception $ae) {
                        $this->getLogger()->err(__METHOD__ . $ae->getMessage());
                        $model = null;
                    }

                    if (!is_null($model)) {
                        if (!$this->isModifiable($model)) {
                            return array('message' => self::MODEL_NOT_MODIFIABLE);
                        }

                        try {
                            $model->store();
                        }
                        catch (Opus_Model_Exception $ome) {
                            // Speichern fehlgeschlagen
                            return array('message' => self::SAVE_FAILURE);
                        }

                        // Redirect zur Show Action
                        if ($this->getShowActionEnabled()) {
                            return array(
                                'action' => 'show', 'message' => self::SAVE_SUCCESS,
                                'params' => array(self::PARAM_MODEL_ID => $model->getId())
                            );
                        }
                        else {
                            // return to index page
                            return array(
                                'message' => self::SAVE_SUCCESS
                            );
                        }
                    }
                    else {
                        // Formular hat kein Model geliefert - Fehler beim speichern
                        return $this->createInvalidIdResult();
                    }
                }
                else {
                    // Validierung fehlgeschlagen; zeige Formular wieder an
                    $form->populate($post); // Validierung entfernt invalide Werte
                }
                break;
            case Application_Form_Model_Abstract::RESULT_CANCEL:
            default:
                return array();

        }

        return $form;
    }

    /**
     * Verarbeitet POST vom Bestätigunsformular.
     *
     */
    public function handleConfirmationPost($post = null) {
        if (is_null($post)) {
            $post = $this->getRequest()->getPost();
        }

        $form = $this->getConfirmationForm();

        if ($form->isConfirmed($post)) {
            // Löschen bestätigt (Ja)
            $modelId = $form->getModelId();
            $model = $this->getModel($modelId);

            if (!is_null($model)) {
                if (!$this->isModifiable($model)) {
                    return array('message' => self::MODEL_NOT_MODIFIABLE);
                }

                // Model löschen
                try {
                    $this->deleteModel($model);
                }
                catch (Opus_Model_Exception $ome) {
                    $this->getLogger()->err(__METHOD__ . ' ' . $ome->getMessage());
                    return array('message' => self::DELETE_FAILURE);
                }

                return array('message' => self::DELETE_SUCCESS);
            }
        }
        else {
            // Löschen abgebrochen (Nein) - bzw. Formular nicht valide
            if (!$form->hasErrors()) {
                // keine Validierungsfehler
                return array();
            }
        }

        // ID war invalid oder hat im POST gefehlt (ID in Formular required)
        return $this->createInvalidIdResult();
    }

    /**
     * Setzt das Ergebnis der Verarbeitung um.
     *
     * Es wird entweder ein Formular ausgeben oder ein Redirect veranlasst.
     */
    protected function renderResult($result) {
        if (is_array($result)) {
            $action = array_key_exists('action', $result) ? $result['action'] : 'index';
            $params = array_key_exists('params', $result) ? $result['params'] : array();

            $messageKey = array_key_exists('message', $result) ? $result['message'] : null;
            $message = !is_null($messageKey) ? $this->getMessage($messageKey) : null;

            $this->_helper->Redirector->redirectTo($action, $message, null, null, $params);
        }
        else {
            // Ergebnis ist Formular
            if (!is_null($result) && $result instanceof Zend_Form) {
                $this->renderForm($result);
            }
        }
    }

    /**
     * Löscht ein Model.
     *
     * Die Funktion kann überschrieben werden, falls spezielle Schritte beim Löschen notwendig sind.
     *
     * @param $model \Opus_Model_Abstract
     */
    protected function deleteModel($model) {
        $model->delete();
    }

    /**
     * Fuehrt Redirect fuer eine ungueltige Model-ID aus.
     */
    public function createInvalidIdResult() {
        return array('message' => self::INVALID_ID);
    }

    /**
     * Fuehrt Redirect fuer ein nicht editierbares Model aus.
     * @return array
     */
    public function createNotModifiableResult() {
        return array('message' => self::MODEL_NOT_MODIFIABLE);
    }

    /**
     * Erzeugt ein Bestätigunsformular für ein Model.
     *
     * Das Bestätigunsformular ohne Model wird für die Validierung verwendet.
     *
     * @param Opus_Model_AbstractDb $model
     * @return Application_Form_Confirmation
     */
    public function getConfirmationForm($model = null) {
        $form = new Application_Form_Confirmation($this->getModelClass());

        if (!$this->getVerifyModelIdIsNumeric()) {
            $form->getElement(Application_Form_Confirmation::ELEMENT_MODEL_ID)->removeValidator('int');
        }

        if (!is_null($model)) {
            $form->setModel($model);
        }

        return $form;
    }

    /**
     * Liefert alle Instanzen der Model-Klasse.
     */
    public function getAllModels() {
        return call_user_func(array($this->getModelClass(), $this->_functionNameForGettingModels));
    }

    /**
     * Erzeugt neue Instanz von Model-Klasse.
     * @return mixed
     */
    public function getNewModel() {
        $modelClass = $this->getModelClass();
        return new $modelClass();
    }

    /**
     * Liefert Instanz des Models.
     * @param type $modelId
     * @return \modelClass
     */
    public function getModel($modelId) {
        if (is_null($modelId) || is_numeric($modelId) || !$this->getVerifyModelIdIsNumeric()) {
            $modelClass = $this->getModelClass();

            if (strlen(trim($modelId)) !== 0) {
                try {
                    return new $modelClass($modelId);
                }
                catch (Opus_Model_NotFoundException $omnfe) {
                    $this->getLogger()->err(__METHOD__ . ':' . $omnfe->getMessage());
                }
            }
        }

        return null; // keine gültige ID
    }

    /**
     * Erzeugt Formular.
     * @return Application_Form_IModel
     */
    public function getModelForm() {
        $form = new $this->_formClass();
        if (!$this->getVerifyModelIdIsNumeric()) {
            $form->getElement(Application_Form_Model_Abstract::ELEMENT_MODEL_ID)->removeValidator('int');
        }
        return $form;
    }

    /**
     * Erzeugt Formular zum Editieren von Model.
     * @param $model
     * @return Application_Form_IModel
     */
    public function getEditModelForm($model) {
        $form = $this->getModelForm();
        if (!$this->getVerifyModelIdIsNumeric()) {
            $form->getElement(Application_Form_Model_Abstract::ELEMENT_MODEL_ID)->removeValidator('int');
        }
        $form->populateFromModel($model);
        return $form;
    }

    /**
     * Erzeugt Formular zum Hinzufügen eines neuen Models.
     * @return Application_Form_IModel
     */
    public function getNewModelForm() {
        $model = $this->getNewModel();
        $form = $this->getModelForm();
        $form->populateFromModel($model); // um evtl. Defaultwerte des Models zu setzen
        return $form;
    }

    /**
     * Liefert Formularklasse für Controller.
     * @return Application_Form_IModel|null
     */
    public function getFormClass() {
        return $this->_formClass;
    }

    /**
     * Setzt die Model-Klasse die verwaltet wird.
     * @param $modelClass Name von Opus Model Klasse
     */
    public function setFormClass($formClass) {
        if (!$this->isClassSupported($formClass)) {
            throw new Application_Exception("Class '$formClass' is not instance of Application_Form_IModel.");
        }

        $this->_formClass = $formClass;
    }

    /**
     * Liefert die Model-Klasse die verwaltet wird.
     * @return null|Opus_Model_Abstract
     */
    public function getModelClass() {
        if (is_null($this->_modelClass)) {
            $this->_modelClass = $this->getModelForm()->getModelClass();
        }

        return $this->_modelClass;
    }

    /**
     * Prüft ob eine Formularklasse vom Controller unterstützt wird.
     * @param $formClass Name der Formularklasse
     * @return bool TRUE - wenn die Klasse unterstützt wird; FALSE - wenn nicht
     */
    public function isClassSupported($formClass) {
        $form = new $formClass();
        return ($form instanceof Application_Form_IModel) ? true : false;
    }

    /**
     * Liefert die konfigurierten Nachrichten.
     * @return array
     */
    public function getMessages() {
        return $this->_messageTemplates->getMessages();
    }

    /**
     * Setzt die Nachrichten.
     * @param $messages
     */
    public function setMessages($messages) {
        $this->_messageTemplates->setMessages($messages);
    }

    /**
     * Liefert die Nachricht für den Schlüssel.
     * @param $key Nachrichtenschlüssel
     * @return null|string
     */
    public function getMessage($key) {
        return $this->_messageTemplates->getMessage($key);
    }

    /**
     * Setzt die Nachricht für einen Schlüssel.
     * @param $key Nachrichtenschlüssel
     * @param $message Nachricht
     */
    public function setMessage($key, $message) {
        $this->_messageTemplates->setMessage($key, $message);
    }

    /**
     * Lädt die Standardnachrichten.
     */
    public function loadDefaultMessages() {
        $this->_messageTemplates = new Application_Controller_MessageTemplates($this->_defaultMessageTemplates);
    }

    /**
     * Setzt Namen der Funktion, um alle Modelle zu holen.
     * @param $name
     */
    public function setFunctionNameForGettingModels($name) {
        $this->_functionNameForGettingModels = ($name != null) ? $name : 'getAll';
    }

    /**
     * Liefert Namen der Funktion, die für das holen aller Modelle verwendet wird.
     * @return string
     */
    public function getFunctionNameForGettingModels() {
        return $this->_functionNameForGettingModels;
    }

    /**
     * @param $enabled boolean true enabled verification that model ID is numeric value
     */
    public function setVerifyModelIdIsNumeric($enabled) {
        $this->_verifyModelIdIsNumeric = $enabled;
    }

    /**
     * Returns setting for verification of numeric model IDs.
     * @return bool
     */
    public function getVerifyModelIdIsNumeric() {
        return $this->_verifyModelIdIsNumeric;
    }

    /**
     * Enables or disables show action.
     * @param $enabled bool true to enable show action
     */
    public function setShowActionEnabled($enabled) {
        $this->_showActionEnabled = $enabled;
    }

    /**
     * Returns status of show action.
     * @return bool true - enabled; false - disabled
     */
    public function getShowActionEnabled() {
        return $this->_showActionEnabled;
    }

    /**
     * Determines if a model can be edited.
     *
     * @param $model Object
     * @return bool true if object can be edited; false - object cannot be edited
     */
    public function isModifiable($model) {
        return true;
    }

}
