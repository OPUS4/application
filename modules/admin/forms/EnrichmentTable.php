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
 */

/**
 * Formular für die Anzeige der Enrichment-Tabelle.
 *
 * @category    Application
 * @package     Application_Form_Model
 * @author      Maximilian Salomon <salomon@zib.de>
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Admin_Form_EnrichmentTable extends Application_Form_Model_Table
{
    private $enrichmentKeys;

    public function init()
    {
        $this->enrichmentKeys = new Admin_Model_EnrichmentKeys();
        parent::init();
    }

    /**
     * Liefert true, wenn es sich um einen geschützten EnrichmentKey handelt; andernfalls false.
     *
     * @param Opus_EnrichmentKey $model
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
     * @param Opus_EnrichmentKey $model
     * @return bool
     */
    public function isUsed($model)
    {
        return in_array($model->getId(), Opus_EnrichmentKey::getAllReferenced());
    }

    public function getRowCssClass($model)
    {
        if ($this->isProtected($model) and $this->isUsed($model)) {
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

    public function getRowTooltip($model)
    {
        if ($this->isProtected($model) and $this->isUsed($model)) {
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
}
