<?php

namespace RS\AdministrationBundle\Controller;

use DSG\HomeBundle\Service\CsvResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CurrentController extends Controller
{
    // http://sol7.eanadev.org:9191/solr/search/minimlt?wt=json&mlt=true <- USING AT MLTFiddle
    // https://cwiki.apache.org/confluence/display/solr/MoreLikeThis <- doc
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

            $response = new CsvResponse($returnList, 200, ['europeana_id', 'dcType', 'dcSubject', 'dcCreator', 'title', 'dataProvider']);
            $response->setFilename("parametersOf1000.csv");
            return $response;
        }

        return $this->render('DSGAdministrationBundle:BabelNetDataSet:query.html.twig', array('form' => $form->createView()));
    }

    protected function query($urlFile)
    {
        set_time_limit(0);
        $em = $this->getDoctrine()->getManager();
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
                            if(isset($doc->proxy_edm_type)) {$dcType = $doc->proxy_edm_type;} else { $dcType = null;}
                            if(isset($doc->proxy_edm_subject)) {$dcSubject = $doc->proxy_edm_subject;} else { $dcSubject = null;}
                            if(isset($doc->proxy_dc_creator)) {$dcCreator = $doc->proxy_dc_creator;} else { $dcCreator = null;}
                            if(isset($doc->proxy_dc_title)) {$title = $doc->proxy_dc_title[0];} else { $title = null;}
                            if(isset($doc->provider_aggregation_edm_dataProvider)) {$dataProvider = $doc->provider_aggregation_edm_dataProvider[0];} else { $dataProvider = null;}

                            $returnList[] = [urlencode($data[$c]), urlencode(json_encode($dcType)), urlencode(json_encode($dcSubject)), urlencode(json_encode($dcCreator)), urlencode($title), urlencode($dataProvider)];
                        }
                    }
                }
            }
            fclose($handle);
        }

        return $returnList;
    }


    public function queryEuropeana($query)
    {
        //http://sol7.eanadev.org:9191/solr/search/search?q=*&qf=LANGUAGE:fr&fl=europeana_id&rows=300000&cursorMark=*&wt=json&start=0&sort=europeana_id asc
        set_time_limit(0);
        $this->get('Buzz')->getClient()->setTimeout(0);

        $timestart=microtime(true);
        $queryResponse = $this->get('Buzz')->get('http://sol7.eanadev.org:9191/solr/search/search?q=europeana_id:"'.$query.'"&rows=1&wt=json');

        $timeend=microtime(true);
        $time=$timeend-$timestart;
        $timeQuery = number_format($time, 3);

        return [json_decode($queryResponse->getContent()), $timeQuery];
    }
}
