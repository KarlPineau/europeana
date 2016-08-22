<?php

namespace RS\HomeBundle\Service;

use Doctrine\ORM\EntityManager;

class recommenderQuery
{
    protected $em;
    protected $buzz;

    public function __construct(EntityManager $EntityManager, $buzz)
    {
        $this->em = $EntityManager;
        $this->buzz = $buzz;
    }

    public function getInformation($mainProxy, $entity)
    {
        /* dcType */
        $dcTypes = array();
        if (isset($mainProxy->dcType)) {
            $dcTypes = $mainProxy->dcType;
        }

        /* dcSubject */
        $dcSubjects = array();
        if (isset($mainProxy->dcSubject)) {
            $dcSubjects = $mainProxy->dcSubject;
        }

        /* dcCreator */
        $dcCreators = array();
        if (isset($mainProxy->dcCreator)) {
            $dcCreators = $mainProxy->dcCreator;
        }

        /* title */
        $title = '';
        if (isset($entity->object->title)) {
            $title = $entity->object->title[0];
        }

        /* dataProvider */
        $dataProvider = '';
        if (isset($entity->object->aggregations[0]->edmDataProvider->def)) {
            $dataProvider = $entity->object->aggregations[0]->edmDataProvider->def[0];
        }

        return [
            'dcTypes' => $dcTypes,
            'dcSubjects' => $dcSubjects,
            'dcCreators' => $dcCreators,
            'dataProvider' => $dataProvider,
            'title' => $title
        ];
    }

    public function getRecommenderQuery($parameters, $recommenderSearch, $itemInformation)
    {
        //https://github.com/europeana/europeana-blacklight/blob/develop/app/models/europeana/blacklight/document/more_like_this.rb
        /*
            { param: 'what', fields: ['proxies.dcType', 'proxies.dcSubject'], boost: 0.8 },
            { param: 'who', fields: 'proxies.dcCreator', boost: 0.5 },
            { param: 'title', fields: 'title', boost: 0.3 },
            { param: 'DATA_PROVIDER', fields: 'aggregations.edmDataProvider', boost: 0.2 }
        */

        // Query Building:
        $query = '';
        if (count($itemInformation['dcCreators']) > 0 AND $parameters->getIsDcCreator() == true) {
            foreach ($itemInformation['dcCreators'] as $dcCreator) {
                $query .= urlencode('who:"'.$dcCreator[0].'"');
            }
        }

        if(count($itemInformation['dcSubjects']) > 0 AND $parameters->getIsDcSubject() == true) {
            foreach($itemInformation['dcSubjects'] as $dcSubject) {
                $query .= urlencode('what:"'.$dcSubject[0].'"');
            }
        }

        if(count($itemInformation['dcTypes']) > 0 AND $parameters->getIsDcType() == true) {
            foreach($itemInformation['dcTypes'] as $dcType) {
                $query .= urlencode('what:"'.$dcType[0].'"');
            }
        }

        if($parameters->getIsTitle() == true) {$query .= urlencode('title:"'.$itemInformation['title'].'"');}


        $query .= urlencode('NOT europeana_id:"/'.$recommenderSearch->getItem().'"');

        $relatedItemsInformation = $this->getQuery('http://www.europeana.eu/api/v2/search.json?wskey=api2demo&profile=rich&query='.$query);
        $timeRelatedItems = $relatedItemsInformation[1];
        $relatedItems = json_decode($relatedItemsInformation[0]->getContent());

        return [
            'relatedItems' => $relatedItems,
            'timeRelatedItems' => $timeRelatedItems
        ];
    }

    public function getQuery($query)
    {
        $timeStart = microtime(true);
        $response = $this->buzz->get($query);
        $timeEnd = microtime(true);
        $time = $timeEnd - $timeStart;
        $timeQuery = number_format($time, 3);

        return [$response, $timeQuery];
    }
}
