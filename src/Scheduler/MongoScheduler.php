<?php
namespace ConnectHolland\Tactician\SchedulerPlugin\Scheduler;

use ConnectHolland\Tactician\SchedulerPlugin\Command\ScheduledCommandInterface;
use MongoCollection;
use MongoDate;
use MongoDB;
use MongoId;

/**
 * Scheduler that uses a mongo database to store the commands
 *
 * @author Ron Rademaer
 */
class MongoScheduler implements SchedulerInterface
{
    /**
     * Collection to store in
     *
     * @var MongoCollection
     */
    private $collection;

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
            $commands[] = unserialize($storedCommand['command']);
            $this->collection->remove(['_id' => $storedCommand['_id']]);
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
        $data = [
            '_id' => $mongoId,
            'command' => serialize($command),
            'timestamp' => new MongoDate($command->getTimestamp())
        ];
        $this->collection->save($data);

        return $mongoId->__toString();
    }
}
