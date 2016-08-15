<?php

namespace DSG\LayoutBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('DSGLayoutBundle:Default:index.html.twig');
    }
}
