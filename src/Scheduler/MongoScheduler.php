<?php
namespace ConnectHolland\Tactician\SchedulerPlugin\Scheduler;

use ConnectHolland\Tactician\SchedulerPlugin\Command\ScheduledCommandInterface;
use ConnectHolland\Tactician\SchedulerPlugin\Command\StatefulCommandInterface;
use ConnectHolland\Tactician\SchedulerPlugin\Factory\StatefulCommandFactory;
use MongoCollection;
use MongoDate;
use MongoDB;
use MongoId;

/**
 * Scheduler that uses a mongo database to store the commands
 *
 * @author Ron Rademaker
 */
class MongoScheduler implements StatefulAwareSchedulerInterface
{
    /**
     * Collection to store in
     *
     * @var MongoCollection
     */
    private $collection;

    /**
     * Factory to create statemachines for stateful commands
     *
     * @var StatefulCommandFactory
     */
    private $factory;

    /**
     * Local storage of mongo ids
     *
     * @var array
     */
    private $commandIdStorage = [];

    /**
     * Creates a new MongoScheduler
     *
     * @access public
     * @since 1.1
     * @param MongoDB $mongoDB
     * @param string $collection
     * @return void
     */
    public function __construct(MongoCollection $collection)
    {
        $this->collection = $collection;
        $this->factory = new StatefulCommandFactory();
    }

    /**
     * Gets the commands that should be executed and removes them from the database
     *
     * @access public
     * @since 1.1
     * @return array
     */
    public function getCommands()
    {
        $query = [
            'timestamp' => ['$lt' => new MongoDate()]
        ];

        $storedCommands = $this->collection->find($query);
        $commands = [];
        foreach ($storedCommands as $storedCommand) {
            $command = unserialize($storedCommand['command']);
            $this->commandIdStorage[spl_object_hash($command)] = $storedCommand['_id'];
            $this->collection->remove(['_id' => $storedCommand['_id']]);
            $commands[] = $command;

            if ($command instanceof StatefulCommandInterface) {
                $this->applyTransition($command, 'execute');
            }
        }

        return $commands;
    }

    /**
     * Schedules $command to be executed at its set time
     *
     * @param ScheduledCommandInterface $command
     * @param type $id
     * @return $id
     */
    public function schedule(ScheduledCommandInterface $command, $id = null)
    {
        $mongoId = isset($id) ? new MongoId($id) : new MongoId();
        $this->commandIdStorage[spl_object_hash($command)] = $mongoId;
        if ($command instanceof StatefulCommandInterface) {
            $this->applyTransition($command, 'schedule');
        } else {
            $this->store($command);
        }

        return $mongoId->__toString();
    }

    public function fail(ScheduledCommandInterface $command)
    {
        if ($command instanceof StatefulCommandInterface) {
            $this->applyTransition($command, 'fail');
        }
    }

    public function succeed(ScheduledCommandInterface $command)
    {
        if ($command instanceof StatefulCommandInterface) {
            $this->applyTransition($command, 'succeed');
        }
    }

    /**
     * Saves $command to the mongo database
     *
     * @param ScheduledCommandInterface $command
     * @param MongoId $id
     */
    private function store(ScheduledCommandInterface $command)
    {
        $id = $this->commandIdStorage[spl_object_hash($command)];
        $data = [
            '_id' => $id,
            'command' => serialize($command)
        ];

        if ($command instanceof StatefulCommandInterface) {
            $data['state'] = $command->getFiniteState();
            if ($data['state'] === 'scheduled') {
                $data['timestamp'] = new MongoDate($command->getTimestamp());
            } else {
                $data['timestamp_scheduled'] = new MongoDate($command->getTimestamp());
            }
        } else {
            $data['timestamp'] = new MongoDate($command->getTimestamp());
        }

        $this->collection->save($data);
    }

    /**
     * Applies $transition to $command and saves it to the mongo database
     *
     * @param StatefulCommandInterface $command
     * @param string $transition
     */
    private function applyTransition(StatefulCommandInterface $command, $transition)
    {
        $stateMachine = $this->factory->get($command);
        $stateMachine->apply($transition);
        $this->store($stateMachine->getObject());
    }
}
