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
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2014, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */


/**
 * Dieses Script gibt die IDs aller veröffentlichten Dokumente aus, bei denen
 * Jane Doe der Name des Autors ODER der Name einer sonstigen beteiligten
 * Personen (advisor, contributor, editor, other, translator) ist
 */

$firstName = "Jane";
$lastName = "Doe";

$docfinder = new Opus_DocumentFinder();
// wichtig: müssen diesen Filter setzen, da im Index nur Dokument im Zustand published sind
$docfinder->setServerState('published');
$select = $docfinder->getSelect();
$select
  ->joinLeft(
      ['pd' => 'link_persons_documents'],
      'd.id = pd.document_id AND (pd.role = "author"'
      . ' OR pd.role = "advisor" OR pd.role = "contributor" OR pd.role = "editor" OR pd.role = "other"'
      . ' OR pd.role = "translator")',
      []
  )
  ->joinLeft(['p' => 'persons'], 'pd.person_id = p.id', [])
  ->where('p.first_name = ?', $firstName)
  ->where('p.last_name = ?', $lastName)
  ->group('d.id');

foreach ($docfinder->ids() as $docId) {
    echo "DocID $docId\n";
}

exit();
