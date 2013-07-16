<?php

namespace Vectorface\BacklogBundle;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Vectorface\BacklogBundle\AbstractContainerAware;

class RedisHelper extends AbstractContainerAware
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
		$redis = new \Redis();
		$redis->connect($parameters["host"], $parameters["port"]);
		$redis->select($parameters["db"]);
		$this->redis = $redis;

		return $this->redis;
	}
}