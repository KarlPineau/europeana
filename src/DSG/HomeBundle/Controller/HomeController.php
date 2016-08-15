<?php

namespace DSG\HomeBundle\Controller;

use DSG\ModelBundle\Entity\EuropeanaItemsSession;
use DSG\ModelBundle\Form\EuropeanaItemsSessionType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use DSG\HomeBundle\Service\CsvResponse;

class HomeController extends Controller
{
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $europeanaItemsSession = new EuropeanaItemsSession();

        $form = $this->createForm(EuropeanaItemsSessionType::class, $europeanaItemsSession);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($europeanaItemsSession);
            $em->flush();

            return $this->redirect($this->generateUrl('dsg_home_home_wait', array('europeanaItemsSession_id' => $europeanaItemsSession->getId())));

        }

        return $this->render('DSGHomeBundle:Home:index.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function waitAction($europeanaItemsSession_id)
    {
        $em = $this->getDoctrine()->getManager();
        $europeanaItemsSession = $em->getRepository('DSGModelBundle:EuropeanaItemsSession')->findOneById($europeanaItemsSession_id);

        if($europeanaItemsSession === null) {throw $this->createNotFoundException('EuropeanaItemsSession [id='.$europeanaItemsSession_id.'] not found.');}

        return $this->redirectToRoute('dsg_model_dsGenerator_generator', array('europeanaItemsSession_id' => $europeanaItemsSession_id));

    }

    public function resultAction($europeanaItemsSession_id)
    {
        $em = $this->getDoctrine()->getManager();
        $europeanaItemsSession = $em->getRepository('DSGModelBundle:EuropeanaItemsSession')->findOneById($europeanaItemsSession_id);

        if($europeanaItemsSession === null) {throw $this->createNotFoundException('EuropeanaItemsSession [id='.$europeanaItemsSession_id.'] not found.');}
        $europeanaItems = $em->getRepository('DSGModelBundle:EuropeanaItem')->findBy(array('europeanaItemsSession' => $europeanaItemsSession));

        $data = array();
        foreach($europeanaItems as $europeanaItem) {
            array_push($data, [$europeanaItem->getURI()]);
        }

        $columns = 'uri';
        $response = new CsvResponse($data, 200, explode( ', ', $columns ) );
        $response->setFilename( "data.csv" );
        return $response;
    }
}
