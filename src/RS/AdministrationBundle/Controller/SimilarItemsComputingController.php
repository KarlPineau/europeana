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
            $response = new CsvResponse($returnList, 200, ['query', 'item', 'numFound', '1', '2', '3', '4', '5']);
            $response->setFilename($timestamp.'-'.$date.'-top5relatedItems.csv');
            $fs = new \Symfony\Component\Filesystem\Filesystem();
            $fs->dumpFile('data/'.$timestamp.'/'.$timestamp.'-'.$date.'-top5relatedItems.csv', $response);
            $fs->copy('../src/RS/AdministrationBundle/Controller/SimilarItemsComputingController.php', 'data/'.$timestamp.'/SimilarItemsComputingController.php');

            return $response;
        }

        $this->get('session')->getFlashBag()->add('notice', 'Step 2: use parametersOf1000.csv' );
        return $this->render('RSAdministrationBundle:Query:query.html.twig', array(
            'form' => $form->createView(),
            'next' => 'rs_administration_score_query',
            'previous' => 'rs_administration_current_query'));
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

                if(isset($data[1]) AND isset($data[2]) AND isset($data[3]) AND isset($data[4]) AND isset($data[5]) AND isset($data[6]) AND isset($data[7])) {
                    $europeana_id = $data[0];
                    $dcType = $data[1];
                    $dcSubject = $data[2];
                    $dcCreator = $data[3];
                    $title = $data[4];
                    $dataProvider = $data[5];
                    $when = $data[6];
                    $where = $data[7];

                    /* LOGGING */
                    $fs = new \Symfony\Component\Filesystem\Filesystem();
                    if(!$fs->exists('data/'.$timestamp.'/'.$timestamp.'-'.$date.'-logs-query.text')) {
                        $fs->dumpFile('data/'.$timestamp.'/'.$timestamp.'-'.$date.'-logs-query.text', '');
                    }
                    $content = file_get_contents('data/'.$timestamp.'/'.$timestamp.'-'.$date.'-logs-query.text');
                    $content .= $europeana_id.'<>'.$dcType.'<>'.$dcSubject.'<>'.$dcCreator.'<>'.$title.'<>'.$dataProvider."\n";
                    try {$fs->dumpFile('data/'.$timestamp.'/'.$timestamp.'-'.$date.'-logs-query.text', $content);}
                    catch(IOException $e) {}
                    /* -- END LOGGING */


                    $defaultTier = $this->getTier($europeana_id)['level'];
                    $reboucle = 0;
                    $reboucleBase = 0;
                    $init = 5;

                    $listRelatedItems = [];
                    $response = $this->queryEuropeana($europeana_id, $dcType, $dcSubject, $dcCreator, $title, $dataProvider, $date, $timestamp, $when, $where, 5, 0);
                    $content = $response[0];
                    $query = $response[1];
                    $listRelatedItems[] = $query;
                    $listRelatedItems[] = $europeana_id;
                    if(isset($content->response->numFound) AND !empty($content->response->numFound)) {
                        if(is_int($content->response->numFound)) {
                            $listRelatedItems[] = strval($content->response->numFound);
                        } else {$listRelatedItems[] = strval(intval($content->response->numFound));}
                    } else {$listRelatedItems[] = "0";}


                    if (isset($content->response->docs)) {
                        foreach ($content->response->docs as $doc) {
                            $docTier = $this->getTier($doc->europeana_id)['level'];
                            /* LOGGING */
                            $fs = new \Symfony\Component\Filesystem\Filesystem();
                            if(!$fs->exists('data/'.$timestamp.'/'.$timestamp.'-'.$date.'-logs-tier.text')) {
                                $fs->dumpFile('data/' . $timestamp . '/' . $timestamp . '-' . $date . '-logs-tier.text', '');
                            }
                            $content = file_get_contents('data/'.$timestamp.'/'.$timestamp.'-'.$date.'-logs-tier.text');
                            $content .= $doc->europeana_id.' > '.$docTier.' | '.$defaultTier."\n";
                            try {$fs->dumpFile('data/'.$timestamp.'/'.$timestamp.'-'.$date.'-logs-tier.text', $content);}
                            catch(IOException $e) {}
                            /* -- END LOGGING */

                            if($docTier > $defaultTier) {
                                $listRelatedItems[] = $doc->europeana_id;
                            } else {
                                $reboucle++;
                            }
                        }
                    }

                    while($reboucle > $reboucleBase) {
                        $reboucleBase = $reboucle;
                        $init += $reboucle;

                        $response = $this->queryEuropeana($europeana_id, $dcType, $dcSubject, $dcCreator, $title, $dataProvider, $date, $timestamp, $when, $where, $reboucle, $init);
                        $content = $response[0];

                        if (isset($content->response->docs)) {
                            foreach ($content->response->docs as $doc) {
                                if(count($listRelatedItems) >= 8) {break;}

                                $docTier = $this->getTier($doc->europeana_id)['level'];
                                /* LOGGING */
                                $fs = new \Symfony\Component\Filesystem\Filesystem();
                                if(!$fs->exists('data/'.$timestamp.'/'.$timestamp.'-'.$date.'-logs-tier.text')) {
                                    $fs->dumpFile('data/' . $timestamp . '/' . $timestamp . '-' . $date . '-logs-tier.text', '');
                                }
                                $content = file_get_contents('data/'.$timestamp.'/'.$timestamp.'-'.$date.'-logs-tier.text');
                                $content .= $doc->europeana_id.' > '.$docTier.' | '.$defaultTier."\n";
                                try {$fs->dumpFile('data/'.$timestamp.'/'.$timestamp.'-'.$date.'-logs-tier.text', $content);}
                                catch(IOException $e) {}
                                /* -- END LOGGING */

                                if($docTier > $defaultTier) {
                                    $listRelatedItems[] = $doc->europeana_id;
                                } else {
                                    $reboucle++;
                                }
                            }
                        }
                        if(count($listRelatedItems) >= 8) {break;}
                    }

                    $returnList[$europeana_id] = $listRelatedItems;
                }
            }
            fclose($handle);
        }

        return $returnList;
    }


    public function queryEuropeana($europeana_id, $dcType, $dcSubject, $dcCreator, $title, $dataProvider, $date, $timestamp, $when, $where, $rows, $start)
    {
        set_time_limit(0);

        $dcType = (json_decode(urldecode($dcType)));
        $dcSubject = (json_decode(urldecode($dcSubject)));
        $dcCreator = (json_decode(urldecode($dcCreator)));
        $when = (json_decode(urldecode($when)));
        $where = (json_decode(urldecode($where)));

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
        /*if($when != null) {
            $whenArray = array();
            foreach($when as $whenUnity) {
                $whenArray[] = '"'.urldecode($whenUnity).'"';
            }
            $arrayQuery[] = 'when:('.implode(' AND ', $whenArray).')^0.3';
        }
        if($where != null) {
            $whereArray = array();
            foreach($where as $whereUnity) {
                $whereArray[] = '"'.urldecode($whereUnity).'"';
            }
            $arrayQuery[] = 'where:('.implode(' AND ', $whereArray).')^0.3';
        }*/
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
                        'rows'=>$rows,
                        'wt'=>'json',
                        'fl' => 'europeana_id',
                        'start' => $start);


        $timestart=microtime(true);
        $queryResponse = @file_get_contents('http://sol1.eanadev.org:9191/solr/search_1_shard1_replica2/select?'.http_build_query($data));
        $timeend=microtime(true);
        $time=$timeend-$timestart;
        $timeQuery = number_format($time, 3);

        /* LOGGING */
        $fs = new \Symfony\Component\Filesystem\Filesystem();
        if(!$fs->exists('data/'.$timestamp.'/'.$timestamp.'-'.$date.'-logs.text')) {
            $fs->dumpFile('data/' . $timestamp . '/' . $timestamp . '-' . $date . '-logs.text', '');
        }
        $content = file_get_contents('data/'.$timestamp.'/'.$timestamp.'-'.$date.'-logs.text');
        if($queryResponse !== FALSE) {$content .= json_decode($queryResponse)->response->numFound." > ".$rows." > ".$start.' > ';} else {$content .= 'Error'." > ";}
        $content .= $query."\n";
        try {$fs->dumpFile('data/'.$timestamp.'/'.$timestamp.'-'.$date.'-logs.text', $content);}
        catch(IOException $e) {}
        /* -- END LOGGING */

        return [json_decode($queryResponse), $query, $timeQuery];
    }

    public function getTier($europeana_id)
    {
        set_time_limit(0);

        $queries = [
            //Images
            ['code' => ['type' => 'Image', 'level' => 5], 'query' => 'http://www.europeana.eu/api/v2/search.json?query=europeana_id:"'.$europeana_id.'"&qf=IMAGE_SIZE:extra_large&reusability=open&thumbnail=true&rows=0&start=1&profile=facets&wskey=api2demo'],
            ['code' => ['type' => 'Image', 'level' => 4], 'query' => 'http://www.europeana.eu/api/v2/search.json?query=europeana_id:"'.$europeana_id.'"&qf=IMAGE_SIZE:large&qf=IMAGE_SIZE:extra_large&reusability=open&thumbnail=true&rows=0&start=1&profile=facets&wskey=api2demo'],
            ['code' => ['type' => 'Image', 'level' => 3], 'query' => 'http://www.europeana.eu/api/v2/search.json?query=europeana_id:"'.$europeana_id.'"&qf=IMAGE_SIZE:large&qf=IMAGE_SIZE:extra_large&reusability=open,restricted&thumbnail=true&rows=0&start=1&profile=facets&wskey=api2demo'],
            ['code' => ['type' => 'Image', 'level' => 2], 'query' => 'http://www.europeana.eu/api/v2/search.json?query=europeana_id:"'.$europeana_id.'"&qf=IMAGE_SIZE:medium&qf=IMAGE_SIZE:large&qf=IMAGE_SIZE:extra_large&thumbnail=true&rows=0&start=1&profile=facets&wskey=api2demo'],
            ['code' => ['type' => 'Image', 'level' => 1], 'query' => 'http://www.europeana.eu/api/v2/search.json?query=europeana_id:"'.$europeana_id.'"&qf=TYPE:IMAGE&thumbnail=true&rows=0&start=1&profile=facets&wskey=api2demo'],
            ['code' => ['type' => 'Image', 'level' => 0], 'query' => 'http://www.europeana.eu/api/v2/search.json?query=europeana_id:"'.$europeana_id.'"&qf=TYPE:IMAGE&rows=0&start=1&profile=facets&wskey=api2demo'],
            //Text
            ['code' => ['type' => 'Text', 'level' => 4], 'query' => 'http://www.europeana.eu/api/v2/search.json?query=europeana_id:"'.$europeana_id.'"&qf=TEXT_FULLTEXT:true&reusability=open&rows=0&start=1&profile=facets&wskey=api2demo'],
            ['code' => ['type' => 'Text', 'level' => 3], 'query' => 'http://www.europeana.eu/api/v2/search.json?query=europeana_id:"'.$europeana_id.'"&qf=TEXT_FULLTEXT:true&reusability=open,restricted&rows=0&start=1&profile=facets&wskey=api2demo'],
            ['code' => ['type' => 'Text', 'level' => 2], 'query' => 'http://www.europeana.eu/api/v2/search.json?query=europeana_id:"'.$europeana_id.'"&qf=TEXT_FULLTEXT:true&rows=0&start=1&profile=facets&wskey=api2demo'],
            ['code' => ['type' => 'Text', 'level' => 1], 'query' => 'http://www.europeana.eu/api/v2/search.json?query=europeana_id:"'.$europeana_id.'"&qf=TYPE:TEXT&rows=0&start=1&profile=facets&wskey=api2demo'],
            ['code' => ['type' => 'Text', 'level' => 0], 'query' => 'http://www.europeana.eu/api/v2/search.json?query=europeana_id:"'.$europeana_id.'"&qf=TYPE:TEXT&rows=0&start=1&profile=facets&wskey=api2demo'],
            //Sound :
            ['code' => ['type' => 'Sound', 'level' => 5], 'query' => 'http://www.europeana.eu/api/v2/search.json?query=europeana_id:"'.$europeana_id.'"&qf=SOUND_DURATION:very_short&qf=SOUND_DURATION:short&qf=SOUND_DURATION:medium&qf=SOUND_DURATION:long&qf=SOUND_HQ:true&reusability=open&rows=0&start=1&profile=facets&wskey=api2demo'],
            ['code' => ['type' => 'Sound', 'level' => 4], 'query' => 'http://www.europeana.eu/api/v2/search.json?query=europeana_id:"'.$europeana_id.'"&qf=SOUND_DURATION:very_short&qf=SOUND_DURATION:short&qf=SOUND_DURATION:medium&qf=SOUND_DURATION:long&reusability=open&rows=0&start=1&profile=facets&wskey=api2demo'],
            ['code' => ['type' => 'Sound', 'level' => 3], 'query' => 'http://www.europeana.eu/api/v2/search.json?query=europeana_id:"'.$europeana_id.'"&qf=SOUND_DURATION:very_short&qf=SOUND_DURATION:short&qf=SOUND_DURATION:medium&qf=SOUND_DURATION:long&reusability=open,restricted&rows=0&start=1&profile=facets&wskey=api2demo'],
            ['code' => ['type' => 'Sound', 'level' => 2], 'query' => 'http://www.europeana.eu/api/v2/search.json?query=europeana_id:"'.$europeana_id.'"&qf=SOUND_DURATION:very_short&qf=SOUND_DURATION:short&qf=SOUND_DURATION:medium&qf=SOUND_DURATION:long&rows=0&start=1&profile=facets&wskey=api2demo'],
            ['code' => ['type' => 'Sound', 'level' => 1], 'query' => 'http://www.europeana.eu/api/v2/search.json?query=europeana_id:"'.$europeana_id.'"&qf=TYPE:SOUND&rows=0&start=1&profile=facets&wskey=api2demo'],
            ['code' => ['type' => 'Sound', 'level' => 0], 'query' => 'http://www.europeana.eu/api/v2/search.json?query=europeana_id:"'.$europeana_id.'"&qf=TYPE:SOUND&rows=0&start=1&profile=facets&wskey=api2demo'],
            //Video:
            ['code' => ['type' => 'Video', 'level' => 5], 'query' => 'http://www.europeana.eu/api/v2/search.json?query=europeana_id:"'.$europeana_id.'"&qf=VIDEO_DURATION:short&qf=VIDEO_DURATION:medium&qf=VIDEO_DURATION:long&qf=VIDEO_HD:true&reusability=open&rows=0&start=1&profile=facets&wskey=api2demo'],
            ['code' => ['type' => 'Video', 'level' => 4], 'query' => 'http://www.europeana.eu/api/v2/search.json?query=europeana_id:"'.$europeana_id.'"&qf=VIDEO_DURATION:short&qf=VIDEO_DURATION:medium&qf=VIDEO_DURATION:long&reusability=open&rows=0&start=1&profile=facets&wskey=api2demo'],
            ['code' => ['type' => 'Video', 'level' => 3], 'query' => 'http://www.europeana.eu/api/v2/search.json?query=europeana_id:"'.$europeana_id.'"&qf=VIDEO_DURATION:short&qf=VIDEO_DURATION:medium&qf=VIDEO_DURATION:long&reusability=open,restricted&rows=0&start=1&profile=facets&wskey=api2demo'],
            ['code' => ['type' => 'Video', 'level' => 2], 'query' => 'http://www.europeana.eu/api/v2/search.json?query=europeana_id:"'.$europeana_id.'"&qf=VIDEO_DURATION:short&qf=VIDEO_DURATION:medium&qf=VIDEO_DURATION:long&rows=0&start=1&profile=facets&wskey=api2demo'],
            ['code' => ['type' => 'Video', 'level' => 1], 'query' => 'http://www.europeana.eu/api/v2/search.json?query=europeana_id:"'.$europeana_id.'"&qf=TYPE:VIDEO&rows=0&start=1&profile=facets&wskey=api2demo'],
            ['code' => ['type' => 'Video', 'level' => 0], 'query' => 'http://www.europeana.eu/api/v2/search.json?query=europeana_id:"'.$europeana_id.'"&qf=TYPE:VIDEO&rows=0&start=1&profile=facets&wskey=api2demo'],
            //3D:
            ['code' => ['type' => '3D', 'level' => 4], 'query' => 'http://www.europeana.eu/api/v2/search.json?query=europeana_id:"'.$europeana_id.'"&qf=TYPE:3D&media=true&reusability=open&rows=0&start=1&profile=facets&wskey=api2demo'],
            ['code' => ['type' => '3D', 'level' => 3], 'query' => 'http://www.europeana.eu/api/v2/search.json?query=europeana_id:"'.$europeana_id.'"&qf=TYPE:3D&reusability=open,restricted&rows=0&start=1&profile=facets&wskey=api2demo'],
            ['code' => ['type' => '3D', 'level' => 2], 'query' => 'http://www.europeana.eu/api/v2/search.json?query=europeana_id:"'.$europeana_id.'"&qf=TYPE:3D&rows=0&start=1&profile=facets&wskey=api2demo'],
            ['code' => ['type' => '3D', 'level' => 1], 'query' => 'http://www.europeana.eu/api/v2/search.json?query=europeana_id:"'.$europeana_id.'"&qf=TYPE:3D&rows=0&start=1&profile=facets&wskey=api2demo'],
            ['code' => ['type' => '3D', 'level' => 0], 'query' => 'http://www.europeana.eu/api/v2/search.json?query=europeana_id:"'.$europeana_id.'"&qf=TYPE:3D&rows=0&start=1&profile=facets&wskey=api2demo'],
        ];

        foreach($queries as $query) {
            $response = $this->queryTierEuropeana($query['query']);
            if($response[0]->totalResults == 1) {
                return $query['code'];
                break;
            }
        }
        return 0;

    }

    public function queryTierEuropeana($query)
    {
        set_time_limit(0);
        $this->get('Buzz')->getClient()->setTimeout(0);
        //$queryResponse = $this->get('Buzz')->get('http://www.europeana.eu/api/v2/search.json?query=europeana_id:"/9200365/BibliographicResource_1000054834489"&qf=TYPE:3D&media=true&reusability=open&rows=0&start=1&profile=facets&wskey=api2demo');
        $queryResponse = @file_get_contents($query);

        return [json_decode($queryResponse)];
    }
}
