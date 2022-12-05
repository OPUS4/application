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

use Opus\Common\EnrichmentKey;

/**
 * Formular für die Anzeige der Enrichment-Tabelle.
 */
class Admin_Form_EnrichmentTable extends Application_Form_Model_Table
{
    private $enrichmentKeys;

    private $managedKeys = [];

    private $unmanagedKeys = [];

    public function init()
    {
        $this->enrichmentKeys = new Admin_Model_EnrichmentKeys();
        parent::init();
    }

    /**
     * Liefert true, wenn es sich um einen geschützten EnrichmentKey handelt; andernfalls false.
     *
     * @param EnrichmentKey $model
     * @return bool
     */
    public function isProtected($model)
    {
        return in_array($model->getId(), $this->enrichmentKeys->getProtectedEnrichmentKeys());
    }

    /**
     * Liefert true, wenn der EnrichmentKey in mindestens einem Enrichment eines Dokuments
     * verwendet wird; andernfalls false.
     *
     * @param EnrichmentKey $model
     * @return bool
     */
    public function isUsed($model)
    {
        return in_array($model->getId(), EnrichmentKey::getAllReferenced());
    }

    /**
     * Bestimmt die zu verwendene CSS-Klasse für den übergebenen EnrichmentKey in der Listenansicht.
     *
     * @param EnrichmentKey $model
     * @return string Name der zu nutzenden CSS-Klasse
     */
    public function getRowCssClass($model)
    {
        if (is_null($model->getId())) {
            // es handelt sich um einen nicht registrierten, in Benutzung befindlichen Enrichment Namen
            return "used";
        } elseif ($this->isProtected($model) and $this->isUsed($model)) {
            return "protected used";
        } elseif (! $this->isUsed($model) and $this->isProtected($model)) {
            return "protected unused";
        } elseif ($this->isUsed($model)) {
            return "used";
        } elseif ($this->isProtected($model)) {
            return "protected";
        } elseif (! $this->isUsed($model)) {
            return "unused";
        }

        return "";
    }

    /**
     * Bestimmt den Übersetzungsschlüssel des anzuzeigenden Tooltips für den übergebenen EnrichmentKey in
     * der Listenansicht.
     *
     * @param EnrichmentKey $model
     * @return string Übersetzungsschlüssel für den Tooltip
     */
    public function getRowTooltip($model)
    {
        if (is_null($model->getId())) {
            // es handelt sich um einen nicht registrierten, in Benutzung befindlichen Enrichment Namen
            return 'admin_enrichmentkey_unregistered_tooltip';
        } elseif ($this->isProtected($model) and $this->isUsed($model)) {
            return 'admin_enrichmentkey_used_tooltip';
        } elseif (! $this->isUsed($model) and $this->isProtected($model)) {
            return 'admin_enrichmentkey_unused_tooltip';
        } elseif ($this->isUsed($model)) {
            return 'admin_enrichmentkey_used_tooltip';
        } elseif (! $this->isUsed($model)) {
            return 'admin_enrichmentkey_unused_tooltip';
        }

        return "";
    }

    public function setModels($models)
    {
        parent::setModels($models);
        foreach ($models as $enrichmentKey) {
            if (is_null($enrichmentKey->getEnrichmentType())) {
                $this->unmanagedKeys[] = $enrichmentKey;
            } else {
                $this->managedKeys[] = $enrichmentKey;
            }
        }
    }

    public function getManaged()
    {
        return $this->managedKeys;
    }

    public function getUnmanaged()
    {
        return $this->unmanagedKeys;
    }
}
