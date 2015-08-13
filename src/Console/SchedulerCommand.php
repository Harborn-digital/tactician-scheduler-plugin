<?php

namespace ConnectHolland\Tactician\SchedulerPlugin\Console;

use ConnectHolland\Tactician\SchedulerPlugin\Command\ExecuteScheduledCommandsCommand;
use League\Tactician\CommandBus;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Symfony console command to pick up commands from the scheduler and put them on the command bus.
 *
 * @author ron
 */
class SchedulerCommand extends Command
{
    /**
     * configure.
     *
     * Sets the command variables and it's inputs
     **/
    protected function configure()
    {
        $this->setName('scheduler:execute');
        $this->addArgument(
            'bootstrap',
            InputArgument::REQUIRED,
            'A bootstrap file to setup your environment and Command Bus, should return your command bus'
        );
    }

    /**
     * execute.
     *
     * Puts the ExecuteScheduledCommandsCommand on the bus provided by the bootstrap
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     **/
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bootstrap = $input->getArgument('bootstrap');

        $commandbus = require $bootstrap;

        if (!$commandbus instanceof CommandBus) {
            $output->writeln('<error>Bootstrap did not return a proper CommandBus</error>');
        } else {
            $commandbus->handle(new ExecuteScheduledCommandsCommand($commandbus));
        }
    }
}
