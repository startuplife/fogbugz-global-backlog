<?php

namespace Vectorface\BacklogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProductController extends Controller
{

    public function indexAction()
    {
        $redis = $this->get("RedisService")->getRedis();

        $data['backlogs'] = array();
        $backlogs = $redis->lRange("ranks:product", 0, -1);
        foreach($backlogs as $backlog) {
            $data['backlogs'][] = $redis->hGetAll('ticket:'. $backlog);
        }

        return $this->render('VectorfaceBacklogBundle:Product:index.html.twig', $data);
    }

    public function addAction($ixBug)
    {
        $redis = $this->get("RedisService")->getRedis();

        $data['status'] = true;
        $data['ticket'] = $redis->hGetAll('ticket:'. $ixBug);
        $data['ticket']['url'] = $this->container->getParameter('fogbugz')['url_ticket'];
        $checkExistingSprint = $redis->zAdd('backlogs:product', $ixBug, $data['ticket']['sTitle']);
        if($checkExistingSprint) {
            $redis->lPush('ranks:product', $ixBug);

        } else {
            $data['status'] = false;
        }
        $response = new JsonResponse();
        $response->setData($data);
        return $response;
    }

}