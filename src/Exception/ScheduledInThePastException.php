<?php

namespace ConnectHolland\Tactician\SchedulerPlugin\Exception;

use Exception;

/**
 * Exception to throw when attempting to schedule commands in the past.
 *
 * @author ron
 */
class ScheduledInThePastException extends Exception
{
}
