<?php

namespace Vectorface\BacklogBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Vectorface\BacklogBundle;
use There4\FogBugz;

class FogbugzCommand extends BacklogBundle\AbstractCommand
{

    protected function configure()
    {
        $this
        ->setName('backlog:fogbugz')
        ->setDescription('Backlog Fogbugz')
        ->addOption(
            'push',
            null,
            InputOption::VALUE_NONE,
            'Will push the backlog order to Fogbugz'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $container = $this->getContainer();
        $this->fogbugz = $container->get("FogbugzService");
        $this->fogbugz->logon();

        $this->fogbugz->pullUsers();
        $this->fogbugz->pullTickets();
        $this->fogbugz->removeClosedTickets();

        if ($input->getOption('push')) {
            $this->fogbugz->pushBacklog();
        }

    }

}
