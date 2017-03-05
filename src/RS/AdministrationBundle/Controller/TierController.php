<?php

namespace RS\AdministrationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpFoundation\Response;

class TierController extends Controller
{
    public function getTierAction($europeana_id)
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
            $response = $this->queryEuropeana($query['query']);
            if($response[0]->totalResults == 1) {
                return new Response(json_encode($query['code']));
                break;
            }
        }
        return new Response(($europeana_id));

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
