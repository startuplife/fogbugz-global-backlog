<?php

namespace Vectorface\BacklogBundle;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractContainerAware implements ContainerAwareInterface
{
	private $container;

	public function setContainer(ContainerInterface $container = null)
	{
		$this->container = $container;
	}

	public function getContainer()
	{
		return $this->container;
	}
}