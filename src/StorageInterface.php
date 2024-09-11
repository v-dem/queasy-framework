<?php

namespace queasy\framework;

interface StorageInterface
{
    /**
     * Store data.
     *
     * @param mixed $data Data to store
     * @param int|string $key Optional. Unique key
     *
     * @return int|string Unique key
     */
    public function store($data, $key = null);

    /**
     * Read data.
     *
     * @param int|string $key Optional. Unique key. If not specified all records should be returned
     *
     * @return mixed Data
     */
    public function read($key = null);

    /**
     * Remove data.
     *
     * @param int|string $key Unique key
     *
     * @return bool Success flag
     */
    public function remove($key);
}

