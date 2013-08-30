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
        $backlogs = $redis->lRange("sprints", 0, -1);
        foreach($backlogs as $backlog) {
            $data['backlogs'][] = $redis->hGetAll('ticket:'. $backlog);
        }

        return $this->render('VectorfaceBacklogBundle:Sprint:index.html.twig', $data);
    }

    public function viewAction($id)
    {
        $redis = $this->get("RedisService")->getRedis();
        $data['listId'] = $id;
        $data['backlogs'] = array();
        $backlogs = $redis->lRange("ranks:sprints:".$id, 0, -1);
        foreach($backlogs as $backlog) {
            $data['backlogs'][] = $redis->hGetAll('ticket:'. $backlog);
        }

        return $this->render('VectorfaceBacklogBundle:Sprint:view.html.twig', $data);
    }

    public function addAction($ixBug, $id)
    {
        $redis = $this->get("RedisService")->getRedis();

        $data['status'] = true;
        $data['ticket'] = $redis->hGetAll('ticket:'. $ixBug);
        $data['ticket']['url'] = $this->container->getParameter('fogbugz')['url_ticket'];
        $checkExistingSprint = $redis->zAdd('backlogs:sprints:'.$id, $ixBug, $data['ticket']['sTitle']);
        if($checkExistingSprint) {
            $redis->lPush('ranks:sprints:'.$id, $ixBug);

            //Rules say when we add to sprint that it automatically adds to product
            $checkExistingProduct = $redis->zAdd('backlogs:product', $ixBug, $data['ticket']['sTitle']);
            if($checkExistingProduct) {
                $redis->lPush('ranks:product', $ixBug);
            }

        } else {
            $data['status'] = false;
        }
        $response = new JsonResponse();
        $response->setData($data);
        return $response;
    }

}