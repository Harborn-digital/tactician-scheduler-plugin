<?php
namespace ConnectHolland\Tactician\SchedulerPlugin\Factory;

use Finite\Factory\AbstractFactory;
use Finite\StateMachine\StateMachine;

/**
 * Factory to create statemachines from a stateful command
 *
 * @author Ron Rademaker
 */
class StatefulCommandFactory extends AbstractFactory
{
    /**
     * Initializes the loader for the stateful commands
     */
    public function __construct()
    {
        $this->addLoader(new StatefulCommandLoader());
    }

        /**
     * Creates a new empty statemachine
     *
     * @since 1.2
     * @return StateMachine
     */
    protected function createStateMachine()
    {
        return new StateMachine();
    }
}
