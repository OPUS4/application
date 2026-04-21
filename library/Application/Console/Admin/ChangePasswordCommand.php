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
 * @copyright   Copyright (c) 2026, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Account;
use Opus\Common\Security\SecurityException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Set password of user account.
 *
 * TODO no password requirements are enforced
 */
class Application_Console_Admin_ChangePasswordCommand extends Command
{
    public const ARGUMENT_USER = 'user';

    public const OPTION_PASSWORD = 'password';

    protected function configure()
    {
        parent::configure();

        $help = <<<EOT
Sets the password of a user account. The password is entered interactively.

The <info>--password</info> option can be used to provide a new password without interaction, however this should be used carefully, since the password won't be hidden.
EOT;

        $this->setName('account:setpwd')
            ->setDescription('Set user password')
            ->setHelp($help)
            ->addArgument(
                self::ARGUMENT_USER,
                InputArgument::REQUIRED,
                'User login'
            )
            ->addOption(
                self::OPTION_PASSWORD,
                '-p',
                InputOption::VALUE_REQUIRED,
                'New password'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $formatter = new FormatterHelper();

        $login = $input->getArgument(self::ARGUMENT_USER);

        try {
            $account = Account::fetchAccountByLogin($login);
        } catch (SecurityException $e) {
            $formatted = $formatter->formatBlock('User not found', 'error');
            $output->writeln($formatted);
            return self::FAILURE;
        }

        $password = $input->getOption(self::OPTION_PASSWORD);

        if ($password === null) {
            $helper = new QuestionHelper();

            $question = new Question('New password: ');
            $question->setHidden(true);
            $question->setHiddenFallback(false);
            $password = $helper->ask($input, $output, $question);

            $question = new Question('Confirm password: ');
            $question->setHidden(true);
            $question->setHiddenFallback(false);
            $confirm = $helper->ask($input, $output, $question);

            if ($password !== $confirm) {
                $formatted = $formatter->formatBlock('Passwords do not match.', 'error');
                $output->writeln($formatted);
                return self::FAILURE;
            }
        }

        try {
            $account->setPassword($password)->store();
        } catch (SecurityException $e) {
            $formatted = $formatter->formatBlock('Setting password failed', 'error');
            $output->writeln($formatted);
            return self::FAILURE;
        }

        $output->writeln('Password successfully changed.');

        return self::SUCCESS;
    }
}
