<?php
namespace ConnectHolland\Tactician\SchedulerPlugin\Scheduler;

use ConnectHolland\Tactician\SchedulerPlugin\Command\ScheduledCommandInterface;

/**
 * Interface that defines schedulers that are aware of possible stateful commands
 *
 * @author Ron Rademaker
 */
interface StatefulAwareSchedulerInterface extends SchedulerInterface
{
    /**
     * Marks the command as succeeded (if a stateful command)
     *
     * @param ScheduledCommandInterface $command
     */
    public function succeed(ScheduledCommandInterface $command);

    /**
     * Marks the command as failed (if a stateful command)
     *
     * @param ScheduledCommandInterface $command
     */
    public function fail(ScheduledCommandInterface $command);
}
