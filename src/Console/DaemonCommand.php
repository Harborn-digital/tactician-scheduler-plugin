<?php
namespace ConnectHolland\Tactician\SchedulerPlugin\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to run the scheduler as a daemon, alternative to using a crontab
 *
 * @author ron
 */
class DaemonCommand extends SchedulerCommand
{
     /**
     * configure.
     *
     * Sets the command variables and it's inputs
     **/
    protected function configure()
    {
        parent::configure();
        $this->setName('scheduler:daemon');
        $this->addArgument(
            'interval',
            InputArgument::REQUIRED,
            'An integer defining every how many seconds the daemon should try to execute commands'
        );
        $this->addArgument(
            'count',
            InputArgument::OPTIONAL,
            'Stop the daemon after count runs'
        );
    }

     /**
     * execute.
     *
     * Calls the parent execut every $interval seconds
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     **/
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $interval = $input->getArgument('interval');
        $count = $input->getArgument('count');

        do {
            parent::execute($input, $output);
            sleep($interval);
            if (isset($count)) {
                $count--;
            }
        } while (!isset($count) || ($count > 0));
    }
}
