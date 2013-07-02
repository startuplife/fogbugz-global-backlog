<?php

namespace Vectorface\BacklogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('VectorfaceBacklogBundle:Default:index.html.twig');
    }
}
