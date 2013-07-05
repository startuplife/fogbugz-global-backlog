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
        return $this->render('VectorfaceBacklogBundle:Default:index.html.twig');
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
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
