<?php

namespace RS\ModelBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('RSModelBundle:Default:index.html.twig');
    }
}
