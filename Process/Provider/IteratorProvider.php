<?php

namespace Sirian\Helpers\Process\Provider;

class IteratorProvider implements ProviderInterface
{
    private $iterator;

    public function __construct(\Iterator $iterator)
    {
        $this->iterator = $iterator;
        $this->iterator->rewind();
    }

    public function provide()
    {
        $process = $this->iterator->current();
        $this->iterator->next();
        return $process;
    }

    public function isDrain()
    {
        return $this->iterator->valid();
    }
}
