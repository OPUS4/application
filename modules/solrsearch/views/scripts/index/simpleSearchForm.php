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
<div class="searchform">
    <h2><?= $this->translate('solrsearch_title_simple'); ?></h2>

    <form action="<?= $this->url(array('module'=>'solrsearch','controller'=>'index','action'=>'searchdispatch')); ?>" method="post" class="opus_form">
        <label for="query"><?= $this->translate('solrsearch_query_label'); ?></label>
        <input id="query" name="query" type="text" value="<?= htmlspecialchars($this->q) ?>" />
        <input type="submit" value="<?= $this->translate('solrsearch_searchaction'); ?>" />
        <input type="hidden" name="searchtype" id="searchtype" value="simple" />
        <input type="hidden" name="rows" id="rows" value="10" />
        <input type="hidden" name="start" id="start" value="0" />
        <input type="hidden" name="sortfield" id="sortfield" value="score" />
        <input type="hidden" name="sordorder" id="sortorder" value="desc" />
    </form>

    <a href="<?= $this->url(array('module'=>'solrsearch','controller'=>'index','action'=>'advanced'),null,true); ?>"><?= $this->translate('solrsearch_title_advanced'); ?></a>
</div>