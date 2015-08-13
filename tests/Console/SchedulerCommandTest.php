<?php

namespace ConnectHolland\Tactician\SchedulerPlugin\Console\Tests;

use ConnectHolland\Tactician\SchedulerPlugin\Console\SchedulerCommand;
use ConnectHolland\Tactician\SchedulerPlugin\Middleware\SchedulerMiddleware;
use ConnectHolland\Tactician\SchedulerPlugin\Scheduler\FileBasedScheduler;
use ConnectHolland\Tactician\SchedulerPlugin\Tests\AbstractFileBasedSchedulerTest;
use ConnectHolland\Tactician\SchedulerPlugin\Tests\Fixtures\Command\ScheduledCommand;
use League\Tactician\CommandBus;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;
use League\Tactician\Handler\Locator\InMemoryLocator;
use League\Tactician\Handler\MethodNameInflector\HandleClassNameInflector;
use League\Tactician\Tests\Fixtures\Command\AddTaskCommand;
use League\Tactician\Tests\Fixtures\Handler\DynamicMethodsHandler;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit test for the console command to execute scheduled commands.
 *
 * @author ron
 */
class SchedulerCommandTest extends AbstractFileBasedSchedulerTest
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
            ]),
            new HandleClassNameInflector()
        );

        $scheduler = new FileBasedScheduler($this->path);

        $schedulerMiddleware = new SchedulerMiddleware($scheduler);

        $this->commandBus = new CommandBus([$schedulerMiddleware, $handlerMiddleware]);
    }

    /**
     * testExecuteScheduledCommand.
     */
    public function testExecuteScheduledCommand()
    {
        // schedule a command
        $command = new ScheduledCommand();
        $command->setTimestamp(time() + 2);
        $id = $this->commandBus->handle($command);

        $this->assertFileExists($this->path.$id);

        // does nothing yet
        $application = new Application();
        $application->add(new SchedulerCommand());

        $consoleCommand = $application->find('scheduler:execute');
        $commandTester = new CommandTester($consoleCommand);
        $commandTester->execute(array(
            'command' => $consoleCommand->getName(),
            'bootstrap' => 'tests/Fixtures/bootstrap.php',
        ));

        $this->assertFileExists($this->path.$id);
        sleep(2);

        $commandTester->execute(array(
            'command' => $consoleCommand->getName(),
            'bootstrap' => 'tests/Fixtures/bootstrap.php',
        ));

        $this->assertFileNotExists($this->path.$id);
    }

        /**
     * testInvalidBootstrapShowsMessage.
     */
    public function testInvalidBootstrapThrowsException()
    {
        $application = new Application();
        $application->add(new SchedulerCommand());

        $consoleCommand = $application->find('scheduler:execute');
        $commandTester = new CommandTester($consoleCommand);
        $commandTester->execute(array(
            'command' => $consoleCommand->getName(),
            'bootstrap' => 'tests/Fixtures/invalid_bootstrap.php',
        ));

        $this->assertRegExp('/Bootstrap did not return a proper CommandBus/', $commandTester->getDisplay());
    }
}
