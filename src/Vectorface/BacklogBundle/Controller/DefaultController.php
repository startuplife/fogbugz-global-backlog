<?php

namespace Vectorface\BacklogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;


class DefaultController extends Controller
{
    public function indexAction()
    {
        $redisHelper = $this->get("redishelper");
        $redis = $redisHelper->getRedis();

        $data['backlogs'] = array();
        $backlogs = $redis->lRange("rankOfBacklogs", 0, -1);
        foreach($backlogs as $backlog) {
            $data['backlogs'][] = $redis->hGetAll('ticket:'. $backlog);
        }

        return $this->render('VectorfaceBacklogBundle:Default:index.html.twig', $data);
    }

    public function autocompleteAction()
    {
        $redisHelper = $this->get("redishelper");
        $redis = $redisHelper->getRedis();

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
        $redisHelper = $this->get("redishelper");
        $redis = $redisHelper->getRedis();

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
        $redisHelper = $this->get("redishelper");
        $redis = $redisHelper->getRedis();

        $response = new Response();
        $redis->lRem('rankOfBacklogs', $ixBug, 1);
        $redis->zRemRangeByScore('listOfBacklogs', $ixBug, $ixBug);
        return $response;
    }

    public function upAction($position, $ixBug)
    {
        $redisHelper = $this->get("redishelper");
        $redis = $redisHelper->getRedis();

        $countBacklogs = $redis->lLen('rankOfBacklogs');
        if($countBacklogs > 1 && $position != 0) {
            //Assumption we're ever going to have one in the list
            $checkRightOne = $redis->lRange('rankOfBacklogs', $position, $position);
            $checkRightOne = $checkRightOne [0];

            if($ixBug == $checkRightOne) {
                //Get element before current position
                $beforePosition = $redis->lRange('rankOfBacklogs', $position-1, $position-1);
                //Assumption we're ever going to have one in the list
                $beforePosition = $beforePosition[0];

                //Insert new record, remove prior
                $redis->lInsert('rankOfBacklogs', \Redis::BEFORE, $beforePosition, $ixBug);
                $redis->lRem('rankOfBacklogs', $ixBug, -1);

            }
        }
        $response = new Response();
        return $response;
    }

    public function downAction($position, $ixBug)
    {
        $redisHelper = $this->get("redishelper");
        $redis = $redisHelper->getRedis();

        $countBacklogs = $redis->lLen('rankOfBacklogs');
        if($countBacklogs > 1 && $position != $countBacklogs) {
            //Assumption we're ever going to have one in the list
            $checkRightOne = $redis->lRange('rankOfBacklogs', $position, $position);
            $checkRightOne = $checkRightOne [0];

            if($ixBug == $checkRightOne) {
                //Get element before current position
                $beforePosition = $redis->lRange('rankOfBacklogs', $position+1, $position+1);
                //Assumption we're ever going to have one in the list
                $beforePosition = $beforePosition[0];

                //Insert new record, remove prior
                $redis->lInsert('rankOfBacklogs', \Redis::AFTER, $beforePosition, $ixBug);
                $redis->lRem('rankOfBacklogs', $ixBug, 1);

            }
        }
        $response = new Response();
        return $response;
    }

}
