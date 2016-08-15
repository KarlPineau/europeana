<?php

namespace NER\AdministrationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('NERAdministrationBundle:Default:index.html.twig');
    }
}
