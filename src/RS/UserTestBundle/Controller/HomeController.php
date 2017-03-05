<?php

namespace RS\UserTestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller
{
    public function indexAction()
    {
        return $this->render('RSUserTestBundle:Home:index.html.twig');
    }
}
