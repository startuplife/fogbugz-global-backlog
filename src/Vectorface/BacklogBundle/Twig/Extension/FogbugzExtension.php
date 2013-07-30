<?php
namespace Vectorface\BacklogBundle\Twig\Extension;

use Symfony\Component\HttpKernel\KernelInterface;

class FogbugzExtension extends \Twig_Extension
{

    public function __construct($params)
    {
        $this->params = $params;
    }

    public function getFunctions()
    {
        return array(
            'fogbugzticket' => new \Twig_Function_Method($this, 'fogbugzTicket'),
            'fogbugz' => new \Twig_Function_Method($this, 'fogbugz')
        );
    }

    public function fogbugzTicket($ticketID)
    {
        return $this->params['url_ticket'].$ticketID;
    }

    public function fogbugz()
    {
        return $this->params['url'];
    }

    public function getName()
    {
        return 'FogbugzExtension';
    }
}