<?php

namespace RS\AdministrationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('RSAdministrationBundle:Default:index.html.twig');
    }
}
