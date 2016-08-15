<?php

namespace DSG\AdministrationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $europeanaItems = $em->getRepository('NERModelBundle:EuropeanaItem')->findAll();

        return $this->render('NERHomeBundle:EuropeanaItem:index.html.twig', array(
            'europeanaItems' => $europeanaItems
        ));
    }
}
