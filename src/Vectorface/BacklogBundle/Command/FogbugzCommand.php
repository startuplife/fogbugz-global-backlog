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

    public function __construct()
    {
        parent::__construct();

        //Redis Connection
        $this->redis = new \Redis();
        $this->redis->connect('127.0.0.1');
    }

    protected function configure()
    {
        $this
            ->setName('backlog:fogbugz:populate')
            ->setDescription('Backlog Fogbugz')
            ->addArgument(
                'range',
                InputArgument::OPTIONAL,
                'What range are we populating the missing data from?'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $container = $this->getContainer();
        $this->fogbugz = new FogBugz\Api(
            $container->getParameter('fogbugz_user'),
            $container->getParameter('fogbugz_password'),
            $container->getParameter('fogbugz_url')
        );

        $this->fogbugz->logon();
        $this->populate();

    }

    public function populate()
    {
        $xml = $this->fogbugz->search(array('q' => 'status:"open"', 'cols' => 'ixBug,sTitle,ixProject,ixFixFor,sEmailAssignedTo,sPersonAssignedTo,ixPersonAssignedTo'));


        foreach($xml->children() as $tickets){
            foreach($tickets->children() as $ticket){
                $data = array(
                    'sTitle' => (string)$ticket->sTitle,
                    'ixProject' => (string)$ticket->ixProject,
                    'ixFixFor' => (string)$ticket->ixFixFor,
                    'sEmailAssignedTo' => (string)$ticket->sEmailAssignedTo,
                    'sPersonAssignedTo' => (string)$ticket->sPersonAssignedTo,
                    'ixPersonAssignedTo' => (string)$ticket->ixPersonAssignedTo
                    );
                $this->redis->hMset('VectorfaceBacklog:ticket:'.(string)$ticket->ixBug, $data);
                $this->redis->zAdd('VectorfaceBacklog:tickets', (string)$ticket->ixBug, (string)$ticket->sTitle);
            }
        }
    }

}
