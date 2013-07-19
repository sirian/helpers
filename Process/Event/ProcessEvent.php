<?php

namespace Sirian\Helpers\Process\Event;

use Symfony\Component\Process\Process;

class ProcessEvent extends Event
{
    protected $process;

    public function __construct(Process $process)
    {
        $this->process = $process;
    }

    public function getProcess()
    {
        return $this->process;
    }
}
