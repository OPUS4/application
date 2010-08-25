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

<form action="<?= $this->url(array('module'=>'solrsearch','controller'=>'solrsearch','action'=>'searchdispatch')); ?>" method="post">

    <?php if($this->searchType != 'authorsearch') : ?>
    <fieldset>
        <legend>Allgemeine Suchoptionen</legend>
        <label for="default_operator">Suchergebnisse enthalten</label>
        <select name="defaultoperator" id="default_operator">
            <option value="AND" <?= isset($this->defaultoperator) && $this->defaultoperator === 'AND' ? 'selected="true"' : '' ?>>alle Begriffe</option>
            <option value="OR" <?= isset($this->defaultoperator) && $this->defaultoperator === 'OR' ? 'selected="true"' : '' ?>>mindestens einen Begriff</option>
        </select>

        <br/>

        <label for="rows">Treffer pro Seite</label>
        <select name="rows" id="rows">
            <option value="10" <?= $this->rows === '10' || !isset($this->rows)? 'selected="true"' : '' ?>>10</option>
            <option value="20" <?= $this->rows === '20' ? 'selected="true"' : '' ?>>20</option>
            <option value="50" <?= $this->rows === '50' ? 'selected="true"' : '' ?>>50</option>
            <option value="100" <?= $this->rows === '100' ? 'selected="true"' : '' ?>>100</option>
        </select>
    </fieldset>
    <?php endif ?>

    <fieldset>
        <legend>Suchfelder</legend>

        <table>
            <tr>
                <td>
                    <label for="author">Autor</label>
                </td>
                <td>
                    <select name="authormodifier">
                        <option value="+" <?= $this->authorQueryModifier === '+' || !isset($this->authorQueryModifier) ? 'selected="true"' : '' ?>>enth&auml;lt</option>
                        <option value="-" <?= $this->authorQueryModifier === '-' ? 'selected="true"' : '' ?>>enth&auml;lt nicht</option>
                    </select>
                </td>
                <td>
                    <input type="text" id="author" name="author" value="<?= isset($this->authorQuery) ? $this->authorQuery : '' ?>" />
                </td>
            </tr>
            <tr>
                <td>
                    <label for="title">Titel</label>
                </td>
                <td>
                    <select name="titlemodifier">
                        <option value="+" <?= $this->titleQueryModifier === '+' || !isset($this->titleQueryModifier) ? 'selected="true"' : '' ?>>enth&auml;lt</option>
                        <option value="-" <?= $this->titleQueryModifier === '-' ? 'selected="true"' : '' ?>>enth&auml;lt nicht</option>
                    </select>
                </td>
                <td>
                    <input type="text" id="title" name="title" value="<?= isset($this->titleQuery) ? $this->titleQuery : '' ?>" />
                </td>
            </tr>
            <tr>
                <td>
                    <label for="evaluator">Gutachter</label>
                </td>
                <td>
                    <select name="evaluatormodifier">
                        <option value="+" <?= $this->evaluatorQueryModifier === '+' || !isset($this->evaluatorQueryModifier) ? 'selected="true"' : '' ?>>enth&auml;lt</option>
                        <option value="-" <?= $this->evaluatorQueryModifier === '-' ? 'selected"true"' : '' ?>>enth&auml;lt nicht</option>
                    </select>
                </td>
                <td>
                    <input type="text" name="evaluator" id="evaluator" value="<?= isset($this->evaluatorQuery) ? $this->evaluatorQuery : '' ?>" />
                </td>
            </tr>
            <tr>
                <td>
                    <label for="abstract">Volltext</label>
                </td>
                <td>
                    enth&auml;lt
                </td>
                <td>
                    <input type="text" id="abstract" name="abstract" value="<?= isset($this->abstractQuery) ? $this->abstractQuery : '' ?>" />
                </td>
            </tr>
            <?php if($this->searchType != 'authorsearch') : ?>
            <tr>
                <td>
                    <label for="year">Erscheinungsjahr</label>
                </td>
                <td>
                    <select name="yearmodifier">
                        <option value="+" <?= $this->yearQueryModifier === '+' || !isset($this->yearQueryModifier) ? 'selected="true"' : '' ?>>enth&auml;lt</option>
                        <option value="-" <?= $this->yearQueryModifier === '-' ? 'selected="true"' : '' ?>>enth&auml;lt nicht</option>
                    </select>
                </td>
                <td>
                    <input type="text" id="year" name="year" value="<?= isset($this->yearQuery) ? $this->yearQuery : '' ?>" />
                </td>
            </tr>
            <?php endif ?>

        </table>

    </fieldset>

    <input type="submit" value="Suchen" />

    <input type="hidden" name="searchtype" value="advanced" />
    <input type="hidden" name="start" value="0" />
    <input type="hidden" name="sortfield" value="score" />
    <input type="hidden" name="sordorder" value="desc" />

</form>