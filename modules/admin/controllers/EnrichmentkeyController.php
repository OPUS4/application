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

use Opus\Common\Enrichment;
use Opus\Common\EnrichmentKey;
use Opus\Common\EnrichmentKeyInterface;
use Opus\Common\Model\NotFoundException;

/**
 * All enrichment keys are shown, but only enrichment keys that are not protected
 * can be edited or deleted. An enrichment key is protected if it is configured
 * as such in the configuration file or if it is referenced by at least one document.
 *
 * The two configuration parameters are:
 *
 * enrichmentkey.protected.modules   (for special enrichments used by modules)
 * enrichmentkey.protected.migration (for enrichments created during migration from OPUS 3)
 *
 * TODO show protected/referenced in list of keys
 * TODO move to setup area (maybe in own module, but part of setup in administration)
 */
class Admin_EnrichmentkeyController extends Application_Controller_ActionCRUD
{
    /**
     * Model for handling enrichment keys.
     *
     * @var Admin_Model_EnrichmentKeys
     */
    private $enrichmentKeys;

    /**
     * Initializes and configures controller.
     *
     * @throws Application_Exception
     */
    public function init()
    {
        $this->enrichmentKeys = new Admin_Model_EnrichmentKeys();
        $this->setVerifyModelIdIsNumeric(false);
        $this->setShowActionEnabled(false);
        $this->setFormClass('Admin_Form_EnrichmentKey');
        parent::init();
    }

    /**
     * Modifiziert Formular für Indextabelle, so dass angepasstes ViewScript verwendet wird.
     *
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
     * @param EnrichmentKeyInterface $model
     * @return bool true if model can be edited and deleted, false if model is protected
     */
    public function isModifiable($model)
    {
        $protectedKeys = $this->enrichmentKeys->getProtectedEnrichmentKeys();
        // hier kann $model->getId() statt $model->getName() verwendet werden,
        // weil die Tabelle enrichmentkeys keine Spalte mit dem Namen 'id' besitzt
        return ! in_array($model->getId(), $protectedKeys);
    }

    /**
     * Erzeugt Formular zum Hinzufügen eines neuen Enrichment Keys.
     * Wird das Formular für einen bereits in Benutzung befindlichen Enrichment Key, der noch nicht registriert ist,
     * aufgerufen, so wird das Formularfeld für den Namen bereits gefüllt.
     *
     * @return Application_Form_ModelFormInterface
     */
    public function getNewModelForm()
    {
        $form = parent::getNewModelForm();
        if ($this->getRequest()->isGet()) {
            $name = $this->getRequest()->getParam(self::PARAM_MODEL_ID);
            if ($name !== null && trim($name) !== '') {
                // prüfe, ob bereits ein nicht registrierter EnrichmentKey mit den Namen existiert, der in Benutzung ist
                if (
                    EnrichmentKey::fetchByName($name) === null &&
                    in_array($name, EnrichmentKey::getAllReferenced())
                ) {
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
     *
     * @param array|null $post
     * @return array|string|null
     */
    public function handleModelPost($post = null)
    {
        if ($post === null) {
            $post = $this->getRequest()->getPost();
        }

        // handelt es sich um die newAction und wird der per Request Parameter referenzierte Enrichment Key
        // in Dokumenten verwendet und ist er nicht registriert, dann muss überprüft werden, ob eine
        // Registrierung des Enrichment Keys mit gleichzeitiger Umbenennung vorliegt
        $renamingOfUnregisteredKey = false;
        $oldName                   = null;
        $newName                   = null;

        if (! in_array(self::PARAM_MODEL_ID, $post) || $post[self::PARAM_MODEL_ID] === '') {
            // Verarbeitung im Kontext der newAction: keine Model-ID im POST Request enthalten oder leer

            $oldName = $this->getRequest()->getParam(self::PARAM_MODEL_ID);
            if (
                $oldName !== null &&
                EnrichmentKey::fetchByName($oldName) === null &&
                in_array($oldName, EnrichmentKey::getAllReferenced())
            ) {
                $newName = $post[Admin_Form_EnrichmentKey::ELEMENT_NAME];
                if ($newName !== null && $oldName !== $newName) {
                    // Neuregistrierung mit gleichzeitiger Umbenennung des Enrichment Keys
                    $renamingOfUnregisteredKey = true;
                }
            }
        }

        $result = parent::handleModelPost($post);

        if ($renamingOfUnregisteredKey && is_array($result) && in_array(self::SAVE_SUCCESS, $result)) {
            // Enrichment Key wurde erfolgreich registriert: Umbenennung des EK in den Dokumenten durchführen
            $enrichmentKey = EnrichmentKey::fetchByName($newName);
            if ($enrichmentKey !== null) {
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
            if ($enrichmentKey !== null && in_array($enrichmentKey->getName(), EnrichmentKey::getAllReferenced())) {
                if ($enrichmentKey->getId() === null) {
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
     * @param EnrichmentKeyInterface $model
     * @param bool                   $unregistered
     * @return Application_Form_Confirmation
     */
    private function initConfirmationForm($model, $unregistered = false)
    {
        $modelName = $model->getName();
        $form      = $this->getConfirmationForm($model, $unregistered);
        if ($unregistered) {
            $form->setLegend($this->view->translate(
                'admin_enrichmentkey_remove_unregistered_from_documents_title',
                $modelName
            ));
            $form->setQuestion($this->view->translate(
                'admin_enrichmentkey_remove_unregistered_from_documents_description',
                $modelName
            ));
            $form->getElement(Application_Form_Confirmation::ELEMENT_MODEL_ID)->setValue($model->getName());
        } else {
            $form->setLegend($this->view->translate(
                'admin_enrichmentkey_remove_from_documents_title',
                $modelName
            ));
            $form->setQuestion($this->view->translate(
                'admin_enrichmentkey_remove_from_documents_description',
                $modelName
            ));
        }
        return $form;
    }

    /**
     * @param ModelInterface $model
     */
    public function deleteModel($model)
    {
        $request = $this->getRequest();
        // Kontext ermitteln: Löschen des EKs oder nur Enrichments mit dem EK aus den Dokumenten löschen
        if ($request->getActionName() === 'removeFromDocs') {
            $model->deleteFromDocuments();
        } else {
            $name = $model->getName();
            parent::deleteModel($model);
            $helper = new Admin_Model_EnrichmentKeys();
            $helper->removeTranslations($name);
        }
    }

    /**
     * @return array
     */
    public function getAllModels()
    {
        // determine names of all enrichment keys
        $allKeyNames = Enrichment::getAllUsedEnrichmentKeyNames();

        $registeredEnrichmentKeys = parent::getAllModels();
        $mapNamesToEnrichmentKeys = [];
        foreach ($registeredEnrichmentKeys as $enrichmentKey) {
            $name                            = $enrichmentKey->getName();
            $mapNamesToEnrichmentKeys[$name] = $enrichmentKey;
            if (! in_array($name, $allKeyNames)) {
                $allKeyNames[] = $name;
            }
        }

        sort($allKeyNames, SORT_FLAG_CASE);

        $result = [];
        foreach ($allKeyNames as $keyName) {
            if (array_key_exists($keyName, $mapNamesToEnrichmentKeys)) {
                $result[] = $mapNamesToEnrichmentKeys[$keyName];
            } else {
                $newEnrichmentKey = EnrichmentKey::new();
                $newEnrichmentKey->setName($keyName);
                $result[] = $newEnrichmentKey;
            }
        }
        return $result;
    }

    /**
     * Liefert eine Instanz von Enrichment mit übergebenen $modelId, sofern diese existiert (andernfalls null).
     * Hier wird der Sonderfall behandelt, dass die Id (d.h. der EnrichmentKey Name) nicht registriert ist.
     * Wird der nicht registrierte EnrichmentKey Name in Dokumenten verwendet, so wird ebenfalls eine Instanz von
     * Enrichment zurückgegeben, die allerdings nicht in der Datenbank persistiert ist.
     *
     * @param int $modelId
     * @return EnrichmentKeyInterface|null
     */
    public function getModel($modelId)
    {
        if ($modelId === null || is_numeric($modelId) || ! $this->getVerifyModelIdIsNumeric()) {
            $modelClass = $this->getModelClass();

            if ($modelId !== null && strlen(trim($modelId)) !== 0) {
                try {
                    return $modelClass::get($modelId);
                } catch (NotFoundException $omnfe) {
                    if (in_array($modelId, EnrichmentKey::getAllReferenced())) {
                        // Sonderbehandlung: nicht registrierter, aber in Benutzung befindlicher Enrichment Key
                        $enrichmentKey = EnrichmentKey::new();
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
