<?php
namespace ConnectHolland\Tactician\SchedulerPlugin\Command;

use Finite\StatefulInterface;

/**
 * Stateful command to keep track of failed / succeeded scheduled commands
 *
 * @author Ron Rademaker
 */
class AbstractStatefulScheduledCommand extends AbstractScheduledCommand implements StatefulInterface
{
    /**
     * The current state of the command
     *
     * @var string
     */
    private $state;

    /**
     * Gets the current state
     *
     * @return string
     */
    public function getFiniteState()
    {
        return $this->state;
    }

    /**
     * Sets the state
     *
     * @param string $state
     */
    public function setFiniteState($state)
    {
       $this->state = $state;
    }
}
