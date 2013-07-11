<?php

namespace Vectorface\BacklogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function __construct()
    {
        $this->redis = new \Redis();
        $this->redis->connect('127.0.0.1');
    }

    public function indexAction()
    {
        $data['backlogs'] = array();
        $backlogs = $this->redis->lRange("VectorfaceBacklog:rankOfBacklogs", 0, -1);
        foreach($backlogs as $backlog) {
            $data['backlogs'][] = $this->redis->hGetAll('VectorfaceBacklog:ticket:'. $backlog);
        }

        return $this->render('VectorfaceBacklogBundle:Default:index.html.twig', $data);
    }

    public function autocompleteAction()
    {
        $tickets = $this->redis->zrevrange("VectorfaceBacklog:tickets", 0, -1, true);

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
        $data['status'] = true;
        $data['ticket'] = $this->redis->hGetAll('VectorfaceBacklog:ticket:'. $ixBug);
        $checkExisting = $this->redis->zAdd('VectorfaceBacklog:listOfBacklogs', $ixBug, $data['ticket']['sTitle']);
        if($checkExisting) {
            $this->redis->lPush('VectorfaceBacklog:rankOfBacklogs', $ixBug);
        } else {
            $data['status'] = false;
        }
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function deleteAction($ixBug)
    {
        $response = new Response();
        $response->setStatusCode(200);
        $this->redis->lRem('VectorfaceBacklog:rankOfBacklogs', $ixBug, 1);
        $this->redis->zRemRangeByScore('VectorfaceBacklog:listOfBacklogs', $ixBug, $ixBug);
        return $response;
    }

    public function upAction($position, $ixBug)
    {
        $countBacklogs = $this->redis->lLen('VectorfaceBacklog:rankOfBacklogs');
        if($countBacklogs > 1 && $position != 0) {
            //Assumption we're ever going to have one in the list
            $checkRightOne = $this->redis->lRange('VectorfaceBacklog:rankOfBacklogs', $position, $position);
            $checkRightOne = $checkRightOne [0];

            if($ixBug == $checkRightOne) {
                //Get element before current position
                $beforePosition = $this->redis->lRange('VectorfaceBacklog:rankOfBacklogs', $position-1, $position-1);
                //Assumption we're ever going to have one in the list
                $beforePosition = $beforePosition[0];

                //Insert new record, remove prior
                $this->redis->lInsert('VectorfaceBacklog:rankOfBacklogs', \Redis::BEFORE, $beforePosition, $ixBug);
                $this->redis->lRem('VectorfaceBacklog:rankOfBacklogs', $ixBug, -1);

            }
        }
        $response = new Response();
        $response->setStatusCode(200);
        return $response;
    }

    public function downAction($position, $ixBug)
    {
        $countBacklogs = $this->redis->lLen('VectorfaceBacklog:rankOfBacklogs');
        if($countBacklogs > 1 && $position != $countBacklogs) {
            //Assumption we're ever going to have one in the list
            $checkRightOne = $this->redis->lRange('VectorfaceBacklog:rankOfBacklogs', $position, $position);
            $checkRightOne = $checkRightOne [0];

            if($ixBug == $checkRightOne) {
                //Get element before current position
                $beforePosition = $this->redis->lRange('VectorfaceBacklog:rankOfBacklogs', $position+1, $position+1);
                //Assumption we're ever going to have one in the list
                $beforePosition = $beforePosition[0];

                //Insert new record, remove prior
                $this->redis->lInsert('VectorfaceBacklog:rankOfBacklogs', \Redis::AFTER, $beforePosition, $ixBug);
                $this->redis->lRem('VectorfaceBacklog:rankOfBacklogs', $ixBug, 1);

            }
        }
        $response = new Response();
        $response->setStatusCode(200);
        return $response;
    }

}
