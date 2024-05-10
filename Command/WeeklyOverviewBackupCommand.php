<?php

/*
 * This file is part of the WeeklyOverviewBundle.
 * All rights reserved by Maximilian Groß (www.maximiliangross.de).
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KimaiPlugin\WeeklyOverviewBundle\Command;

use KimaiPlugin\WeeklyOverviewBundle\Service\WeeklyOverviewService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// the name of the command is what users type after "php bin/console"

class WeeklyOverviewBackupCommand extends Command
{
    protected static $defaultName = 'WeeklyOverview:backup';
    // the command description shown when running "php bin/console list"
    protected static $defaultDescription = 'Creates a new backup.';

    private $router;
    private $easyBackupService;

    public function __construct(WeeklyOverviewService $easyBackupService)
    {
        $this->easyBackupService = $easyBackupService;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('WeeklyOverview:backup')
            ->setDescription('Creates a new backup.')
            ->setHelp('This command allows you to create a new backup e.g. via cronjob.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $log = $this->easyBackupService->createBackup();

        //$output->writeln($result);
        $output->writeln($log);

        return 0;

        // or return this if some error happened during the execution
        // (it's equivalent to returning int(1))
        // return Command::FAILURE;

        // or return this to indicate incorrect command usage; e.g. invalid options
        // or missing arguments (it's equivalent to returning int(2))
        // return Command::INVALID
    }
}
