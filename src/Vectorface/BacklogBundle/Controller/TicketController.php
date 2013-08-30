<?php

namespace Vectorface\BacklogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class TicketController extends Controller
{

    public function indexAction()
    {

    }

    public function addAction($type, $typeID = NULL)
    {
        $redis = $this->get("RedisService")->getRedis();

        if($type == "sprints"){
            $redisKey = ":".$typeID;
        } else {
            $redisKey = "product";
        }

        $data['status'] = true;
        $data['ticket'] = $redis->hGetAll('ticket:'. $ixBug);
        $data['ticket']['url'] = $this->container->getParameter('fogbugz')['url_ticket'];
        $checkExisting = $redis->zAdd('backlogs:', $redisKey, $data['ticket']['sTitle']);

        if($checkExisting) {
            $redis->lPush('ranks:'.$redisKey, $ixBug);

            if($type == 'sprint'){
            //Rules say when we add to sprint that it automatically adds to product
                $checkExistingProduct = $redis->zAdd('backlogs:product', $ixBug, $data['ticket']['sTitle']);
                if($checkExistingProduct) {
                    $redis->lPush('ranks:product', $ixBug);
                }
            }

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

    public function editAction($ixBug)
    {
        $data = array();
        $fogbugz = $this->get("FogbugzService");

        $request = $this->get("request");
        $timeEstimate = $request->request->get("timeEstimate");
        $personAssignedTo = $request->request->get("personAssignedTo");

        if(!is_null($timeEstimate) || !is_null($personAssignedTo)) {
            $fogbugz->updatePersonAssignedTo($ixBug, $personAssignedTo);
            $fogbugz->updateTimeEstimate($ixBug, $timeEstimate);
        }

        $redis = $this->get("RedisService")->getRedis();
        $data = $redis->hGetAll('ticket:'. $ixBug);

        $response = new JsonResponse();
        $response->setData($data);
        return $response;
    }

}