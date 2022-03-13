<?php

namespace queasy\framework\container;

interface ContainerInterface
{
    public function has($service);

    public function get($service);
}

