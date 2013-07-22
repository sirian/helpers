<?php

namespace Sirian\Helpers\Process\Provider;

use Symfony\Component\Process\Process;

class CallbackProvider implements ProviderInterface
{
    protected $callback;
    protected $drain = false;

    private $nextProcess;

    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    public function provide()
    {
        $process = $this->getNextProcess();
        $this->nextProcess = null;
        return $process;
    }

    public function isDrain()
    {
        if ($this->drain) {
            return true;
        }

        $this->drain = null === $this->getNextProcess();
        return $this->drain;
    }

    private function getNextProcess()
    {
        if (null !== $this->nextProcess) {
            return $this->nextProcess;
        }

        $process = call_user_func($this->callback);
        $this->nextProcess = $process instanceof Process ? $process : null;
        return $this->nextProcess;
    }
}
