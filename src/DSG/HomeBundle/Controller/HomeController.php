<?php

namespace DSG\HomeBundle\Controller;

use DSG\ModelBundle\Entity\EuropeanaItemsSession;
use DSG\ModelBundle\Form\EuropeanaItemsSessionType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use DSG\HomeBundle\Service\CsvResponse;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $europeanaItemsSession = new EuropeanaItemsSession();
        $form = $this->createForm(EuropeanaItemsSessionType::class, $europeanaItemsSession);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $europeanaItemsSession->setSentConfirmation(false);
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

        return $this->render('DSGHomeBundle:Home:wait.html.twig', array(
            'europeanaItemsSession' => $europeanaItemsSession,
        ));

    }

    public function computeAction($europeanaItemsSession_id)
    {
        set_time_limit(0);
        $request = Request::createFromGlobals();
        if($request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $europeanaItemsSession = $em->getRepository('DSGModelBundle:EuropeanaItemsSession')->findOneById($europeanaItemsSession_id);
            if ($europeanaItemsSession === null) {throw $this->createNotFoundException('EuropeanaItemsSession [id=' . $europeanaItemsSession_id . '] not found.');}

            if($europeanaItemsSession->getSentConfirmation() == false) {
                $response = $this->get('dsg_model.dsgenerator')->generator($europeanaItemsSession);

                if(count($em->getRepository('DSGModelBundle:EuropeanaItem')->findBy(array('europeanaItemsSession' => $europeanaItemsSession))) >= ($europeanaItemsSession->getNumberOfItems()-1))
                {
                    $message = \Swift_Message::newInstance()
                        ->setSubject('Your dataset is ready!')
                        ->setFrom('cliches@karl-pineau.fr')
                        ->setTo($europeanaItemsSession->getEmail())
                        ->setBody('Your Europeana dataset is ready. Download it here : http://karlpine.cluster014.ovh.net/europeana/web/dsg/result/' . $europeanaItemsSession->getId() . ' - Enjoy !');

                    $this->get('mailer')->send($message);
                    $europeanaItemsSession->setSentConfirmation(true);
                    $em->persist($europeanaItemsSession);
                    $em->flush();
                }
                return new Response(json_encode($response));
            } else { return new Response(json_encode(true));}

        }
    }

    public function resultAction($europeanaItemsSession_id)
    {
        $em = $this->getDoctrine()->getManager();
        $europeanaItemsSession = $em->getRepository('DSGModelBundle:EuropeanaItemsSession')->findOneById($europeanaItemsSession_id);
        if ($europeanaItemsSession === null) {throw $this->createNotFoundException('EuropeanaItemsSession [id=' . $europeanaItemsSession_id . '] not found.');}
        $europeanaItems = $em->getRepository('DSGModelBundle:EuropeanaItem')->findBy(array('europeanaItemsSession' => $europeanaItemsSession));

        $data = array();
        foreach ($europeanaItems as $europeanaItem) {
            array_push($data, [$europeanaItem->getURI()]);
        }
        shuffle($data); // Create more "random" aspect

        $columns = 'uri';
        $response = new CsvResponse($data, 200, explode(', ', $columns));
        $response->setFilename("data.csv");
        return $response;
    }
}
