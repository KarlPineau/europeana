<?php

namespace DSG\AdministrationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TestController extends Controller
{
    public function indexAction()
    {
        $dbhost = '127.0.0.1:45942';
        $dbname = 'evaluation';

        // Connect to test database
        $m = new \MongoClient("mongodb://$dbhost");
        $db = new \MongoDB($m, $dbname);

        // select the collection
        $collection = $db->shows();

        // pull a cursor query
        $cursor = $collection->find();

        return $this->render('DSGAdministrationBundle:Test:index.html.twig', array('cursor' => $cursor));
    }
}
