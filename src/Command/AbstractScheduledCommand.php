<?php

namespace ConnectHolland\Tactician\SchedulerPlugin\Command;

use ConnectHolland\Tactician\SchedulerPlugin\Exception\ScheduledInThePastException;

/**
 * Base implementation of a scheduled command.
 *
 * @author Ron Rademaker
 */
class AbstractScheduledCommand implements ScheduledCommandInterface
{
    /**
     * The time to execute at.
     */
    private $timestamp;

    /**
     * setTimestamp.
     *
     * Sets the timestamp when this command should be execute
     *
     * @since 1.0
     *
     * @param int $timestamp
     */
    public function setTimestamp($timestamp)
    {
        if ($timestamp < time()) {
            throw new ScheduledInThePastException('Scheduling commands in the past is not allowed');
        }

        $this->timestamp = $timestamp;
    }

    /**
     * getTimestamp.
     *
     * SGts the timestamp when this command should be executed
     *
     * @since 1.0
     *
     * @return int
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }
}
