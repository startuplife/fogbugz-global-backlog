<?php

namespace Vectorface\BacklogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $redisHelper = $this->get("redishelper");
        $redis = $redisHelper->getRedis();

        $data['backlogs'] = array();
        $backlogs = $redis->lRange("VectorfaceBacklog:rankOfBacklogs", 0, -1);
        foreach($backlogs as $backlog) {
            $data['backlogs'][] = $redis->hGetAll('VectorfaceBacklog:ticket:'. $backlog);
        }

        return $this->render('VectorfaceBacklogBundle:Default:index.html.twig', $data);
    }

    public function autocompleteAction()
    {
        $redisHelper = $this->get("redishelper");
        $redis = $redisHelper->getRedis();

        $tickets = $redis->zrevrange("VectorfaceBacklog:tickets", 0, -1, true);

        $data=array();
        foreach ($tickets as $key => $value) {
            $data[] = array('label'=> $key, 'value' => $value);
        }

        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    public function addAction($ixBug)
    {
        $redisHelper = $this->get("redishelper");
        $redis = $redisHelper->getRedis();

        $data['status'] = true;
        $data['ticket'] = $redis->hGetAll('VectorfaceBacklog:ticket:'. $ixBug);
        $data['ticket']['url'] = $this->container->getParameter('fogbugz_url_ticket');
        $checkExisting = $redis->zAdd('VectorfaceBacklog:listOfBacklogs', $ixBug, $data['ticket']['sTitle']);
        if($checkExisting) {
            $redis->lPush('VectorfaceBacklog:rankOfBacklogs', $ixBug);
        } else {
            $data['status'] = false;
        }
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function deleteAction($ixBug)
    {
        $redisHelper = $this->get("redishelper");
        $redis = $redisHelper->getRedis();

        $response = new Response();
        $response->setStatusCode(200);
        $redis->lRem('VectorfaceBacklog:rankOfBacklogs', $ixBug, 1);
        $redis->zRemRangeByScore('VectorfaceBacklog:listOfBacklogs', $ixBug, $ixBug);
        return $response;
    }

    public function upAction($position, $ixBug)
    {
        $redisHelper = $this->get("redishelper");
        $redis = $redisHelper->getRedis();

        $countBacklogs = $redis->lLen('VectorfaceBacklog:rankOfBacklogs');
        if($countBacklogs > 1 && $position != 0) {
            //Assumption we're ever going to have one in the list
            $checkRightOne = $redis->lRange('VectorfaceBacklog:rankOfBacklogs', $position, $position);
            $checkRightOne = $checkRightOne [0];

            if($ixBug == $checkRightOne) {
                //Get element before current position
                $beforePosition = $redis->lRange('VectorfaceBacklog:rankOfBacklogs', $position-1, $position-1);
                //Assumption we're ever going to have one in the list
                $beforePosition = $beforePosition[0];

                //Insert new record, remove prior
                $redis->lInsert('VectorfaceBacklog:rankOfBacklogs', \Redis::BEFORE, $beforePosition, $ixBug);
                $redis->lRem('VectorfaceBacklog:rankOfBacklogs', $ixBug, -1);

            }
        }
        $response = new Response();
        $response->setStatusCode(200);
        return $response;
    }

    public function downAction($position, $ixBug)
    {
        $redisHelper = $this->get("redishelper");
        $redis = $redisHelper->getRedis();

        $countBacklogs = $redis->lLen('VectorfaceBacklog:rankOfBacklogs');
        if($countBacklogs > 1 && $position != $countBacklogs) {
            //Assumption we're ever going to have one in the list
            $checkRightOne = $redis->lRange('VectorfaceBacklog:rankOfBacklogs', $position, $position);
            $checkRightOne = $checkRightOne [0];

            if($ixBug == $checkRightOne) {
                //Get element before current position
                $beforePosition = $redis->lRange('VectorfaceBacklog:rankOfBacklogs', $position+1, $position+1);
                //Assumption we're ever going to have one in the list
                $beforePosition = $beforePosition[0];

                //Insert new record, remove prior
                $redis->lInsert('VectorfaceBacklog:rankOfBacklogs', \Redis::AFTER, $beforePosition, $ixBug);
                $redis->lRem('VectorfaceBacklog:rankOfBacklogs', $ixBug, 1);

            }
        }
        $response = new Response();
        $response->setStatusCode(200);
        return $response;
    }

}
