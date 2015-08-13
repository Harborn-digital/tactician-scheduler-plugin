# tactician-prioritycommand-plugin
Tactician plugin that allows scheduling a command to be executed at a specific time in the future

[![Build Status](https://travis-ci.org/RonRademaker/tactician-scheduler-plugin.svg?branch=master)](https://travis-ci.org/RonRademaker/tactician-scheduler-plugin)
[![Coverage Status](https://coveralls.io/repos/RonRademaker/tactician-scheduler-plugin/badge.svg?branch=master&service=github)](https://coveralls.io/github/RonRademaker/tactician-scheduler-plugin?branch=master)

# Concept
This plugin allows you to create ScheduledCommands that will be executed at a specific time in the future.

# Usage
Make sure you put the SchedulerMiddleware in your CommandBus middleware chain:

```
// create your other middleware
$middleware[] = new FileBasedScheduler($pathWhereTheSchedulerMayKeepItsFiles);
// create your other middleware
$commandbus = new CommandBus($middleware);
```

Let the command you want to schedule extend from AbstractScheduledCommand or implement the ScheduledCommandInterface. Create it and set a execution time:

```
class SayHappyNewYear extends AbstractScheduledCommand
{
    private $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function getMessage() {
        return $this->message;
    }
}

$myScheduledCommand = new SayHappyNewYear('Happy New Year');
$myScheduledCommand->setTimestamp(strtotime'2016-01-01 0:00:00') );
$myCommandBus->handle($myScheduledCommand);
```

Create a bootstrap file that builds your Commandbus and cron the schedule execution command, for example bootstrap.php

```
// setup any environment you need
// create your other middleware
$middleware[] = new SchedulerMiddleware(new FileBasedScheduler($pathWhereTheSchedulerMayKeepItsFiles) );
// create your other middleware
$commandbus = new CommandBus($middleware);
return $commandbus;
```

Cron the scheduler at any interval you like (the more it runs, the better you can time your commands), example for once a minute

```
* * * * *   www-data    vendor/bin/scheduler scheduler:execute bootstrap.php
```