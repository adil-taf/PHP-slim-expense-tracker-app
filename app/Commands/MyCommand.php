<?php

declare(strict_types=1);

namespace App\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:my-command',
    description: 'My Command.'
)]
class MyCommand extends Command
{
    //protected function configure(): void
    //{
    //    $this->setName('app:my-command')
    //      ->setDescription('My Command.');
    //}

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->write('Hello World', true);

        return Command::SUCCESS;
    }
}
