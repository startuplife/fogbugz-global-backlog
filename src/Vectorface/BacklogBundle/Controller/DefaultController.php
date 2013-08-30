<?php

namespace Vectorface\BacklogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $redis = $this->get("RedisService")->getRedis();

        $data['backlogs'] = array();
        $backlogs = $redis->lRange("rankOfBacklogs", 0, -1);
        foreach($backlogs as $backlog) {
            $data['backlogs'][] = $redis->hGetAll('ticket:'. $backlog);
        }

        return $this->render('VectorfaceBacklogBundle:Default:index.html.twig', $data);
    }

    public function autocompleteUsersAction()
    {
        $redis = $this->get("RedisService")->getRedis();

        $objects = $redis->zrevrange('users', 0, -1, true);

        $data=array();
        foreach ($objects as $key => $value) {
            $data[] = array('label'=> $key, 'value' => $value);
        }

        $response = new JsonResponse();
        $response->setData($data);
        return $response;
    }

    public function autocompleteTicketsAction($type = "")
    {
        $redis = $this->get("RedisService")->getRedis();
        $tickets = $redis->lRange('tickets', 0, -1);

        $redis->multi(\Redis::PIPELINE);
        foreach ($tickets as $id) {
            $redis->hGetAll('ticket:'.$id);
        }
        $tickets = $redis->exec();

        foreach($tickets as $ticket){
            $data[] = array('label'=> $ticket['sTitle'], 'value' => $ticket['ixBug']);
        }

        $response = new JsonResponse();
        $response->setData($data);
        return $response;
    }

    public function pushAction()
    {
        $fogbugz = $this->get("FogbugzService");
        $fogbugz->pushBacklog();

        $response = new RedirectResponse($this->generateUrl('vectorface_backlog_main'));
        return $response;
    }

    public function pullAction()
    {
        $fogbugz = $this->get("FogbugzService");
        $fogbugz->pullUsers();
        $fogbugz->pullTickets();

        $response = new RedirectResponse($this->generateUrl('vectorface_backlog_main'));
        return $response;
    }

}
