<?php

namespace DSG\AdministrationBundle\Controller;

use DSG\HomeBundle\Service\CsvResponse;
use DSG\ModelBundle\Entity\EuropeanaItem;
use DSG\ModelBundle\Entity\EuropeanaItemsSession;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Request;

class BabelNetDataSetController extends Controller
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

            $response = new CsvResponse($returnList, 200, explode(', ', 'URI'));
            $response->setFilename("data.csv");
            return $response;
        }

        return $this->render('DSGAdministrationBundle:BabelNetDataSet:query.html.twig', array('form' => $form->createView()));
    }

    protected function query($urlFile)
    {
        set_time_limit(0);
        $em = $this->getDoctrine()->getManager();
        $europeanaItemsSession = new EuropeanaItemsSession();
        $returnList = array();

        $row = 1;
        if (($handle = fopen($urlFile, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $num = count($data);
                $row++;
                for ($c=0; $c < $num; $c++) {
                    //$data[$c] = query
                    $content = $this->queryEuropeana($data[$c])[0];
                    if(isset($content->response->docs)) {
                        foreach ($content->response->docs as $doc) {
                            $europeanaItem = new EuropeanaItem();
                            $europeanaItem->setURI($doc->europeana_id);
                            $europeanaItem->setEuropeanaItemsSession($europeanaItemsSession);
                            $em->persist($europeanaItem);
                            $returnList[] = [$doc->europeana_id];
                        }
                    }
                }
            }
            fclose($handle);
        }

        $em->persist($europeanaItemsSession);
        $em->flush();

        return $returnList;
    }


    public function queryEuropeana($query)
    {
        //http://sol7.eanadev.org:9191/solr/search/search?q=*&qf=LANGUAGE:fr&fl=europeana_id&rows=300000&cursorMark=*&wt=json&start=0&sort=europeana_id asc
        set_time_limit(0);
        $this->get('Buzz')->getClient()->setTimeout(0);

        $timestart=microtime(true);
        $queryResponse = $this->get('Buzz')->get($query);

        $timeend=microtime(true);
        $time=$timeend-$timestart;
        $timeQuery = number_format($time, 3);

        return [json_decode($queryResponse->getContent()), $timeQuery];
    }
}
