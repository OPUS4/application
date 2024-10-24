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
 * @copyright   Copyright (c) 2023, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class Application_Console_Collection_MoveCommand extends Application_Console_Collection_AbstractCollectionCommand
{
    protected function configure()
    {
        parent::configure();

        $help = <<<EOT
The <fg=green>collection:move</> command can be used to move the documents assigned to 
one collection to another collection. All documents are removed from the source collection,
even those that were already present in the destination collection.  
EOT;

        $this->setName('collection:move')
            ->setDescription('Moves documents from one collection to another')
            ->setHelp($help);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->processOptions($input);

        $sourceCol = $this->sourceCol;
        $destCol   = $this->destCol;

        if ($sourceCol === null) {
            $output->writeln('Source collection needs to be specified.');
            return 0;
        }

        if ($destCol === null) {
            $output->writeln('Destination collection needs to be specified.');
            return 0;
        }

        $sourceId = $sourceCol->getId();
        $destId   = $destCol->getId();

        $sourceDocuments = $sourceCol->getDocumentIds();

        $sourceCount = count($sourceDocuments);

        if ($sourceCount === 0) {
            $output->writeln("Collection (ID = ${sourceId}) does not contain documents.");
            $output->writeln('');
            $output->writeln('  "' . $sourceCol->getDisplayName() . '"');
            $output->writeln('');
            return 0;
        }

        $output->writeln("Move documents (${sourceCount}) from source collection (ID = ${sourceId})");
        $output->writeln('');
        $output->writeln('  "' . $sourceCol->getDisplayName() . '"');
        $output->writeln('');
        $output->writeln("to destination collection (ID = ${destId})");
        $output->writeln('');
        $output->writeln('  "' . $destCol->getDisplayName() . '"');
        $output->writeln('');

        if ($this->updateDateModified) {
            $output->writeln('NOTE: ServerDateModified of documents will be updated');
        } else {
            $output->writeln('NOTE: ServerDateModified of documents will NOT be updated');
        }
        $output->writeln('');

        $askHelper = $this->getHelper('question');
        $question  = new ConfirmationQuestion('Move documents [Y|n]?', true);

        if ($askHelper->ask($input, $output, $question)) {
            $sourceCol->moveDocuments($destCol->getId(), $this->updateDateModified);
        } else {
            $output->writeln('Moving cancelled');
        }

        return 0;
    }
}
