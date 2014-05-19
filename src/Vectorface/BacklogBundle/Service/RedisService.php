<?php

namespace Vectorface\BacklogBundle\Service;

class RedisService
{
    private $redis;
    private $config;

    /**
     * @param array $config Configuration for host, port, db, prefix
     */
    public function __construct()
    {
        $this->redis = null;
    }

    public function setConfig($config = array())
    {
        $this->config = $config;
    }

    public function connect()
    {
        if (is_null($this->redis) === false)
        {
            return $this->redis;
        }

        $this->redis = new \Redis();
        $this->redis->connect($this->config["host"], $this->config["port"]);
        $this->redis->select($this->config["db"]);
        $this->redis->setOption(\Redis::OPT_PREFIX, $this->config["prefix"]);
        return $this->redis;
    }

}