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
        $data = $this->redis->hGetAll('VectorfaceBacklog:ticket:'. $ixBug);
        $this->redis->lPush('VectorfaceBacklog:rankOfBacklogs', $ixBug);
        $this->redis->zAdd('VectorfaceBacklog:listOfBacklogs', $ixBug, $data['sTitle']);
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function deleteAction($ixBug)
    {
        $response = new Response(json_encode($data));
        $response->setStatusCode(200);
        $this->redis->lRem('VectorfaceBacklog:rankOfBacklogs', $ixBug, 1);
        $this->redis->zRemRangeByScore('VectorfaceBacklog:listOfBacklogs', $ixBug, $ixBug);
        return $response;
    }

}
