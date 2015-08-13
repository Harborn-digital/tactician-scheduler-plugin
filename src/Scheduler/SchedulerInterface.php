<?php

namespace ConnectHolland\Tactician\SchedulerPlugin\Scheduler;

use ConnectHolland\Tactician\SchedulerPlugin\Command\ScheduledCommandInterface;

/**
 * Interface defining command schedulers.
 *
 * @author ron
 */
interface SchedulerInterface
{
    /**
     * schedule.
     *
     * Schedule $command for later execution
     * Returns a unique identifier for the scheduled execution
     * If an $id was passed it'll overwrite the scheduled execution
     *
     * @since 1.0
     *
     * @param ScheduledCommandInterface $command
     * @param string                    $id      - optional
     *
     * @return string
     */
    public function schedule(ScheduledCommandInterface $command, $id = null);

    /**
     * getCommands.
     *
     * Gets commands that should be executed
     * Note: this removes the commands from the scheduler
     *
     * @since 1.0
     *
     * @return array
     */
    public function getCommands();
}
