<?php

namespace NER\HomeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $uploadFiles = $em->getRepository('NERModelBundle:UploadFile')->findAll();

        return $this->render('NERHomeBundle:Home:index.html.twig', array('uploadFiles' => $uploadFiles));
    }

    public function viewAction($uploadFile_id)
    {
        $em = $this->getDoctrine()->getManager();
        $uploadFile = $em->getRepository('NERModelBundle:UploadFile')->findOneById($uploadFile_id);

        if($uploadFile === null) {throw $this->createNotFoundException('UploadFile [id='.$uploadFile_id.'] not found.');}

        return $this->render('NERHomeBundle:Home:view.html.twig', array(
            'entities' => $em->getRepository('NERModelBundle:Entity')->findByUploadFile($uploadFile),
            'uploadFile' => $uploadFile
        ));

    }
}
