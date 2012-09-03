<?php

namespace %namespace%

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class %controller.class_name% extends Controller
{

    /**
     * @Route("/")
     * @Method("GET")
     */
    public function indexAction()
    {
        $response = new Response();
        //$response->setVary(array('Accept-Encoding'));
        $response->setContent($this->renderView('::index.html.twig'));
        return $response;
    }

}
