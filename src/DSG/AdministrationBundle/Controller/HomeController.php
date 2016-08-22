<?php

namespace DSG\AdministrationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $europeanaItems = $em->getRepository('DSGModelBundle:EuropeanaItem')->findAll();

        return $this->render('DSGAdministrationBundle:Home:index.html.twig', array(
            'europeanaItems' => $europeanaItems
        ));
    }

    public function testAction()
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('Your dataset is ready!')
            ->setFrom('cliches@karl-pineau.fr')
            ->setTo('karl.pineau@gmail.com')
            ->setBody('Your dataset is ready : <a></a>')
        ;

        $this->get('mailer')->send($message);

        return $this->redirectToRoute('dsg_administration_home_index');
    }
}
