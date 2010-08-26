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
        <label for="default_operator">Suchen nach den Kriterien aus </label>
        <select name="defaultoperator" id="default_operator">
            <option value="AND" <?= isset($this->defaultoperator) && $this->defaultoperator === 'AND' ? 'selected="true"' : '' ?>>allen Zeilen</option>
            <option value="OR" <?= isset($this->defaultoperator) && $this->defaultoperator === 'OR' ? 'selected="true"' : '' ?>>mindestens einer Zeile</option>
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
                    <label for="author">Autor(en)</label>
                </td>
                <td>
                    <select name="authormodifier">
                        <option value="<?= Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_ALL ?>" <?= $this->authorQueryModifier === Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_ALL || !isset($this->authorQueryModifier) ? 'selected="true"' : '' ?>>alle W&ouml;rter</option>
                        <option value="<?= Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_ANY ?>" <?= $this->authorQueryModifier === Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_ANY ? 'selected="true"' : '' ?>>mindestens ein Wort</option>
                        <option value="<?= Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_NONE ?>" <?= $this->authorQueryModifier === Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_NONE ? 'selected="true"' : '' ?>>keines der W&ouml;rter</option>
                    </select>
                </td>
                <td>
                    <input type="text" id="author" name="author" value="<?= htmlspecialchars($this->authorQuery) ?>" title="Sie k&ouml;nnen in dieses Feld auch mehrere Autoren eingeben." />
                </td>
            </tr>
            <tr>
                <td>
                    <label for="title">Titel</label>
                </td>
                <td>
                    <select name="titlemodifier">
                        <option value="<?= Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_ALL ?>" <?= $this->titleQueryModifier === Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_ALL || !isset($this->titleQueryModifier) ? 'selected="true"' : '' ?>>alle W&ouml;rter</option>
                        <option value="<?= Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_ANY ?>" <?= $this->titleQueryModifier === Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_ANY ? 'selected="true"' : '' ?>>mindestens ein Wort</option>
                        <option value="<?= Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_NONE ?>" <?= $this->titleQueryModifier === Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_NONE ? 'selected="true"' : '' ?>>keines der W&ouml;rter</option>
                    </select>
                </td>
                <td>
                    <input type="text" id="title" name="title" value="<?= htmlspecialchars($this->titleQuery) ?>" />
                </td>
            </tr>
            <tr>
                <td>
                    <label for="evaluator">Gutachter</label>
                </td>
                <td>
                    <select name="evaluatormodifier">
                        <option value="<?= Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_ALL ?>" <?= $this->evaluatorQueryModifier === Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_ALL || !isset($this->evaluatorQueryModifier) ? 'selected="true"' : '' ?>>alle W&ouml;rter</option>
                        <option value="<?= Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_ANY ?>" <?= $this->evaluatorQueryModifier === Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_ANY ? 'selected"true"' : '' ?>>mindestens ein Wort</option>
                        <option value="<?= Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_NONE ?>" <?= $this->evaluatorQueryModifier === Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_NONE ? 'selected="true"' : '' ?>>keines der W&ouml;rter</option>
                    </select>
                </td>
                <td>
                    <input type="text" name="evaluator" id="evaluator" value="<?=  htmlspecialchars($this->evaluatorQuery) ?>" />
                </td>
            </tr>
            <tr>
                <td>
                    <label for="abstract">Abstract</label>
                </td>
                <td>
                    <select name="abstractmodifier">
                        <option value="<?= Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_ALL ?>" <?= $this->abstractQueryModifier === Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_ALL || !isset($this->abstractQueryModifier) ? 'selected="true"' : '' ?>>alle W&ouml;rter</option>
                        <option value="<?= Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_ANY ?>" <?= $this->abstractQueryModifier === Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_ANY ? 'selected"true"' : '' ?>>mindestens ein Wort</option>
                        <option value="<?= Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_NONE ?>" <?= $this->abstractQueryModifier === Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_NONE ? 'selected="true"' : '' ?>>keines der W&ouml;rter</option>
                    </select>
                </td>
                <td>
                    <input type="text" id="abstract" name="abstract" value="<?= htmlspecialchars($this->abstractQuery) ?>" />
                </td>
            </tr>
            <tr>
                <td>
                    <label for="abstract">Volltext</label>
                </td>
                <td>
                    <select name="fulltextmodifier">
                        <option value="<?= Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_ALL ?>" <?= $this->fulltextQueryModifier === Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_ALL || !isset($this->fulltextQueryModifier) ? 'selected="true"' : '' ?>>alle W&ouml;rter</option>
                        <option value="<?= Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_ANY ?>" <?= $this->fulltextQueryModifier === Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_ANY ? 'selected"true"' : '' ?>>mindestens ein Wort</option>
                    </select>
                </td>
                <td>
                    <input type="text" id="fulltext" name="fulltext" value="<?= htmlspecialchars($this->fulltextQuery) ?>" />
                </td>
            </tr>
            <?php if($this->searchType != 'authorsearch') : ?>
            <tr>
                <td>
                    <label for="year">Erscheinungsjahr</label>
                </td>
                <td>
                    <select name="yearmodifier">
                        <option value="<?= Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_ALL ?>" <?= $this->yearQueryModifier === Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_ALL || !isset($this->yearQueryModifier) ? 'selected="true"' : '' ?>>alle W&ouml;rter</option>
                        <option value="<?= Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_ANY ?>" <?= $this->yearQueryModifier === Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_ANY ? 'selected"true"' : '' ?>>mindestens ein Wort</option>
                        <option value="<?= Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_NONE ?>" <?= $this->yearQueryModifier === Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_NONE ? 'selected="true"' : '' ?>>keines der W&ouml;rter</option>
                    </select>
                </td>
                <td>
                    <input type="text" id="year" name="year" value="<?= htmlspecialchars($this->yearQuery) ?>" />
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