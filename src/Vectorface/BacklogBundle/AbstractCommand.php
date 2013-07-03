<?php

namespace Vectorface\BacklogBundle;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class AbstractCommand extends ContainerAwareCommand
{

    public function __construct()
    {
        parent::__construct();
    }

    public function log($message, $type = "debug")
    {
        $logger = $this->getContainer()->get('logger');
        if(array_key_exists(strtoupper($type), $logger->getLevels())) {
            $logger->$type($message);
        } else {
            $logger->debug('Unknown logging type');
        }
    }
}