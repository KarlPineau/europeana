<?php

namespace RS\AdministrationBundle\Controller;

use DSG\HomeBundle\Service\CsvResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TestController extends Controller
{
    public function queryAction(Request $request)
    {
        set_time_limit(0);
        $form = $this->createFormBuilder()
            ->add('urlFile', UrlType::class, array('mapped' => false, 'required' => true))
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $urlFile = $form->get('urlFile')->getData();

            $returnList = $this->query($urlFile);

            return $this->render('RSAdministrationBundle:Test:index.html.twig', array('returnList' => $returnList));
        }

        return $this->render('RSAdministrationBundle:Test:query.html.twig', array('form' => $form->createView()));
    }

    protected function query($urlFile)
    {
        set_time_limit(0);
        $row = 1;
        $lines = [];
        if (($handle = fopen($urlFile, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row++;
                $lines[] = $data;

            }
            fclose($handle);
        }

        return $lines;
    }
}
