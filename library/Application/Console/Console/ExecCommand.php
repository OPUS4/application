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

use Opus\Common\ConfigTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Console command to execute PHP code snippet files.
 */
class Application_Console_Console_ExecCommand extends Command
{
    use ConfigTrait;

    /** Argument for the PHP code snippet file(s) to be executed */
    public const ARGUMENT_SNIPPET_FILES = 'SnippetFile';

    protected function configure()
    {
        parent::configure();

        $help = <<<EOT
The <fg=green>console:exec</> command can be used to execute PHP code snippet file(s).

Snippets are short PHP scripts using the OPUS 4 API to modify documents, 
generate statistics, do cleanup or any other kind of custom task. In general 
snippets have been replaced by 'bin/opus4' commands, but can still be useful
for tasks required locally.  

Snippets are usually located in 'scripts\snippets'. This command can be used 
with just the name of a snippet located in the default snippet folder or a 
complete path to the snippet.
EOT;

        $this->setName('console:exec')
            ->setDescription('Executes PHP code snippet file(s)')
            ->setHelp($help)
            ->addArgument(
                self::ARGUMENT_SNIPPET_FILES,
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'Snippet file name or path (multiple snippets can be separated by spaces)'
            );
    }

    /**
     * Executes this command to run the PHP code from the passed snippet file(s).
     *
     * This function cannot judge if a snippet was executed "successfully", only if there has been an exception.
     *
     * TODO snippets with parameters?
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $snippetFiles = $input->getArgument(self::ARGUMENT_SNIPPET_FILES);

        foreach ($snippetFiles as $snippetFile) {
            $snippetPath = $this->resolveSnippetPath($snippetFile);

            if ($snippetPath === null) {
                $output->writeln('# snippet ' . $snippetFile . ' not found');
                continue;
            }

            try {
                $output->writeln('# executing snippet ' . $snippetPath);
                include_once $snippetPath;
            } catch (Exception $e) {
                $output->writeln('# failed including snippet ' . $snippetPath . ':');
                $output->writeln('Caught exception ' . get_class($e) . ': ' . $e->getMessage());
                $output->writeln($e->getTraceAsString());
                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }

    /**
     * @param string $snippetFile
     * @return string|null
     */
    protected function resolveSnippetPath($snippetFile)
    {
        if (is_readable($snippetFile)) {
            return $snippetFile;
        }

        $config = $this->getConfig();

        if (! isset($config->snippets->basePath)) {
            return null;
        }

        $snippetFolder = rtrim($config->snippets->basePath, '/\\');
        $snippetPath   = $snippetFolder . '/' . $snippetFile;

        if (! preg_match('/\.php$/i', $snippetPath)) {
            $snippetPath .= '.php';
        }

        if (! is_readable($snippetPath)) {
            return null;
        }

        return $snippetPath;
    }
}
