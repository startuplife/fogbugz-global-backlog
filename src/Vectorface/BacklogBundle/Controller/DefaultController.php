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

    public function autocompleteAction()
    {
        $redis = $this->get("RedisService")->getRedis();

        $tickets = $redis->zrevrange("tickets", 0, -1, true);

        $data=array();
        foreach ($tickets as $key => $value) {
            $data[] = array('label'=> $key, 'value' => $value);
        }

        $response = new JsonResponse();
        $response->setData($data);
        return $response;
    }
    public function addAction($ixBug)
    {
        $redis = $this->get("RedisService")->getRedis();

        $data['status'] = true;
        $data['ticket'] = $redis->hGetAll('ticket:'. $ixBug);
        $data['ticket']['url'] = $this->container->getParameter('fogbugz_url_ticket');
        $checkExisting = $redis->zAdd('listOfBacklogs', $ixBug, $data['ticket']['sTitle']);
        if($checkExisting) {
            $redis->lPush('rankOfBacklogs', $ixBug);
        } else {
            $data['status'] = false;
        }
        $response = new JsonResponse();
        $response->setData($data);
        return $response;
    }

    public function deleteAction($ixBug)
    {
        $redis = $this->get("RedisService")->getRedis();

        $response = new Response();
        $redis->lRem('rankOfBacklogs', $ixBug, 1);
        $redis->zRemRangeByScore('listOfBacklogs', $ixBug, $ixBug);
        return $response;
    }

    public function moveAction($ixBug, $position)
    {
        $redis = $this->get("RedisService")->getRedis();

        $redis->lRem('rankOfBacklogs', $ixBug, 0);

        $currentValue = $redis->lIndex('rankOfBacklogs', $position);
        $listLength = $redis->lLen('rankOfBacklogs');

        if($position == 0) {
            $redis->lPush('rankOfBacklogs', $ixBug);
        } elseif($position == $listLength) {
            $redis->rPush('rankOfBacklogs', $ixBug);
        } else {
            $redis->lInsert('rankOfBacklogs', \Redis::BEFORE, $currentValue, $ixBug);
        }

        $response = new Response();
        return $response;
    }

    public function pushAction()
    {
        $fogbugz = $this->get("FogbugzService");
        $fogbugz->logon();
        $fogbugz->pushBacklog();

        $response = new RedirectResponse($this->generateUrl('vectorface_backlog_main'));
        return $response;
    }

    public function pullAction()
    {
        $fogbugz = $this->get("FogbugzService");
        $fogbugz->logon();
        $fogbugz->pullUsers();
        $fogbugz->pullTickets();

        $response = new RedirectResponse($this->generateUrl('vectorface_backlog_main'));
        return $response;
    }

}
