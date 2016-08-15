<?php

namespace RS\HomeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller
{
    public function indexAction()
    {
        set_time_limit(0);
        $buzz = $this->container->get('buzz');
        $entities = array();
        foreach($this->get('rs_model.dataset')->getDataSet() as $item) {
            $timestart=microtime(true);
            $response = $buzz->get('http://www.europeana.eu/api/v2/record/'.$item.'.json?profile=rich&wskey=api2demo');
            $timeend=microtime(true);
            $time=$timeend-$timestart;
            $timeQuery = number_format($time, 3);

            $entity = json_decode($response->getContent());
            $mainProxy = $entity->object->proxies[0];

            /* dcType */
            $dcType = '';
            if(isset($mainProxy->dcType->def)) {$dcType = $mainProxy->dcType->def[0];}

            /* dcSubject */
            $dcSubject = '';
            if(isset($mainProxy->dcSubject->def)) {$dcSubject = $mainProxy->dcSubject->def[0];}

            /* dcCreator */
            $dcCreator = '';
            if(isset($mainProxy->dcCreator->def)) {$dcCreator = $mainProxy->dcCreator->def[0];}

            /* title */
            $title = '';
            if(isset($entity->object->title)) {$title = $entity->object->title[0];}

            /* dataProvider */
            $dataProvider = '';
            if(isset($entity->object->aggregations[0]->edmDataProvider->def)) {$dataProvider = $entity->object->aggregations[0]->edmDataProvider->def[0];}

            $query = '';
            if($dcCreator != "") {$query .= 'who:"'.urlencode($dcCreator).'"';}
            if($dcSubject != "") {$query .= 'what:"'.urlencode($dcSubject).'"';}
            if($dcType != "") {$query .= 'what:"'.urlencode($dcType).'"';}
            $query .= '(NOT '.$item.')';

            $timestart2=microtime(true);
            $relatedItemsResponse = $buzz->get('https://www.europeana.eu/api/v2/search.json?wskey=api2demo&profile=rich&query='.$query);
            $timeend2=microtime(true);
            $time2=$timeend2-$timestart2;
            $timeRelatedItems = number_format($time2, 3);
            $relatedItems = json_decode($relatedItemsResponse->getContent());

            $entities[] = [
                'dcType' => $dcType,
                'dcSubject' => $dcSubject,
                'dcCreator' => $dcCreator,
                'title' => $title,
                'dataProvider' => $dataProvider,
                'entity' => $entity,
                'timeQuery' => $timeQuery,
                'relatedItems' => $relatedItems,
                'timeRelatedItems' => $timeRelatedItems];
        }


        //$response = $buzz->get('http://www.europeana.eu/api/v2/search.json?wskey=api2demo&query=*&qf=PROVIDER:%22Europeana+280%22&rows=1000&profile=rich');

        //https://github.com/europeana/europeana-blacklight/blob/develop/app/models/europeana/blacklight/document/more_like_this.rb
        /*
            { param: 'what', fields: ['proxies.dcType', 'proxies.dcSubject'], boost: 0.8 },
            { param: 'who', fields: 'proxies.dcCreator', boost: 0.5 },
            { param: 'title', fields: 'title', boost: 0.3 },
            { param: 'DATA_PROVIDER', fields: 'aggregations.edmDataProvider', boost: 0.2 }
        */


        return $this->render('RSHomeBundle:Home:index.html.twig', array('entities' => $entities));
    }
}
