<?php

namespace NER\HomeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller
{
    public function indexAction()
    {
        return $this->render('NERHomeBundle:Home:index.html.twig');
    }
}
