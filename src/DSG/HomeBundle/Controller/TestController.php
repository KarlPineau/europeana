<?php

namespace DSG\HomeBundle\Controller;

use DSG\ModelBundle\Entity\EuropeanaItemsSession;
use DSG\ModelBundle\Form\EuropeanaItemsSessionType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use DSG\HomeBundle\Service\CsvResponse;
use Symfony\Component\HttpFoundation\Response;

class TestController extends Controller
{
    public function testAction()
    {
        set_time_limit(0);
        $query = $this->get('Buzz')->get('http://sol1.eanadev.org:9191/solr/search_1_shard1_replica2/select?q=napoleon&wt=json');
        $response = json_decode($query->getContent())->response->numFound;

        return new Response(json_encode($response));

    }
}
