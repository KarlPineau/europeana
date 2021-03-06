<?php

namespace RS\AdministrationBundle\Controller;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StatisticsQueryController extends Controller
{
    /*
     * AIM of this controller:
     * RETURN STATISTICS ABOUT SIMILAR ITEMS COMPUTED IN SimilarItemsComputingController
     */

    public function queryAction(Request $request)
    {
        set_time_limit(0);
        $form = $this->createFormBuilder()
            ->add('urlFile', UrlType::class,  array('mapped' => false, 'required' => true))
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $urlFile = $form->get('urlFile')->getData();

            $timestamp = strstr(basename($urlFile), '-', true);
            $returnList = $this->query($urlFile, $timestamp);
            $fileURL = $this->register($returnList, $timestamp);

            $fs = new \Symfony\Component\Filesystem\Filesystem();
            $fs->copy('../src/RS/AdministrationBundle/Controller/StatisticsQueryController.php', 'data/'.$timestamp.'/StatisticsQueryController.php');

            $headers = array(
                'Content-Type'     => 'application/json',
                'Content-Disposition' => 'inline; filename="'.$fileURL.'"');
            return new Response(readfile($fileURL), 200, $headers);
        }

        $this->get('session')->getFlashBag()->add('notice', 'Step 4: use top5relatedItems.csv' );
        return $this->render('RSAdministrationBundle:Query:query.html.twig', array(
            'form' => $form->createView(),
            'previous' => 'rs_administration_score_query',
            'next' => 'rs_administration_statistics_index'));
    }

    protected function query($urlFile, $timestamp)
    {
        $date = date('Y-m-d-h-i-s');
        set_time_limit(0);
        $returnList = array();

        $row = 1;
        if (($handle = fopen($urlFile, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
                $row++;
                if(isset($data[1])) {
                    $values = array();

                    foreach ($data as $key => $entity) {
                        /*
                         * KEY value :
                         * 0 = query
                         * 1 = referenceItem
                         * 2 = numFound
                         * 3 = item1
                         * 4 = item2
                         * 5 = item3
                         * 6=  item4
                         * 7=  item5
                         */


                        if ($key > 2) {
                                $queryReturned = $this->queryEuropeana($entity, $date, $timestamp);
                                $values[] = [
                                    'europeana_id' => $entity,
                                    'edm_datasetName' => $queryReturned['edm_datasetName'],
                                    'europeana_aggregation_edm_language' => $queryReturned['europeana_aggregation_edm_language'],
                                    'europeana_aggregation_edm_country' => $queryReturned['europeana_aggregation_edm_country'],
                                    'provider_aggregation_edm_isShownBy' => $queryReturned['provider_aggregation_edm_isShownBy'],

                                ];
                        }
                    }

                    if(isset($data[2])) {$numFound = $data[2];} else {$numFound = 0;}
                    $queryReturned = $this->queryEuropeana(urldecode($data[1]), $date, $timestamp);
                    $returnList[] = [
                        'containerItem' => [
                            'europeana_id' => urldecode($data[1]),
                            'edm_datasetName' => $queryReturned['edm_datasetName'],
                            'europeana_aggregation_edm_language' => $queryReturned['europeana_aggregation_edm_language'],
                            'europeana_aggregation_edm_country' => $queryReturned['europeana_aggregation_edm_country'],
                            'provider_aggregation_edm_isShownBy' => $queryReturned['provider_aggregation_edm_isShownBy']
                        ],
                        'similarItems' => $values,
                        'numFound' => intval($numFound)
                    ];
                }
            }
            fclose($handle);
        }

        return $returnList;
    }

    public function queryEuropeana($europeana_id, $date, $timestamp)
    {
        set_time_limit(0);
        $this->get('Buzz')->getClient()->setTimeout(0);
        $timestart=microtime(true);

        $response = ['edm_datasetName' => null, 'europeana_aggregation_edm_language' => null, 'europeana_aggregation_edm_country' => null, 'provider_aggregation_edm_isShownBy' => null];
        $query = 'http://sol1.eanadev.org:9191/solr/search_1_shard1_replica2/select?q=europeana_id:"'.urlencode($europeana_id).'"&fl=europeana_aggregation_edm_country'.urlencode(',').'europeana_aggregation_edm_language'.urlencode(',').'edm_datasetName'.urlencode(',').'provider_aggregation_edm_isShownBy&rows=1&wt=json';
        $queryResponse = $this->get('Buzz')->get($query);
        $responseContainer = json_decode($queryResponse->getContent());

        if (isset($responseContainer->response)) {
            /* LOGGING */
            $fs = new \Symfony\Component\Filesystem\Filesystem();
            if(!$fs->exists('data/'.$timestamp.'/'.$timestamp.'-'.$date.'-logs-stat.text')) {
                $fs->dumpFile('data/'.$timestamp.'/'.$timestamp.'-'.$date.'-logs-stat.text', '');
            }
            $content = file_get_contents('data/'.$timestamp.'/'.$timestamp.'-'.$date.'-logs-stat.text');
            $content .= json_encode($responseContainer->response)."\n";
            try {$fs->dumpFile('data/'.$timestamp.'/'.$timestamp.'-'.$date.'-logs-stat.text', $content);}
            catch(IOException $e) {}
            /* -- END LOGGING */

            foreach ($responseContainer->response->docs as $doc) {
                if (isset($doc->edm_datasetName[0])) {
                    $response['edm_datasetName'] = $doc->edm_datasetName[0];
                }
                if (isset($doc->europeana_aggregation_edm_language[0])) {
                    $response['europeana_aggregation_edm_language'] = $doc->europeana_aggregation_edm_language[0];
                }
                if (isset($doc->europeana_aggregation_edm_country[0])) {
                    $response['europeana_aggregation_edm_country'] = $doc->europeana_aggregation_edm_country[0];
                }
                if (isset($doc->provider_aggregation_edm_isShownBy[0])) {
                    $response['provider_aggregation_edm_isShownBy'] = $doc->provider_aggregation_edm_isShownBy[0];
                }
            }
        }

        $timeend=microtime(true);
        $time=$timeend-$timestart;
        $timeQuery = number_format($time, 3);
        return $response;
    }

    public function register($returnList, $timestamp)
    {
        set_time_limit(0);
        $fs = new \Symfony\Component\Filesystem\Filesystem();

        try {
            $fs->dumpFile('data/'.$timestamp.'/'.$timestamp.'-'.date('Y-m-d-h-i-s').'-similarItems-top5of1000.json', json_encode($returnList));
            return 'data/'.$timestamp.'/'.$timestamp.'-'.date('Y-m-d-h-i-s').'-similarItems-top5of1000.json';
        }
        catch(IOException $e) {}
    }

}
