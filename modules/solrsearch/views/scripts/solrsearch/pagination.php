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

<div id="pagination" >
    <p class="info_small">
        <?= $this->translate('pagination_display_hits') ?> <strong><?= (int)($this->start)+1 ?></strong> <?= $this->translate('pagination_to') ?> <strong><?php if(((int)($this->start) + (int)($this->rows)) > $this->numOfHits) echo $this->numOfHits; else echo (int)($this->start) + (int)($this->rows); ?></strong>
    </p>
    <ul class="paginationControl">

        <?php if($this->start >= $this->rows) : ?>
        <li>
            <a href="<?= $this->url($this->firstPage); ?>">
                <?= $this->translate('pagination_first_page') ?>
            </a>
        </li>
        <li>
            <a href="<?= $this->url($this->prevPage); ?>">
                <?= $this->translate('pagination_previous_page') ?>
            </a>
        </li>
        <?php endif ?>

        <?php if($this->start < ((int)($this->numOfHits) - (int)($this->rows))) : ?>
        <li>
            <a href="<?= $this->url($this->nextPage); ?>">
                <?= $this->translate('pagination_next_page') ?>
            </a>
        </li>
        <li>
            <a href="<?= $this->url($this->lastPage); ?>">
                <?= $this->translate('pagination_last_page') ?>
            </a>
        </li>
        <?php endif ?>
    </ul>
</div>