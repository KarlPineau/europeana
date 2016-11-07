<?php

namespace RS\AdministrationBundle\Controller;

use DSG\HomeBundle\Service\CsvResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CountController extends Controller
{
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

            $response = new CsvResponse($returnList, 200, ['query', 'item', 'count']);
            $response->setFilename("queryItemCountResults.csv");
            return $response;
        }

        $this->get('session')->getFlashBag()->add('notice', 'use parametersOf1000.csv' );
        return $this->render('DSGAdministrationBundle:BabelNetDataSet:query.html.twig', array('form' => $form->createView()));
    }

    protected function query($urlFile)
    {
        set_time_limit(0);
        $returnList = array();

        $row = 1;
        if (($handle = fopen($urlFile, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row++;

                if(isset($data[1]) AND isset($data[2]) AND isset($data[3]) AND isset($data[4]) AND isset($data[5])) {
                    $europeana_id = $data[0];
                    $dcType = $data[1];
                    $dcSubject = $data[2];
                    $dcCreator = $data[3];
                    $title = $data[4];
                    $dataProvider = $data[5];

                    /* LOGGING */
                    $path = $this->get('kernel')->getRootDir() . '../../web/data-log/logs-query.text';
                    $content = file_get_contents($path);
                    $content .= $europeana_id.'<>'.$dcType.'<>'.$dcSubject.'<>'.$dcCreator.'<>'.$title.'<>'.$dataProvider."\n";
                    $fs = new \Symfony\Component\Filesystem\Filesystem();
                    try {$fs->dumpFile('data-log/logs-query.text', $content);}
                    catch(IOException $e) {}
                    /* -- END LOGGING */


                    $listRelatedItems = [];
                    $response = $this->queryEuropeana($europeana_id, $dcType, $dcSubject, $dcCreator, $title, $dataProvider);
                    $listRelatedItems['query'] = $response[1];
                    $listRelatedItems['europeana_id'] = $europeana_id;
                    $listRelatedItems['count'] = $response[0];
                    $returnList[$europeana_id] = $listRelatedItems;
                }
            }
            fclose($handle);
        }

        return $returnList;
    }

    public function queryEuropeana($europeana_id, $dcType, $dcSubject, $dcCreator, $title, $dataProvider)
    {
        //http://sol1.eanadev.org:9191/solr/search_1_shard1_replica2/select?q=*&qf=LANGUAGE:fr&fl=europeana_id&rows=300000&cursorMark=*&wt=json&start=0&sort=europeana_id asc
        set_time_limit(0);
        $this->get('Buzz')->getClient()->setTimeout(0);

        $timestart=microtime(true);

        $count = 0;

        $dcType = (json_decode(urldecode($dcType)));
        $dcSubject = (json_decode(urldecode($dcSubject)));
        $dcCreator = (json_decode(urldecode($dcCreator)));

        $arrayQuery = array();
        if($dcType != null) {
            $dcTypeArray = array();
            foreach($dcType as $dcTypeUnity) {
                $dcTypeArray[] = '"'.urldecode($dcTypeUnity).'"';
            }
            $arrayQuery[] = 'what:('.implode(' OR ', $dcTypeArray).')^0.8';
        }
        if($dcSubject != null) {
            $dcSubjectArray = array();
            foreach($dcSubject as $dcSubjectUnity) {
                $dcSubjectArray[] = '"'.urldecode($dcSubjectUnity).'"';
            }
            $arrayQuery[] = 'what:('.implode(' OR ', $dcSubjectArray).')^0.8';
        }
        if($dcCreator != null) {
            $dcCreatorArray = array();
            foreach($dcCreator as $dcCreatorUnity) {
                $dcCreatorArray[] = '"'.urldecode($dcCreatorUnity).'"';
            }
            $arrayQuery[] = 'who:('.implode(' OR ', $dcCreatorArray).')^0.5';
        }
        if($title != null) {
            $arrayQuery[] = ('title:("'.urldecode($title).'")^0.3');
        }
        if($dataProvider != null) {
            $arrayQuery[] = ('DATA_PROVIDER:("'.urldecode($dataProvider).'")^0.2');
        }

        $query = '(';
        $query .= implode(' OR ', $arrayQuery);
        $query .= (') AND NOT europeana_id:"'.urldecode($europeana_id).'"');

        $data = array(  'q'=> $query,
            'rows'=>'5',
            'wt'=>'json');

        //$queryResponse = $this->get('Buzz')->get('http://sol1.eanadev.org:9191/solr/search_1_shard1_replica2/select?'.http_build_query($data));
        $queryResponse = @file_get_contents('http://sol1.eanadev.org:9191/solr/search_1_shard1_replica2/select?'.http_build_query($data)); //'http://sol1.eanadev.org:9191/solr/search_1_shard1_replica2/select?q=europeana_id:"/9200365/BibliographicResource_1000054834474"&wt=json'

        $timeend=microtime(true);
        $time=$timeend-$timestart;
        $timeQuery = number_format($time, 3);

        /* LOGGING */
        if($queryResponse !== FALSE) {$content = json_decode($queryResponse)->response->numFound."\n";} else {$content = null;}
        /* -- END LOGGING */

        return [$content, $query];
    }
}
