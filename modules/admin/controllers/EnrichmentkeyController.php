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

    public function removefromdocsAction()
    {
        if ($this->getRequest()->isPost() === true) {
            // Bestätigungsformular POST verarbeiten
            $result = $this->handleConfirmationPost();
        } else {
            // Bestätigungsformular anzeigen
            $model = $this->getModel($this->getRequest()->getParam(self::PARAM_MODEL_ID));
            if (! is_null($model)) {
                if ($this->isDeletable($model)) {
                    $form = $this->getConfirmationForm($model);
                    $form->setLegend($this->view->translate('admin_enrichmentkey_remove_from_documents_title', $model->getName()));
                    $form->setQuestion($this->view->translate('admin_enrichmentkey_remove_from_documents_description', $model->getName()));
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
}
