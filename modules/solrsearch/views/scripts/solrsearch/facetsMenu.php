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
 * @category    TODO
 * @author      Julian Heise <heise@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
?>

<div id="facets">
    <h3>Verfeinern Sie Ihre Suche</h3>
    <?php if (isset ($this->yearFacet)) : ?>
        <div id="yearFacet" class="facet">
            Erscheinungsjahr:
            <ul>
            <?php foreach ($this->yearFacet as $facetItem) :
                $yearfq = $this->firstPage;
                $yearfq['yearfq'] = $facetItem->getText();
            ?>
                <li><a href="<?= $this->url($yearfq) ?>"><?= $facetItem->getText() ?></a> (<?= $facetItem->getCount() ?>)</li>
            <?php endforeach ?>
            </ul>
        </div>
    <?php endif ?>
    <?php if(isset($this->authorFacet)) : ?>
        <div id="authorFacet" class="facet" >
            Autor
            <ul>
            <?php foreach($this->authorFacet as $facetItem) :
                $authorfq = $this->firstPage;
                $authorfq['authorfq'] = $facetItem->getText();
            ?>
                <li><a href="<?= $this->url($authorfq) ?>"><?= $facetItem->getText() ?></a> (<?= $facetItem->getCount() ?>)</li>
            <?php endforeach ?>
            </ul>
        </div>
    <?php endif ?>
    <?php if(isset($this->doctypeFacet)) : ?>
        <div id="doctypeFacet" class="facet">
            Dokumententyp
            <ul>
            <?php foreach($this->doctypeFacet as $facetItem) :
                $doctypefq = $this->firstPage;
                $doctypefq['doctypefq'] = $facetItem->getText();
            ?>
                <li><a href="<?= $this->url($doctypefq) ?>"><?= $facetItem->getText() ?></a> (<?= $facetItem->getCount() ?>)</li>
            <?php endforeach ?>
            </ul>
        </div>
    <?php endif ?>
    <?php if(isset($this->languageFacet)) : ?>
        <div id="languageFacet" class="facet">
            Sprache
            <ul>
            <?php foreach($this->languageFacet as $facetItem) :
                $languagefq = $this->firstPage;
                $languagefq['languagefq'] = $facetItem->getText();
            ?>
                <li><a href="<?= $this->url($languagefq) ?>"><?= $facetItem->getText() ?></a> (<?= $facetItem->getCount() ?>)</li>
            <?php endforeach ?>
            </ul>
        </div>
    <?php endif ?>
</div>