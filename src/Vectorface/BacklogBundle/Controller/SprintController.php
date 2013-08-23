<?php

namespace Vectorface\BacklogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class SprintController extends Controller
{

    public function indexAction()
    {
        $redis = $this->get("RedisService")->getRedis();

        $data['backlogs'] = array();
        $backlogs = $redis->lRange("rankOfBacklogs", 0, -1);
        foreach($backlogs as $backlog) {
            $data['backlogs'][] = $redis->hGetAll('ticket:'. $backlog);
        }

        return $this->render('VectorfaceBacklogBundle:Sprint:index.html.twig', $data);
    }

    public function viewAction($id)
    {
        $redis = $this->get("RedisService")->getRedis();

        $data['backlogs'] = array();
        $backlogs = $redis->lRange("rankOfBacklogs", 0, -1);
        foreach($backlogs as $backlog) {
            $data['backlogs'][] = $redis->hGetAll('ticket:'. $backlog);
        }

        return $this->render('VectorfaceBacklogBundle:Sprint:view.html.twig', $data);
    }

}