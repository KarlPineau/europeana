<?php

namespace RS\AdministrationBundle\Controller;

use DSG\HomeBundle\Service\CsvResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Request;

class CurrentController extends Controller
{
    /*
     * AIM of this controller:
     * RETURN THE LIST OF PARAMETERS FOR FURTHER SEARCH, COMPUTED FROM THE REFERENCE ITEM(S)
     */

    // http://sol1.eanadev.org:9191/solr/search/minimlt?wt=json&mlt=true <- USING AT MLTFiddle
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

            $dateTime = new \DateTime();
            $timestamp = $dateTime->getTimestamp();
            $fs = new \Symfony\Component\Filesystem\Filesystem();
            $fs->mkdir('data/'.$timestamp);

            $date = date('Y-m-d-h-i-s');
            $response = new CsvResponse($returnList, 200, ['europeana_id', 'dcType', 'dcSubject', 'dcCreator', 'title', 'dataProvider', 'when', 'where']);
            $response->setFilename($timestamp.'-'.$date.'-parametersOf1000.csv');
            $fs->dumpFile('data/'.$timestamp.'/'.$timestamp.'-'.$date.'-parametersOf1000.csv', $response);
            $fs->copy('../src/RS/AdministrationBundle/Controller/CurrentController.php', 'data/'.$timestamp.'/CurrentController.php');

            return $response;
        }

        $this->get('session')->getFlashBag()->add('notice', 'Step 1: use data.csv' );
        return $this->render('RSAdministrationBundle:Query:query.html.twig', array(
            'form' => $form->createView(),
            'next' => 'rs_administration_sic_query'));
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

                            if(isset($doc->proxy_dc_subject)) {$dcSubject = $doc->proxy_dc_subject;}
                            elseif(isset($doc->{'proxy_dc_subject.def'})) {$dcSubject = $doc->{'proxy_dc_subject.def'};}
                            elseif(isset($doc->{'proxy_dc_subject.en'})) {$dcSubject = $doc->{'proxy_dc_subject.en'};}
                            elseif(isset($doc->proxy_edm_subject)) {$dcSubject = $doc->proxy_edm_subject;}
                            elseif(isset($doc->subject)) {$dcSubject = $doc->subject;}
                            else { $dcSubject = null;}

                            if(isset($doc->{'proxy_dc_creator.def'})) {$dcCreator = $doc->{'proxy_dc_creator.def'};}
                            elseif(isset($doc->{'proxy_dc_creator.en'})) {$dcCreator = $doc->{'proxy_dc_creator.en'};}
                            elseif(isset($doc->proxy_dc_creator)) {$dcCreator = $doc->proxy_dc_creator;}
                            elseif(isset($doc->CREATOR)) {$dcCreator = $doc->CREATOR;}
                            else { $dcCreator = null;}

                            if(isset($doc->proxy_dc_title)) {$title = $doc->proxy_dc_title[0];} else { $title = null;}

                            if(isset($doc->provider_aggregation_edm_dataProvider)) {$dataProvider = $doc->provider_aggregation_edm_dataProvider[0];} else { $dataProvider = null;}

                            if(isset($doc->YEAR)) {$when = $doc->YEAR;} else { $when = null;}

                            if(isset($doc->proxy_dcterms_spatial)) {$where = $doc->proxy_dcterms_spatial;} else { $where = null;}

                            $returnList[] = [urlencode($data[$c]), urlencode(json_encode($dcType)), urlencode(json_encode($dcSubject)), urlencode(json_encode($dcCreator)), urlencode($title), urlencode($dataProvider), urlencode(json_encode($when)), urlencode(json_encode($where))];
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
