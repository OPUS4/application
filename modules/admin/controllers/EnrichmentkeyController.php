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
 * @package     Module_Admin
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @author      Sascha Szott <opus-development@saschaszott.de>
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Class Admin_EnrichmentkeyController
 *
 * All enrichment keys are shown, but only enrichment keys that are not protected
 * can be edited or deleted. An enrichment key is protected if it is configured
 * as such in the configuration file or if it is referenced by at least one document.
 *
 * The two configuration parameters are:
 *
 * enrichmentkey.protected.modules   (for special enrichments used by modules)
 * enrichmentkey.protected.migration (for enrichments created during migration from OPUS 3)
 *
 * @category    Application
 * @package     Module_Admin
 *
 * TODO show protected/referenced in list of keys
 */
class Admin_EnrichmentkeyController extends Application_Controller_ActionCRUD
{

    /**
     * Model for handling enrichment keys.
     * @var Admin_Model_EnrichmentKeys
     */
    private $_enrichmentKeys;

    /**
     * Initializes and configures controller.
     * @throws Application_Exception
     */
    public function init()
    {
        $this->_enrichmentKeys = new Admin_Model_EnrichmentKeys();
        $this->setVerifyModelIdIsNumeric(false);
        $this->setShowActionEnabled(false);
        $this->setFormClass('Admin_Form_EnrichmentKey');
        parent::init();
    }

    /**
     * Modifiziert Formular für Indextabelle, so dass angepasstes ViewScript verwendet wird.
     * @return Admin_Form_EnrichmentTable
     */
    public function getIndexForm()
    {
        $form = new Admin_Form_EnrichmentTable();
        $form->setModels($this->getAllModels());
        $form->setColumns([['label' => $this->getModelClass()]]);
        $form->setController($this);
        $form->setViewScript('enrichmentkey/modeltable.phtml');
        return $form;
    }

    /**
     * Checks if an enrichment key can be modified. All enrichment keys except protected ones
     * can be modified even if they are referenced in enrichments of documents.
     *
     * @param $model Opus_EnrichmentKey
     * @return bool true if model can be edited and deleted, false if model is protected
     */
    public function isModifiable($model)
    {
        $protectedKeys = $this->_enrichmentKeys->getProtectedEnrichmentKeys();
        // hier kann $model->getId() statt $model->getName() verwendet werden,
        // weil die Tabelle enrichmentkeys keine Spalte mit dem Namen 'id' besitzt
        return ! in_array($model->getId(), $protectedKeys);
    }

    /**
     * Erzeugt Formular zum Hinzufügen eines neuen Enrichment Keys.
     * Wird das Formular für einen bereits in Benutzung befindlichen Enrichment Key, der noch nicht registriert ist,
     * aufgerufen, so wird das Formularfeld für den Namen bereits gefüllt.
     *
     * @return Application_Form_IModel
     */
    public function getNewModelForm()
    {
        $form = parent::getNewModelForm();
        if ($this->getRequest()->isGet()) {
            $name = $this->getRequest()->getParam(self::PARAM_MODEL_ID);
            if (! is_null($name) && trim($name) !== '') {
                // prüfe, ob bereits ein nicht registrierter Enrichment Key mit den Namen existiert, der in Benutzung ist
                if (is_null(Opus_EnrichmentKey::fetchByName($name)) &&
                    in_array($name, Opus_EnrichmentKey::getAllReferenced())) {
                    $form->setNameElementValue($name);
                }
            }
        }
        return $form;
    }

    /**
     * Behandlung des POST-Request beim Anlegen oder Editieren eines Enrichment Keys.
     *
     * Bei der Erzeugung eines neuen Enrichment Keys ist eine Sonderbehandlung erfolrderlich, wenn ein
     * nicht registrierter Enrichment Key mit geändertem Namen gespeichert werden soll. In diesem Fall
     * müssen alle Dokumente, die den EK referenzieren, den geänderten EK-Namen verwenden.
     */
    public function handleModelPost($post = null)
    {
        if (is_null($post)) {
            $post = $this->getRequest()->getPost();
        }

        // handelt es sich um die newAction und wird der per Request Parameter referenzierte Enrichment Key
        // in Dokumenten verwendet und ist er nicht registriert, dann muss überprüft werden, ob eine
        // Registrierung des Enrichment Keys mit gleichzeitiger Umbenennung vorliegt
        $renamingOfUnregisteredKey = false;
        $oldName = null;
        $newName = null;

        if (! in_array(self::PARAM_MODEL_ID, $post) || $post[self::PARAM_MODEL_ID] === '') {
            // Verarbeitung im Kontext der newAction: keine Model-ID im POST Request enthalten oder leer

            $oldName = $this->getRequest()->getParam(self::PARAM_MODEL_ID);
            if (! is_null($oldName) &&
                is_null(Opus_EnrichmentKey::fetchByName($oldName)) &&
                in_array($oldName, Opus_EnrichmentKey::getAllReferenced())) {
                $newName = $post[Admin_Form_EnrichmentKey::ELEMENT_NAME];
                if (! is_null($newName) && $oldName !== $newName) {
                    // Neuregistrierung mit gleichzeitiger Umbenennung des Enrichment Keys
                    $renamingOfUnregisteredKey = true;
                }
            }
        }

        $result = parent::handleModelPost($post);

        if ($renamingOfUnregisteredKey && is_array($result) && in_array(self::SAVE_SUCCESS, $result)) {
            // Enrichment Key wurde erfolgreich registriert: Umbenennung des EK in den Dokumenten durchführen
            $enrichmentKey = Opus_EnrichmentKey::fetchByName($newName);
            if (! is_null($enrichmentKey)) {
                // es hat eine Umbenennung mit gleichzeitiger Registrierung stattgefunden: nach der erfolgreichen
                // Registrierung des Enrichment Key muss der Name des EK in allen Dokumenten geändert werden
                $enrichmentKey->rename($enrichmentKey->getName(), $oldName);
            }
        }

        return $result;
    }

    public function removefromdocsAction()
    {
        if ($this->getRequest()->isPost() === true) {
            // Bestätigungsformular POST verarbeiten
            $result = $this->handleConfirmationPost();
        } else {
            // Bestätigungsformular anzeigen
            $enrichmentKey = $this->getModel($this->getRequest()->getParam(self::PARAM_MODEL_ID));

            // dem Enrichment Key muss mindestens ein Dokument zugeordnet sein, sonst ist die Operation nicht ausführbar
            if (! is_null($enrichmentKey) && in_array($enrichmentKey->getName(), Opus_EnrichmentKey::getAllReferenced())) {
                if (is_null($enrichmentKey->getId())) {
                    // Sonderbehandlung für nicht registrierte Enrichment Keys, die in Dokuemten verwendet werden
                    $result = $this->initConfirmationForm($enrichmentKey, true);
                } elseif ($this->isDeletable($enrichmentKey)) {
                    $result = $this->initConfirmationForm($enrichmentKey);
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
     * @param Opus_EnrichmentKey $model
     * @param bool $unregistered
     * @return Application_Form_Confirmation
     */
    private function initConfirmationForm($model, $unregistered = false)
    {
        $form = $this->getConfirmationForm($model, $unregistered);
        if ($unregistered) {
            $form->setLegend($this->view->translate('admin_enrichmentkey_remove_unregistered_from_documents_title', $model->getName()));
            $form->setQuestion($this->view->translate('admin_enrichmentkey_remove_unregistered_from_documents_description', $model->getName()));
            $form->getElement(Application_Form_Confirmation::ELEMENT_MODEL_ID)->setValue($model->getName());
        } else {
            $form->setLegend($this->view->translate('admin_enrichmentkey_remove_from_documents_title', $model->getName()));
            $form->setQuestion($this->view->translate('admin_enrichmentkey_remove_from_documents_description', $model->getName()));
        }
        return $form;
    }

    /**
     * @param Opus_EnrichmentKey $model
     */
    public function deleteModel($model)
    {
        $request = $this->getRequest();
        // Kontext ermitteln: Löschen des EKs oder nur Enrichments mit dem EK aus den Dokumenten löschen
        if ($request->getActionName() === 'removeFromDocs') {
            $model->deleteFromDocuments();
        } else {
            parent::deleteModel($model);
        }
    }

    public function getAllModels()
    {
        // determine names of all enrichment keys
        $allKeyNames = Opus_Enrichment::getAllUsedEnrichmentKeyNames();

        $registeredEnrichmentKeys = parent::getAllModels();
        $mapNamesToEnrichmentKeys = [];
        foreach ($registeredEnrichmentKeys as $enrichmentKey) {
            $name = $enrichmentKey->getName();
            $mapNamesToEnrichmentKeys[$name] = $enrichmentKey;
            if (! in_array($name, $allKeyNames)) {
                $allKeyNames[] = $name;
            }
        }

        sort($allKeyNames, SORT_FLAG_CASE);

        $result = [];
        foreach ($allKeyNames as $keyName) {
            if (key_exists($keyName, $mapNamesToEnrichmentKeys)) {
                $result[] = $mapNamesToEnrichmentKeys[$keyName];
            } else {
                $newEnrichmentKey = new Opus_EnrichmentKey();
                $newEnrichmentKey->setName($keyName);
                $result[] = $newEnrichmentKey;
            }
        }
        return $result;
    }

    /**
     * Liefert eine Instanz von Opus_Enrichment mit übergebenen $modelId, sofern diese existiert (andernfalls null).
     * Hier wird der Sonderfall behandelt, dass die Id (d.h. der EnrichmentKey Name) nicht registriert ist.
     * Wird der nicht registrierte EnrichmentKey Name in Dokumenten verwendet, so wird ebenfalls eine Instanz von
     * Opus_Enrichment zurückgegeben, die allerdings nicht in der Datenbank persistiert ist.
     *
     * @param $modelId
     * @return Opus_EnrichmentKey|null
     */
    public function getModel($modelId)
    {
        if (is_null($modelId) || is_numeric($modelId) || ! $this->getVerifyModelIdIsNumeric()) {
            $modelClass = $this->getModelClass();

            if (strlen(trim($modelId)) !== 0) {
                try {
                    return new $modelClass($modelId);
                } catch (Opus_Model_NotFoundException $omnfe) {
                    if (in_array($modelId, Opus_EnrichmentKey::getAllReferenced())) {
                        // Sonderbehandlung: nicht registrierter, aber in Benutzung befindlicher Enrichment Key
                        $enrichmentKey = new Opus_EnrichmentKey();
                        $enrichmentKey->setName($modelId);
                        return $enrichmentKey;
                    }
                    $this->getLogger()->err(__METHOD__ . ':' . $omnfe->getMessage());
                }
            }
        }

        return null; // keine gültige ID
    }
}
