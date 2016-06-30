<?php

namespace ConnectHolland\Tactician\SchedulerPlugin\Command;

/**
 * Interface of scheduled commands.
 *
 * @author Ron Rademaker
 */
interface ScheduledCommandInterface
{
    /**
     * setTimestamp.
     *
     * Sets the timestamp when this command should be executed
     *
     * @since 1.0
     *
     * @param int $timestamp
     */
    public function setTimestamp($timestamp);

    /**
     * getTimestamp.
     *
     * Gets the timestamp when this command should be executed
     *
     * @since 1.0
     *
     * @return int
     */
    public function getTimestamp();
}
