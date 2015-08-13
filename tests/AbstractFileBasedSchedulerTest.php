<?php
namespace ConnectHolland\Tactician\SchedulerPlugin\Tests;

use PHPUnit_Framework_TestCase;

/**
 * Base class for file base scheduler tests
 *
 * @author ron
 */
abstract class AbstractFileBasedSchedulerTest extends PHPUnit_Framework_TestCase
{
    protected $path = 'tests/schedulerpath/';

    public function setUp()
    {
        $this->cleanSchedulerPath();
    }

    public function tearDown()
    {
        $this->cleanSchedulerPath();
    }

    private function cleanSchedulerPath()
    {
        $files = scandir($this->path);

        foreach ($files as $file) {
            if (is_file($this->path.$file)) {
                unlink($this->path.$file);
            }
        }
    }
}
