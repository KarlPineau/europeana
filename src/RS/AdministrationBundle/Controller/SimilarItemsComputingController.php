<?php

namespace RS\AdministrationBundle\Controller;

use DSG\HomeBundle\Service\CsvResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SimilarItemsComputingController extends Controller
{
    /*
     * AIM of this controller:
     * RETURN THE LIST OF SIMILAR ITEMS FOR REFERENCE ITEM, BASED ON CURRENTCONTROLLER
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

            $timestamp = strstr(basename($urlFile), '-', true);
            $returnList = $this->query($urlFile, $timestamp);

            $date = date('Y-m-d-h-i-s');
            $response = new CsvResponse($returnList, 200, ['query', 'item', '1', '2', '3', '4', '5', 'numFound']);
            $response->setFilename($timestamp.'-'.$date.'-top5relatedItems.csv');
            $fs = new \Symfony\Component\Filesystem\Filesystem();
            $fs->copy($response, '../../web/'.$timestamp.'/'.$timestamp.'-'.$date.'-top5relatedItems.csv');
            $fs->copy('../../src/RS/AdministrationBundle/Controller/SimilarItemsComputingController.php', '../../web/'.$timestamp.'/SimilarItemsComputingController.php');

            return $response;
        }

        $this->get('session')->getFlashBag()->add('notice', 'Step 2: use parametersOf1000.csv' );
        return $this->render('RSAdministrationBundle:Query:query.html.twig', array(
            'form' => $form->createView(),
            'next' => 'rs_administration_statistics_query',
            'previous' => 'rs_administration_current_query'));
    }

    protected function query($urlFile, $timestamp)
    {
        $date = date('Y-m-d-h-i-s');
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
                    $path = $this->get('kernel')->getRootDir() . '../../web/data/'.$timestamp.'/'.$timestamp.'-'.$date.'-logs-query.text';
                    $content = file_get_contents($path);
                    $content .= $europeana_id.'<>'.$dcType.'<>'.$dcSubject.'<>'.$dcCreator.'<>'.$title.'<>'.$dataProvider."\n";
                    $fs = new \Symfony\Component\Filesystem\Filesystem();
                    try {$fs->dumpFile('data/'.$timestamp.'/'.$timestamp.'-'.$date.'-logs-query.text', $content);}
                    catch(IOException $e) {}
                    /* -- END LOGGING */


                    $listRelatedItems = [];
                    $response = $this->queryEuropeana($europeana_id, $dcType, $dcSubject, $dcCreator, $title, $dataProvider, $date, $timestamp);
                    $content = $response[0];
                    $query = $response[1];
                    $listRelatedItems[] = $query;
                    $listRelatedItems[] = $europeana_id;
                    if (isset($content->response->docs)) {
                        foreach ($content->response->docs as $doc) {
                            $listRelatedItems[] = $doc->europeana_id;
                        }
                        $listRelatedItems[] = $content->response->numFound;
                    }

                    $returnList[$europeana_id] = $listRelatedItems;
                }
            }
            fclose($handle);
        }

        return $returnList;
    }


    public function queryEuropeana($europeana_id, $dcType, $dcSubject, $dcCreator, $title, $dataProvider, $date, $timestamp)
    {
        set_time_limit(0);

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


        $timestart=microtime(true);
        $queryResponse = @file_get_contents('http://sol1.eanadev.org:9191/solr/search_1_shard1_replica2/select?'.http_build_query($data));
        $timeend=microtime(true);
        $time=$timeend-$timestart;
        $timeQuery = number_format($time, 3);

        /* LOGGING */
        $path = $this->get('kernel')->getRootDir() . '../../web/data/'.$timestamp.'/'.$timestamp.'-'.$date.'-logs.text';
        $content = file_get_contents($path);
        $content .= $query."\n";
        if($queryResponse !== FALSE) {$content .= json_decode($queryResponse)->response->numFound."\n";} else {$content .= 'Error'."\n";}
        $fs = new \Symfony\Component\Filesystem\Filesystem();
        try {$fs->dumpFile('data/'.$timestamp.'/'.$timestamp.'-'.$date.'-logs.text', $content);}
        catch(IOException $e) {}
        /* -- END LOGGING */

        return [json_decode($queryResponse), $query, $timeQuery];
    }
}
