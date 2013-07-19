<?php

namespace Sirian\Helpers\Process\Provider;

interface ProviderInterface
{
    public function provide();
    public function isDrain();
}
