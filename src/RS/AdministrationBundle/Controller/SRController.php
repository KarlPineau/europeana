<?php

namespace RS\AdministrationBundle\Controller;

use DSG\HomeBundle\Service\CsvResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SRController extends Controller
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

            $response = new CsvResponse($returnList, 200, ['query', 'item', '1', '2', '3', '4', '5']);
            $response->setFilename("top5relatedItems.csv");
            return $response;
        }

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

                    $listRelatedItems = [];
                    $response = $this->queryEuropeana($europeana_id, $dcType, $dcSubject, $dcCreator, $title, $dataProvider);
                    $content = $response[0];
                    $query = $response[1];
                    $listRelatedItems[] = $query;
                    $listRelatedItems[] = $europeana_id;
                    if (isset($content->response->docs)) {
                        foreach ($content->response->docs as $doc) {
                            $listRelatedItems[] = $doc->europeana_id;
                        }
                    }

                    $returnList[$europeana_id] = $listRelatedItems;
                }
            }
            fclose($handle);
        }

        return $returnList;
    }


    public function queryEuropeana($europeana_id, $dcType, $dcSubject, $dcCreator, $title, $dataProvider)
    {
        //http://sol7.eanadev.org:9191/solr/search/search?q=*&qf=LANGUAGE:fr&fl=europeana_id&rows=300000&cursorMark=*&wt=json&start=0&sort=europeana_id asc
        set_time_limit(0);
        $this->get('Buzz')->getClient()->setTimeout(0);

        $timestart=microtime(true);

        $count = 0;

        $dcType = urldecode(json_decode($dcType));
        $dcSubject = urldecode(json_decode($dcSubject));
        $dcCreator = urldecode(json_decode($dcCreator));

        $query = 'http://sol7.eanadev.org:9191/solr/search/search?q=(';
        if($dcType != null) {
            foreach($dcType as $dcTypeUnity) {
                if($count > 0) {$query .= urlencode(' OR ');}
                $query .= urlencode('what:"' . $dcTypeUnity . '"^0.8');
                $count++;
            }
        }
        if($dcSubject != null) {
            foreach($dcSubject as $dcSubjectUnity) {
                if ($count > 0) {$query .= urlencode(' OR ');}
                $query .= urlencode(' what:"' . $dcSubjectUnity . '"^0.8');
                $count++;
            }
        }
        if($dcCreator != null) {
            foreach($dcCreator as $dcCreatorUnity) {
                if ($count > 0) {$query .= urlencode(' OR ');}
                $query .= urlencode(' who:"' . $dcCreatorUnity . '"^0.5');
                $count++;
            }
        }
        if($title != null) {
            if ($count > 0) {$query .= urlencode(' OR ');}
            $query .= urlencode(' title:"'.$title.'"^0.3');
            $count++;
        }
        if($dataProvider != null) {
            if ($count > 0) {$query .= urlencode(' OR ');}
            $query .= urlencode(' DATA_PROVIDER:"'.$dataProvider.'"^0.2');
        }
        $query .= urlencode(') AND NOT europeana_id:"'.$europeana_id.'"');
        $query .= '&rows=5&wt=json';

        $queryResponse = $this->get('Buzz')->get($query);

        $timeend=microtime(true);
        $time=$timeend-$timestart;
        $timeQuery = number_format($time, 3);

        return [json_decode($queryResponse->getContent()), $query, $timeQuery];
    }
}
