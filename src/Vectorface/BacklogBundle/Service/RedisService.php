<?php

namespace Vectorface\BacklogBundle\Service;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Vectorface\BacklogBundle\AbstractContainerAware;

class RedisService extends AbstractContainerAware
{
    private $redis;

    public function __construct()
    {
        $this->redis = null;
    }

    public function getRedis()
    {
        if (is_null($this->redis) === false)
        {
            return $this->redis;
        }

        // misc
        $parameters = $this->getContainer()->getParameter("redis");

        // connecting to redis
        $this->redis = new \Redis();
        $this->redis->connect($parameters["host"], $parameters["port"]);
        $this->redis->select($parameters["db"]);
        $this->redis->setOption(\Redis::OPT_PREFIX, $parameters["prefix"]);

        return $this->redis;
    }
}