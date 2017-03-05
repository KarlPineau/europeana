<?php

namespace DSG\AdministrationBundle\Controller;

use DSG\HomeBundle\Service\CsvResponse;
use DSG\ModelBundle\Entity\EuropeanaItem;
use DSG\ModelBundle\Entity\EuropeanaItemsSession;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DatasetInfoController extends Controller
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

            $response = new CsvResponse($returnList, 200, ['europeana_id', 'language', 'country', 'type', 'dataProvider']);
            $response->setFilename('dataset-info.csv');
            return $response;
        }

        $this->get('session')->getFlashBag()->add('notice', 'Use data.csv' );
        return $this->render('RSAdministrationBundle:Query:query.html.twig', array(
            'form' => $form->createView()));
    }

    protected function query($urlFile)
    {
        set_time_limit(0);
        $returnList = array();

        $row = 1;
        if (($handle = fopen($urlFile, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
                $num = count($data);
                $row++;
                for ($c=0; $c < $num; $c++) {
                    //$data[$c] = query
                    $content = $this->queryEuropeana($data[$c])[0];
                    if(isset($content->response->docs)) {
                        foreach ($content->response->docs as $doc) {
                            if(isset($doc->{'proxy_edm_type'})) {$dcType = $doc->{'proxy_edm_type'};}
                            elseif(isset($doc->{'proxy_dc_type.def'})) {$dcType = $doc->{'proxy_dc_type.def'};}
                            elseif(isset($doc->{'proxy_dc_type.en'})) {$dcType = $doc->{'proxy_dc_type.en'};}
                            else { $dcType = null;}

                            if(isset($doc->proxy_dc_language)) {$dcLanguage = $doc->proxy_dc_language;}
                            elseif(isset($doc->{'proxy_dc_language.def'})) {$dcLanguage = $doc->{'proxy_dc_language.def'};}
                            elseif(isset($doc->{'proxy_dc_language.en'})) {$dcLanguage = $doc->{'proxy_dc_language.en'};}
                            else { $dcLanguage = null;}

                            if(isset($doc->europeana_aggregation_edm_country)) {$edmCountry = $doc->europeana_aggregation_edm_country;}
                            elseif(isset($doc->{'europeana_aggregation_edm_country.def'})) {$edmCountry = $doc->{'europeana_aggregation_edm_country.def'};}
                            elseif(isset($doc->{'europeana_aggregation_edm_country.en'})) {$edmCountry = $doc->{'europeana_aggregation_edm_country.en'};}
                            else { $edmCountry = null;}

                            if(isset($doc->provider_aggregation_edm_dataProvider)) {$dataProvider = $doc->provider_aggregation_edm_dataProvider[0];} else { $dataProvider = null;}

                            $returnList[] = [urlencode($data[$c]), urlencode(json_encode($dcLanguage)), urlencode(json_encode($edmCountry)), urlencode(json_encode($dcType)), urlencode($dataProvider)];
                        }
                    }
                }
            }
            fclose($handle);
        } else {
            $returnList = $urlFile;
        }

        return $returnList;
    }

    public function queryEuropeana($query)
    {
        set_time_limit(0);
        $this->get('Buzz')->getClient()->setTimeout(0);

        $timestart=microtime(true);
        $queryResponse = $this->get('Buzz')->get('http://sol1.eanadev.org:9191/solr/search_1_shard1_replica2/select?q=europeana_id:"'.$query.'"&rows=1&wt=json');

        $timeend=microtime(true);
        $time=$timeend-$timestart;
        $timeQuery = number_format($time, 3);

        return [json_decode($queryResponse->getContent()), $timeQuery];
    }
}
