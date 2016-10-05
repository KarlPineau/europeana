<?php

namespace RS\AdministrationBundle\Controller;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Request;

class StatisticsController extends Controller
{
    public function viewAction()
    {
        if(!isset($_GET['profile']) OR empty($_GET['profile'])) {
            $profile = 'light';
        } else {
            $profile = $_GET['profile'];
        }

        $path = $this->get('kernel')->getRootDir() . '../../web/data/similarItems-top5Of1000-current.json';
        $content = file_get_contents($path);
        $json = json_decode($content, true);

        return $this->render('RSAdministrationBundle:Statistics:index.html.twig', array('returnList' => $json, 'profile' => $profile));
    }

    public function queryAction(Request $request)
    {
        set_time_limit(0);
        $form = $this->createFormBuilder()
            ->add('urlFile', UrlType::class,  array('mapped' => false, 'required' => true))
            ->add('field',   TextType::class, array('mapped' => false, 'required' => true))
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $urlFile = $form->get('urlFile')->getData();
            $field = $form->get('urlFile')->getData();

            $returnList = $this->query($urlFile, $field);
            $this->register($returnList);

            return $this->render('RSAdministrationBundle:Statistics:index.html.twig', array('returnList' => $returnList));
        }

        return $this->render('RSAdministrationBundle:Statistics:query.html.twig', array('form' => $form->createView()));
    }

    protected function query($urlFile, $field)
    {
        set_time_limit(0);
        $returnList = array();

        $row = 1;
        if (($handle = fopen($urlFile, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row++;
                if(isset($data[1])) {
                    $values = array();
                    foreach ($data as $key => $entity) {
                        /*if($key == 0) {$query = $entity;}
                        elseif($key == 1) {$referenceItem = $entity;}
                        elseif($key == 2) {$item1 = $entity;}
                        elseif($key == 3) {$item2 = $entity;}
                        elseif($key == 4) {$item3 = $entity;}
                        elseif($key == 5) {$item4 = $entity;}
                        elseif($key == 6) {$item5 = $entity;}*/

                        if ($key > 1) {
                            $queryReturned = $this->queryEuropeana($entity, $field);
                            $values[] = ['europeana_id' => $entity, 'edm_datasetName' => $queryReturned[0], 'provider_aggregation_edm_isShownBy' => $queryReturned[1]];
                        }
                    }

                    $returnList[] = ['containerItem' => ['europeana_id' => urldecode($data[1]), 'edm_datasetName' => $this->queryEuropeana(urldecode($data[1]), $field)[0], 'provider_aggregation_edm_isShownBy' => $this->queryEuropeana(urldecode($data[1]), $field)[1]], 'similarItems' => $values];
                }
            }
            fclose($handle);
        }

        return $returnList;
    }

    public function queryEuropeana($europeana_id, $field)
    {
        //http://sol7.eanadev.org:9191/solr/search/search?q=*&qf=LANGUAGE:fr&fl=europeana_id&rows=300000&cursorMark=*&wt=json&start=0&sort=europeana_id asc
        set_time_limit(0);
        $this->get('Buzz')->getClient()->setTimeout(0);

        $timestart=microtime(true);

        $query = 'http://sol7.eanadev.org:9191/solr/search/search?q=europeana_id:"'.urlencode($europeana_id).'"&fl=edm_datasetName'.urlencode(',').'provider_aggregation_edm_isShownBy&rows=1&wt=json';

        $queryResponse = $this->get('Buzz')->get($query);
        $responseContainer = json_decode($queryResponse->getContent());

        $response = 'NaN';
        if(isset($responseContainer->response)) {
            foreach($responseContainer->response->docs as $doc) {
                $response = [$doc->edm_datasetName[0]];
                if(isset($doc->provider_aggregation_edm_isShownBy[0])) {$response[] = $doc->provider_aggregation_edm_isShownBy[0];} else {$response[] = null;}
            }
        }

        $timeend=microtime(true);
        $time=$timeend-$timestart;
        $timeQuery = number_format($time, 3);

        return $response;
    }

    public function register($returnList)
    {
        set_time_limit(0);
        $fs = new \Symfony\Component\Filesystem\Filesystem();

        try {
            $fs->dumpFile('data/similarItems-top5Of1000-current.json', json_encode($returnList));
        }
        catch(IOException $e) {
        }
    }
}
