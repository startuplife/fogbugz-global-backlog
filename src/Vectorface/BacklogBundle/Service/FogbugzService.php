<?php

namespace Vectorface\BacklogBundle\Service;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Vectorface\BacklogBundle\AbstractContainerAware;
use Vectorface\BacklogBundle\Service\RedisService;
use There4\FogBugz;

class FogbugzService extends AbstractContainerAware
{
    private $redis;
    private $logon;

    public function __construct(RedisService $redis)
    {
        $this->redis = $redis->getRedis();
    }

    public function logon(){
        $parameters = $this->getContainer()->getParameter("fogbugz");
        $this->fogbugz = new FogBugz\Api(
            $parameters['user'],
            $parameters['password'],
            $parameters['url']
            );
        $this->fogbugz->logon();
    }

    public function populate()
    {
        $xml = $this->fogbugz->search(array('q' => 'status:"active" OR status:"open"', 'cols' => 'ixBug,sCategory,sTitle,sProject,ixProject,ixFixFor,sFixFor,sEmailAssignedTo,sPersonAssignedTo,ixPersonAssignedTo'));
        $this->redis->del('tickets');
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
                $this->redis->hMset('ticket:'.(string)$ticket->ixBug, $data);
                $this->redis->zAdd('tickets', (string)$ticket->ixBug, (string)$ticket->sTitle);
            }
        }
        return $xml->cases->attributes()->count;
    }

    public function removeClosedTickets()
    {
        $this->redis->zInter('listOfBacklogs', array('tickets', 'listOfBacklogs'), array(1, 0));
        $backLogs = $this->redis->lRange("rankOfBacklogs", 0, -1);
        foreach($backLogs as $backlog) {
            $exists = $this->redis->ZRANGEBYSCORE('listOfBacklogs', $backlog, $backlog);
            if(empty($exists)) {
                $this->redis->lRem('rankOfBacklogs', $backlog);
            }

        }
    }

    public function pushBacklog()
    {
        $backlogs = $this->redis->lRange("rankOfBacklogs", 0, -1);
        $totalBacklogs = count($backlogs);
        for($i=0; $i < $totalBacklogs; $i++) {
            $this->fogbugz->edit(array('ixBug' => $backlogs[$i], 'plugin_projectbacklog_at_fogcreek_com_ibacklog' => $i));
        }
    }

}