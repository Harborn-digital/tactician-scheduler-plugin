<?php

namespace ConnectHolland\Tactician\SchedulerPlugin\Command;

use League\Tactician\CommandBus;

/**
 * Command to put on the bus to execute all scheduled commands.
 *
 * @author ron
 */
class ExecuteScheduledCommandsCommand
{
    /**
     * The bus to put the commands on.
     */
    private $commandBus;

    /**
     * __construct.
     *
     * Creates the command
     *
     * @since 1.0
     *
     * @param CommandBus $commandBus
     */
    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * Gets the command bus.
     */
    public function getCommandBus()
    {
        return $this->commandBus;
    }
}
