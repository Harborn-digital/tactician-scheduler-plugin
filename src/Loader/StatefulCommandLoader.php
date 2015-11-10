<?php
namespace ConnectHolland\Tactician\SchedulerPlugin\Loader;

use ConnectHolland\Tactician\SchedulerPlugin\Command\StatefulCommandInterface;
use Finite\Loader\LoaderInterface;
use Finite\State\State;
use Finite\State\StateInterface;
use Finite\StatefulInterface;
use Finite\StateMachine\StateMachineInterface;
use Finite\Transition\Transition;

/**
 * Loader to defines states, transitions and properties for stateful commands
 *
 * @author Ron Rademaker
 */
class StatefulCommandLoader implements LoaderInterface
{
    public function load(StateMachineInterface $stateMachine)
    {
       $stateMachine->addState(new State('new', StateInterface::TYPE_INITIAL));
       $stateMachine->addState(new State('scheduled', StateInterface::TYPE_NORMAL));
       $stateMachine->addState(new State('executing', StateInterface::TYPE_NORMAL));
       $stateMachine->addState(new State('failed', StateInterface::TYPE_FINAL));
       $stateMachine->addState(new State('succeeded', StateInterface::TYPE_FINAL));

       $stateMachine->addTransition(new Transition('schedule', 'new', 'scheduled'));
       $stateMachine->addTransition(new Transition('execute', 'scheduled', 'executing'));
       $stateMachine->addTransition(new Transition('fail', 'executing', 'failed'));
       $stateMachine->addTransition(new Transition('succeed', 'executing', 'succeeded'));
    }

    /**
     * Returns true if object is a stateful command
     *
     * @param StatefulInterface $object
     * @return boolean
     */
    public function supports(StatefulInterface $object)
    {
        return $object instanceof StatefulCommandInterface;
    }
}
