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

    public function __construct(array $fogbugzConfig, RedisService $redis)
    {
        $this->redis = $redis->getRedis();
        $this->logon($fogbugzConfig);
    }

    public function logon($config){
        $this->fogbugz = new FogBugz\Api(
            $config['user'],
            $config['password'],
            $config['url']
            );
        $this->fogbugz->logon();
    }

    public function pullTickets()
    {
        $xml = $this->fogbugz->search(array('q' => 'status:open OR status:closed', 'cols' => 'ixBug,sCategory,sTitle,sProject,ixProject,sStatus,sEmailAssignedTo,sPersonAssignedTo,ixPersonAssignedTo,hrsCurrEst'));
        $this->redis->del('tickets');
        $this->redis->multi(\Redis::PIPELINE);
        foreach($xml->children() as $tickets){
            foreach($tickets->children() as $ticket){
                $data = array(
                    'ixBug' => (string)$ticket->ixBug,
                    'sCategory' => (string)$ticket->sCategory,
                    'sTitle' => (string)$ticket->sTitle,
                    'ixProject' => (string)$ticket->ixProject,
                    'sProject' => (string)$ticket->sProject,
                    'sStatus' => (string)$ticket->sStatus,
                    'sEmailAssignedTo' => (string)$ticket->sEmailAssignedTo,
                    'sPersonAssignedTo' => (string)$ticket->sPersonAssignedTo,
                    'hrsCurrEst' => (string)$ticket->hrsCurrEst
                    );
                $this->redis->hMset('ticket:'.(string)$ticket->ixBug, $data);

                $type = "closed";
                if((string)$data['sStatus'] == "Active"){
                    $type = "open";
                }

                $this->redis->lPush('tickets:'.$type, (string)$ticket->ixBug);
                $this->redis->lPush('tickets', (string)$ticket->ixBug);
            }
        }
        $this->redis->exec();

    }

    public function pushBacklog()
    {
        $backlogs = $this->redis->lRange("rankOfBacklogs", 0, -1);
        $totalBacklogs = count($backlogs);
        for($i=0; $i < $totalBacklogs; $i++) {
            $this->fogbugz->edit(array('ixBug' => $backlogs[$i], 'plugin_projectbacklog_at_fogcreek_com_ibacklog' => $i));
        }
    }

    public function pullUsers()
    {
        $xml = $this->fogbugz->listPeople(array('fIncludeVirtual' => 1, 'fIncludeNormal' => 1));
        $this->redis->del('users');
        foreach($xml->children() as $users){
            foreach($users->children() as $user){
                $this->redis->zAdd('users', $user->ixPerson, (string)$user->sFullName);
            }
        }
    }

    public function updateTimeEstimate($ixBug, $estimatedTime)
    {
        $ticket = $this->redis->hGetAll('ticket:'. $ixBug);
        if($ticket['hrsCurrEst'] != $estimatedTime) {
            $this->fogbugz->edit(array('ixBug' => $ixBug, 'hrsCurrEst' => $estimatedTime));
            $this->redis->hSet('ticket:'.$ixBug, 'hrsCurrEst', $estimatedTime);
        }
    }

    public function updatePersonAssignedTo($ixBug, $personAssignedTo)
    {
        $ticket = $this->redis->hGetAll('ticket:'. $ixBug);
        if($ticket['sPersonAssignedTo'] != $personAssignedTo) {
            $this->fogbugz->edit(array('ixBug' => $ixBug, 'sPersonAssignedTo' => $personAssignedTo));
            $this->redis->hSet('ticket:'.$ixBug, 'sPersonAssignedTo', $personAssignedTo);
        }
    }

}