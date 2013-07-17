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
    private $redis;

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
        $this->redis = $container->get("redishelper")->getRedis();
        $this->fogbugz = new FogBugz\Api(
            $container->getParameter('fogbugz_user'),
            $container->getParameter('fogbugz_password'),
            $container->getParameter('fogbugz_url')
            );

        $this->fogbugz->logon();
        $this->populate();
        $this->removeClosedTickets();

        if ($input->getOption('push')) {
            $this->pushBacklog();
        }

    }

    public function populate()
    {
        $xml = $this->fogbugz->search(array('q' => 'status:"active" OR status:"open"', 'cols' => 'ixBug,sCategory,sTitle,sProject,ixProject,ixFixFor,sFixFor,sEmailAssignedTo,sPersonAssignedTo,ixPersonAssignedTo'));
        $this->redis->del('VectorfaceBacklog:tickets');
        foreach($xml->children() as $tickets){
            foreach($tickets->children() as $ticket){
                $data = array(
                    'ixBug' => (string)$ticket->ixBug,
                    'sCategory' => (string)$ticket->sCategory,
                    'sTitle' => (string)$ticket->sTitle,
                    'ixProject' => (string)$ticket->ixProject,
                    'sProject' => (string)$ticket->sProject,
                    'ixFixFor' => (string)$ticket->ixFixFor,
                    'sFixFor' => (string)$ticket->sFixFor,
                    'sEmailAssignedTo' => (string)$ticket->sEmailAssignedTo,
                    'sPersonAssignedTo' => (string)$ticket->sPersonAssignedTo,
                    'ixPersonAssignedTo' => (string)$ticket->ixPersonAssignedTo
                    );
                $this->redis->hMset('VectorfaceBacklog:ticket:'.(string)$ticket->ixBug, $data);
                $this->redis->zAdd('VectorfaceBacklog:tickets', (string)$ticket->ixBug, (string)$ticket->sTitle);
            }
        }
        return $xml->cases->attributes()->count;
    }

    public function removeClosedTickets()
    {
        $this->redis->zInter('VectorfaceBacklog:listOfBacklogs', array('VectorfaceBacklog:tickets', 'VectorfaceBacklog:listOfBacklogs'), array(1, 0));
        $backLogs = $this->redis->lRange("VectorfaceBacklog:rankOfBacklogs", 0, -1);
        foreach($backLogs as $backlog) {
            $exists = $this->redis->ZRANGEBYSCORE('VectorfaceBacklog:listOfBacklogs', $backlog, $backlog);
            if(empty($exists)) {
                $this->redis->lRem('VectorfaceBacklog:rankOfBacklogs', $backlog);
            }

        }
    }

    public function pushBacklog()
    {
        $backlogs = $this->redis->lRange("VectorfaceBacklog:rankOfBacklogs", 0, -1);
        $totalBacklogs = count($backlogs);
        for($i=0; $i < $totalBacklogs; $i++) {
            $this->fogbugz->edit(array('ixBug' => $backlogs[$i], 'plugin_projectbacklog_at_fogcreek_com_ibacklog' => $i));
        }
    }

}
