<?php

namespace ConnectHolland\Tactician\SchedulerPlugin\Middleware;

use ConnectHolland\Tactician\SchedulerPlugin\Command\ExecuteScheduledCommandsCommand;
use ConnectHolland\Tactician\SchedulerPlugin\Command\ScheduledCommandInterface;
use ConnectHolland\Tactician\SchedulerPlugin\Scheduler\SchedulerInterface;
use ConnectHolland\Tactician\SchedulerPlugin\Scheduler\StatefulAwareSchedulerInterface;
use Exception;
use League\Tactician\Middleware;

/**
 * Middleware to handle scheduled commands.
 *
 * @author Ron Rademaker
 */
class SchedulerMiddleware implements Middleware
{
    /**
     * The scheduler to use.
     *
     * @var SchedulerInterface
     */
    private $scheduler;

    /**
     * __construct.
     *
     * Creates the scheduler middleware using $scheduler for scheduling
     *
     * @since 1.0
     *
     * @param SchedulerInterface $scheduler
     */
    public function __construct(SchedulerInterface $scheduler)
    {
        $this->scheduler = $scheduler;
    }

    /**
     * execute.
     *
     * Schedule commands if needed, otherwise just pass on to the next middleware
     * Returns the id of the schedule command
     *
     * @since 1.0
     *
     * @api
     *
     * @param type     $command
     * @param callable $next
     */
    public function execute($command, callable $next)
    {
        if (($command instanceof ScheduledCommandInterface) && ($command->getTimestamp() > time())) {
            return $this->scheduler->schedule($command);
        } elseif ($command instanceof ExecuteScheduledCommandsCommand) {
            $commands = $this->scheduler->getCommands();
            foreach ($commands as $scheduledCommand) {
                try {
                    $command->getCommandBus()->handle($scheduledCommand);
                    if ($this->scheduler instanceof StatefulAwareSchedulerInterface) {
                        $this->scheduler->succeed($scheduledCommand);
                    }
                } catch (Exception $e) {
                    if ($this->scheduler instanceof StatefulAwareSchedulerInterface) {
                        $this->scheduler->fail($scheduledCommand);
                    }
                    throw $e;
                }
            }
        } else {
            return $next($command);
        }
    }
}
