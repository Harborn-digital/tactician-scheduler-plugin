<?php

namespace ConnectHolland\Tactician\SchedulerPlugin\Scheduler;

use ConnectHolland\Tactician\SchedulerPlugin\Command\ScheduledCommandInterface;
use ConnectHolland\Tactician\SchedulerPlugin\Exception\ScheduledCommandNotFoundException;
use Rhumsaa\Uuid\Uuid;

/**
 * File based scheduler that uses a file for each scheduled command (the serialized command will be stored in the file).
 *
 * @author ron
 */
class FileBasedScheduler implements SchedulerInterface
{
    /**
     * The path to save commands in.
     */
    private $path;

    /**
     * Schedule register, array keeping timestamps and id's of commands to execute at that time     *.
     */
    private $register = [];

    /**
     * __construct.
     *
     * Creates a file based scheduler
     *
     * @since 1.0
     *
     * @param string $path
     */
    public function __construct($path)
    {
        if (!is_dir($path)) {
            mkdir($path);
        }
        $this->path = $path;

        if (file_exists($path.DIRECTORY_SEPARATOR.'register')) {
            $this->register = unserialize(file_get_contents($path.DIRECTORY_SEPARATOR.'register'));
        }
    }

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
    public function schedule(ScheduledCommandInterface $command, $id = null)
    {
        $fileName = isset($id) ? $id : Uuid::uuid1()->toString();

        file_put_contents($this->path.DIRECTORY_SEPARATOR.$fileName, serialize($command));
        $this->register[$fileName] = $command->getTimestamp();
        file_put_contents($this->path.DIRECTORY_SEPARATOR.'register', serialize($this->register));

        return $fileName;
    }

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
    public function getCommands()
    {
        $commands = [];
        $now = mktime();
        foreach ($this->register as $commandId => $timestamp) {
            if ($timestamp <= $now) {
                $commands[] = $this->getFromScheduler($commandId);
                unset($this->register[$commandId]);
            }
        }
        file_put_contents($this->path.DIRECTORY_SEPARATOR.'register', serialize($this->register));

        return $commands;
    }

    /**
     * getFromScheduler.
     *
     * Gets the command with $id from the scheduler and removes its file
     *
     * @param string $id
     *
     * @return ScheduledCommandInterface
     */
    private function getFromScheduler($id)
    {
        $filename = $this->path.DIRECTORY_SEPARATOR.$id;
        if (!file_exists($filename)) {
            throw new ScheduledCommandNotFoundException("Command '{$id}' not found");
        }

        $command = unserialize(file_get_contents($filename));
        unlink($filename);

        return $command;
    }
}
