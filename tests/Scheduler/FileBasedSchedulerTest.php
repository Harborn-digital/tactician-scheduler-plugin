<?php

namespace ConnectHolland\Tactician\SchedulerPlugin\Scheduler\Tests;

use ConnectHolland\Tactician\SchedulerPlugin\Exception\ScheduledCommandNotFoundException;
use ConnectHolland\Tactician\SchedulerPlugin\Scheduler\FileBasedScheduler;
use ConnectHolland\Tactician\SchedulerPlugin\Tests\AbstractFileBasedSchedulerTest;
use ConnectHolland\Tactician\SchedulerPlugin\Tests\Fixtures\Command\ScheduledCommand;

/**
 * Unit test for the file based scheduler.
 *
 * @author ron
 */
class FileBasedSchedulerTest extends AbstractFileBasedSchedulerTest
{
    /**
     * testCreatesSchedulePath.
     */
    public function testCreatesSchedulePath()
    {
        $this->assertFalse(is_dir('tests/testpath'));
        new FileBasedScheduler('tests/testpath');
        $this->assertTrue(is_dir('tests/testpath'));
        rmdir('tests/testpath');
    }

    /**
     * testMissingScheduledCommandThrowsException.
     */
    public function testMissingScheduledCommandThrowsException()
    {
        $scheduler = new FileBasedScheduler($this->path);
        $command = new ScheduledCommand();
        $command->setTimestamp(time() + 1);
        $id = $scheduler->schedule($command);
        $this->assertFileExists($this->path.$id);
        unlink($this->path.$id);
        sleep(1);
        $this->setExpectedException(ScheduledCommandNotFoundException::class);
        $scheduler->getCommands();
    }
}
