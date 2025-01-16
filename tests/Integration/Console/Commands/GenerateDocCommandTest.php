<?php

declare(strict_types=1);

use IsmayilDev\LaravelDocKit\Console\Commands\GenerateDocCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;


it('it should work', function (): void {
    $application = new Application;

    $application->add(new GenerateDocCommand());
//
    $command = $application->find('doc:generate');
//
    $commandTester = new CommandTester($command);

//
    $commandTester->execute([]);
//
    $output = $commandTester->getDisplay();
//
    dd($output);
})->only();