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
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Console\AbstractDocumentCommand;
use Opus\Common\Console\Helper\ProgressMatrix;
use Opus\Common\Console\Helper\ProgressOutputInterface;
use Opus\Common\Document;
use Opus\Common\Model\NotFoundException;
use Opus\Search\Console\Helper\DocumentHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * TODO use switch instead of singleDocument and removeAll (?)
 */
class Application_Console_Document_DeleteCommand extends AbstractDocumentCommand
{
    public const OPTION_PERMANENT = 'permanent';

    protected function configure()
    {
        parent::configure();

        $help = <<<EOT
The <fg=green>document:delete</> command can be used to delete a single document or a 
range of documents. It can set the status of documents to <fg=yellow>deleted</> or remove
the metadata and files of documents permanently (<fg=green>--permanent</> or <fg=green>-p</>). 

If no <fg=green>ID</> is provided, all documents will be deleted. You can use a dash (<fg=yellow>-</>)
as <fg=green>StartID</> or <fg=green>EndID</>, if you want to delete all documents up to or starting
from an ID. 

Examples:
  <fg=yellow></>        will delete all documents 
  <fg=yellow>50</>      will delete document 50
  <fg=yellow>20 60</>   will delete documents 20 to 60
  <fg=yellow>20 -</>    will delete all documents starting from 20
  <fg=yellow>- 50</>    will delete all documents up to 50
  
Deleting a document will only set its <fg=green>ServerState</> to <fg=yellow>deleted</>. 

If a document is deleted <fg=green>permanently</>, it is removed from the database and 
its files are deleted from the filesystem. This operation is not reversible.  
EOT;

        $this->setName('document:delete')
            ->setDescription('Deletes documents from database')
            ->setHelp($help)
            ->addOption(
                self::OPTION_PERMANENT,
                'p',
                null,
                'Permanently remove metadata and files or documents'
            );
    }

    /**
     * @return int
     * @throws NotFoundException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->processArguments($input);

        $startId = $this->startId;
        $endId   = $this->endId;

        $permanent = $input->getOption(self::OPTION_PERMANENT);

        $askHelper = $this->getHelper('question');

        // TODO do not use IndexHelper to get document IDs (has nothing to do with index -> create new helper)
        $helper = new DocumentHelper();

        if ($this->isSingleDocument()) {
            $docIds = [$startId];
        } else {
            $docIds = $helper->getDocumentIds($startId, $endId);
        }

        $docCount = count($docIds);

        if ($permanent) {
            $permanentText = 'permanently (not reversible) ';
        } else {
            $permanentText = '';
        }

        if ($this->isSingleDocument()) {
            $questionText = "Delete document $startId {$permanentText}[Y,n]? ";
        } elseif ($this->isAllDocuments()) {
            $questionText = "Delete all ($docCount) documents {$permanentText}[Y,n]? ";
        } else {
            $questionText = "Delete $docCount documents from $startId to $endId {$permanentText}[Y,n]? ";
        }

        $question = new ConfirmationQuestion($questionText, true);

        if ($askHelper->ask($input, $output, $question)) {
            if ($this->isSingleDocument()) {
                $this->deleteDocument($startId, $permanent);
                $output->writeln("Document $startId has been deleted");
            } else {
                $progress = new ProgressMatrix($output, $docCount);
                $this->deleteDocuments($progress, $docIds, $permanent);
            }
        } else {
            $output->writeln('Deletion cancelled');
        }

        return 0;
    }

    /**
     * @param ProgressOutputInterface $progress
     * @param int[]                   $docIds
     * @param bool                    $permanent
     */
    protected function deleteDocuments($progress, $docIds, $permanent = false)
    {
        $progress->start();
        foreach ($docIds as $docId) {
            $this->deleteDocument($docId, $permanent);
            $progress->advance();
        }
        $progress->finish();
    }

    /**
     * @param int  $docId
     * @param bool $permanent
     * @throws NotFoundException
     */
    protected function deleteDocument($docId, $permanent = false)
    {
        $doc = Document::get($docId);
        if ($permanent) {
            $doc->delete();
        } else {
            $doc->setServerState(Document::STATE_DELETED);
            $doc->store();
        }
    }
}
