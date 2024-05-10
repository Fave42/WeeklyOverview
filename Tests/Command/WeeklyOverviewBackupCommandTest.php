<?php

/*
 * This file is part of the WeeklyOverviewBundle.
 * All rights reserved by Maximilian Groß (www.maximiliangross.de).
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KimaiPlugin\WeeklyOverviewBundle\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class WeeklyOverviewBackupCommandTest extends KernelTestCase
{
    public function testExecute()
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('WeeklyOverview:backup');
        $commandTester = new CommandTester($command);
        $commandTester->execute();

        $commandTester->assertCommandIsSuccessful();

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Some output', $output);

        // ...
    }
}
