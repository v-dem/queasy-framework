<?php

namespace queasy\framework\container;

interface ServiceContainerInterface
{
    public function has($service);

    public function get($service);
}

