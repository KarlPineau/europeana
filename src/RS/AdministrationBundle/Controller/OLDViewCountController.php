<?php

namespace RS\AdministrationBundle\Controller;

use DSG\HomeBundle\Service\CsvResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ViewCountController extends Controller
{
    public function queryAction(Request $request)
    {
        set_time_limit(0);
        $form = $this->createFormBuilder()
            ->add('urlFile', UrlType::class,  array('mapped' => false, 'required' => true))
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $urlFile = $form->get('urlFile')->getData();
            $returnList = $this->query($urlFile);

            return $this->render('RSAdministrationBundle:ViewCount:view.html.twig', array('returnList' => $returnList));
        }

        $this->get('session')->getFlashBag()->add('notice', 'use queryItemCountResults.csv' );
        return $this->render('DSGAdministrationBundle:BabelNetDataSet:query.html.twig', array('form' => $form->createView()));
    }

    protected function query($urlFile)
    {
        set_time_limit(0);
        $returnList = array();

        $row = 1;
        if (($handle = fopen($urlFile, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row++; $data[2];

                $returnList[] = ['query' => $data[0], 'europeana_id' => urldecode($data[1]), 'count' => $data[2]];
            }
            fclose($handle);
        }

        usort($returnList, function ($a, $b) {
            return $a['count'] - $b['count'];
        });
        return $returnList;
    }

}
