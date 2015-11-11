<?php

namespace ConnectHolland\Tactician\SchedulerPlugin\Middleware\Tests;

use ConnectHolland\Tactician\SchedulerPlugin\Command\ExecuteScheduledCommandsCommand;
use ConnectHolland\Tactician\SchedulerPlugin\Middleware\SchedulerMiddleware;
use ConnectHolland\Tactician\SchedulerPlugin\Scheduler\MongoScheduler;
use ConnectHolland\Tactician\SchedulerPlugin\Tests\AbstractFileBasedSchedulerTest;
use ConnectHolland\Tactician\SchedulerPlugin\Tests\Fixtures\Command\ScheduledCommand;
use ConnectHolland\Tactician\SchedulerPlugin\Tests\Fixtures\Command\StatefulCommand;
use League\Tactician\CommandBus;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;
use League\Tactician\Handler\Locator\InMemoryLocator;
use League\Tactician\Handler\MethodNameInflector\HandleClassNameInflector;
use League\Tactician\Tests\Fixtures\Command\AddTaskCommand;
use League\Tactician\Tests\Fixtures\Handler\DynamicMethodsHandler;
use MongoClient;

/**
 * Unit test for the scheduler middleware.
 *
 * @author ron
 */
class SchedulerMiddlewareTest extends AbstractFileBasedSchedulerTest
{
    /**
     * Command Bus for testing.
     */
    private $commandBus;

    /**
     * DynamicMethodsHander.
     */
    private $methodHandler;

    /**
     * Mongo cllection with the commands
     */
    private $collection;

    /**
     * Creates a command bus to use for testing.
     */
    public function setUp()
    {
        parent::setUp();

        $this->methodHandler = new DynamicMethodsHandler();
        $handlerMiddleware = new CommandHandlerMiddleware(
            new ClassNameExtractor(),
            new InMemoryLocator([
                AddTaskCommand::class => $this->methodHandler,
                ScheduledCommand::class => $this->methodHandler,
                StatefulCommand::class => $this->methodHandler
            ]),
            new HandleClassNameInflector()
        );

        $con = new MongoClient('mongodb://localhost');
        $db = $con->selectDB('ConnectHollandTacticianSchedulerTest');
        $this->collection = $db->selectCollection('MongoScheduler');

        $scheduler = new MongoScheduler($this->collection);

        $schedulerMiddleware = new SchedulerMiddleware($scheduler);

        $this->commandBus = new CommandBus([$schedulerMiddleware, $handlerMiddleware]);
    }

    /**
     * Drop any leftover test commands
     */
    public function tearDown()
    {
        $con = new MongoClient('mongodb://localhost');
        $db = $con->selectDB('ConnectHollandTacticianSchedulerTest');
        $db->dropCollection('MongoScheduler');
    }

    /**
     * Tests if regular commands (no implementations of PriorityCommandInterface are executed).
     **/
    public function testRegularCommandIsExecuted()
    {
        $command = new AddTaskCommand();
        $this->commandBus->handle($command);

        $this->assertContains('handleAddTaskCommand', $this->methodHandler->getMethodsInvoked());
    }

    /**
     * Tests if scheduled command is executed when scheduled.
     **/
    public function testSchedulingCommand()
    {
        $command = new ScheduledCommand();
        $command->setTimestamp(time() + 1);
        $this->commandBus->handle($command);

        $this->assertNotContains('handleScheduledCommand', $this->methodHandler->getMethodsInvoked());
        sleep(1);
        $this->commandBus->handle(new ExecuteScheduledCommandsCommand($this->commandBus));
        $this->assertContains('handleScheduledCommand', $this->methodHandler->getMethodsInvoked());
    }

    /**
     * Tests if scheduled in the past gives an exception.
     **/
    public function testSchedulingCommandInThePast()
    {
        $this->setExpectedException('ConnectHolland\Tactician\SchedulerPlugin\Exception\ScheduledInThePastException');
        $command = new ScheduledCommand();
        $command->setTimestamp(time() - 1);
        $this->commandBus->handle($command);
    }

    /**
     * Tests if a stateful command is executed and marked succesful
     */
    public function testStatefulCommand()
    {
        $command = new StatefulCommand();
        $command->setTimestamp(time() + 1);
        $this->commandBus->handle($command);

        $this->assertNotContains('handleStatefulCommand', $this->methodHandler->getMethodsInvoked());
        sleep(1);
        $this->commandBus->handle(new ExecuteScheduledCommandsCommand($this->commandBus));
        $this->assertContains('handleStatefulCommand', $this->methodHandler->getMethodsInvoked());

        $resultCommand = $this->collection->findOne();        
        $this->assertEquals('succeeded', $resultCommand['state']);
    }
}
