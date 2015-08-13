<?php

use ConnectHolland\Tactician\SchedulerPlugin\Middleware\SchedulerMiddleware;
use ConnectHolland\Tactician\SchedulerPlugin\Scheduler\FileBasedScheduler;
use ConnectHolland\Tactician\SchedulerPlugin\Tests\Fixtures\Command\ScheduledCommand;
use League\Tactician\CommandBus;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;
use League\Tactician\Handler\Locator\InMemoryLocator;
use League\Tactician\Handler\MethodNameInflector\HandleClassNameInflector;
use League\Tactician\Tests\Fixtures\Command\AddTaskCommand;
use League\Tactician\Tests\Fixtures\Handler\DynamicMethodsHandler;

/*
 * Fixture bootstrap to create a command bus
 */
chdir(__DIR__.'/../../');

$methodHandler = new DynamicMethodsHandler();
$handlerMiddleware = new CommandHandlerMiddleware(
    new ClassNameExtractor(),
    new InMemoryLocator([
        AddTaskCommand::class => $methodHandler,
        ScheduledCommand::class => $methodHandler,
    ]),
    new HandleClassNameInflector()
);

$scheduler = new FileBasedScheduler('tests/schedulerpath');

$schedulerMiddleware = new SchedulerMiddleware($scheduler);

return new CommandBus([$schedulerMiddleware, $handlerMiddleware]);
