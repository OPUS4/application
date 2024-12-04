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
 * @copyright   Copyright (c) 2024, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\EnrichmentKey;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for listing enrichments.
 *
 * TODO option to only list used enrichments
 * TODO show number of documents using enrichment key
 */
class Application_Console_Model_EnrichmentListCommand extends Command
{
    protected function configure()
    {
        parent::configure();

        $help = <<<EOT
The <fg=green>enrichment:list</> command lists all enrichments present in the
database. 
EOT;

        $this->setName('enrichment:list')
            ->setDescription('Lists enrichments')
            ->setHelp($help);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $allKeys        = EnrichmentKey::getAll();
        $referencedKeys = EnrichmentKey::getAllReferenced();

        $rows = [];

        foreach ($allKeys as $key) {
            $rows[] = [
                $key,
                in_array($key, $referencedKeys) ? 'used' : '',
            ];
        }

        $table = new Table($output);
        $table->setHeaders(['Enrichment-Key', 'Used'])
            ->setRows($rows);
        $table->render();

        return 0;
    }
}
