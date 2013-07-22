<?php

namespace Sirian\Helpers\Process;

use Sirian\Helpers\Process\Event\ProcessEvent;
use Sirian\Helpers\Process\Provider\ProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Process\Process;

class Queue
{
    const EVENT_PROCESS_FINISHED = 'process_finished';
    const EVENT_PROCESS_STARTED = 'process_started';

    protected $concurrency;
    protected $processQueue;
    protected $eventDispatcher;
    protected $isStarted = false;

    /**
     * @var Process[]
     */
    protected $activeProcesses = [];

    /**
     * @var ProviderInterface
     */
    protected $provider;

    public function __construct($concurrency = null)
    {
        $this->setConcurrency($concurrency);
        $this->processQueue = new \SplPriorityQueue();
        $this->eventDispatcher = new EventDispatcher();
    }

    public function addProcess(Process $process, $priority = 0)
    {
        $this->processQueue->insert($process, $priority);
        $this->process();
        return $this;
    }

    public function addProcesses(array $processes, $priority = 0)
    {
        foreach ($processes as $process) {
            $this->addProcess($process, $priority);
        }
        return $this;
    }

    public function addListener($eventName, $listener, $priority = 0)
    {
        $this->eventDispatcher->addListener($eventName, $listener, $priority);
        return $this;
    }

    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    public function setEventDispatcher(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        return $this;
    }

    public function run()
    {
        if ($this->isStarted) {
            return;
        }

        $this->isStarted = true;

        while (!$this->isDrain()) {
            foreach ($this->activeProcesses as $key => $process) {
                if (!$process->isRunning()) {
                    $this->eventDispatcher->dispatch(self::EVENT_PROCESS_FINISHED, new ProcessEvent($process));
                    unset($this->activeProcesses[$key]);
                }
            }

            if (!$this->isSaturated()) {
                $this->process();
            }

            usleep(1000);
        }

        $this->isStarted = false;
    }

    public function process()
    {
        if (!$this->isStarted || $this->isSaturated()) {
            return;
        }

        if ($this->processQueue->isEmpty()) {
            if (null === $this->provider || $this->provider->isDrain()) {
                return;
            }

            $process = $this->provider->provide();
            if (!$process instanceof Process) {
                return;
            }

            $this->processQueue->insert($process, 0);
        }

        /**
         * @var Process $process
         */
        $process = $this->processQueue->extract();
        $process->start();
        $this->eventDispatcher->dispatch(self::EVENT_PROCESS_STARTED, new ProcessEvent($process));
        $this->activeProcesses[] = $process;
    }

    public function isSaturated()
    {
        return null !== $this->concurrency && count($this->activeProcesses) >= $this->concurrency;
    }

    public function isDrain()
    {
        return empty($this->activeProcesses)
            && $this->processQueue->isEmpty()
            && (null === $this->provider || $this->provider->isDrain())
        ;
    }

    public function setConcurrency($concurrency)
    {

        $this->concurrency = $concurrency ? max(1, $concurrency) : null;
        $this->process();
        return $this;
    }

    public function getConcurrency()
    {
        return $this->concurrency;
    }

    public function getProvider()
    {
        return $this->provider;
    }

    public function setProvider(ProviderInterface $provider)
    {
        $this->provider = $provider;
        return $this;
    }
}
