<?php

namespace DSG\ModelBundle\Controller;

use DSG\ModelBundle\Entity\EuropeanaItem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DSGeneratorController extends Controller
{
    public function generatorAction($europeanaItemsSession_id)
    {
        $em = $this->getDoctrine()->getManager();
        $europeanaItemsSession = $em->getRepository('DSGModelBundle:EuropeanaItemsSession')->findOneById($europeanaItemsSession_id);

        set_time_limit(0);
        $limit = $europeanaItemsSession->getNumberOfItems();
        $query = $europeanaItemsSession->getQuery();
        $queryFacet = $europeanaItemsSession->getQF();

        $dispatched = false;
        $dispatchedCounter = 0;

        if(count($em->getRepository('DSGModelBundle:EuropeanaItem')->findBy(array('europeanaItemsSession' => $europeanaItemsSession))) > 0) {
            $count = count($em->getRepository('DSGModelBundle:EuropeanaItem')->findBy(array('europeanaItemsSession' => $europeanaItemsSession)));
            $resultsNumber = $europeanaItemsSession->getResultsNumber();
            $cursor = $europeanaItemsSession->getQueryCursor();

            if($resultsNumber > $limit) {
                $dispatched = floor($resultsNumber/$limit);
                if($dispatched > 50) {$dispatched == 50;}
            }
        } else {
            $count = 0;
            $cursor = '*';
            $resultsNumber = 0;
        }

        while($count <= $limit)
        {
            $responseArray = $this->queryEuropeana($query, $queryFacet, $cursor);
            $response = json_decode($responseArray[0]->getContent());

            if($count == 0) {
                $resultsNumber = $response->totalResults;
                $europeanaItemsSession->setResultsNumber($resultsNumber);
                $em->persist($europeanaItemsSession);
                $em->flush();
                if($resultsNumber > $limit) {
                    $dispatched = floor($resultsNumber/$limit);
                    if($dispatched > 50) {$dispatched == 50;}
                }
            }

            $registerReturn = $this->registerEuropeanaItem($response->items, $count, $europeanaItemsSession, $limit, $dispatched, $dispatchedCounter);

            $count = $registerReturn[0];
            $dispatchedCounter = $registerReturn[1];

            if(isset($response->nextCursor)) {
                $cursor = $response->nextCursor;
                $europeanaItemsSession->setQueryCursor($cursor);
                $em->persist($europeanaItemsSession);
                $em->flush();
            } else {
                $europeanaItemsSession->setQueryCursor(null);
                $em->persist($europeanaItemsSession);
                $em->flush();
                break;
            }
        }


        return $this->redirectToRoute('dsg_home_home_home');
    }

    private function queryEuropeana($query, $queryFacet, $cursor)
    {
        $buzz = $this->container->get('buzz');
        $timestart=microtime(true);
        $queryResponse = $buzz->get('https://www.europeana.eu/api/v2/search.json?wskey=api2demo&profile=rich&query='.urlencode($query).'&qf='.urlencode($queryFacet).'&cursor='.urlencode($cursor).'&rows=100');
        $timeend=microtime(true);
        $time=$timeend-$timestart;
        $timeQuery = number_format($time, 3);

        return [$queryResponse, $timeQuery];
    }

    private function registerEuropeanaItem($items, $count, $europeanaItemsSession, $limit, $dispatched, $dispatchedCounter)
    {
        $em = $this->getDoctrine()->getManager();
        foreach($items as $item)
        {
            if($dispatched == $dispatchedCounter)
            {
                if($count <= $limit)
                {
                    if($em->getRepository('DSGModelBundle:EuropeanaItem')->findOneBy(array('URI' => $item->link, 'europeanaItemsSession' => $europeanaItemsSession)) == null) {
                        $europeanaItem = new EuropeanaItem();
                        $europeanaItem->setURI($item->link);
                        $europeanaItem->setEuropeanaItemsSession($europeanaItemsSession);
                        $em->persist($europeanaItem);
                        $em->flush();
                    }

                    $count++;
                } else {
                    break;
                }
                $dispatchedCounter=0;
            } else {
                $dispatchedCounter++;
            }

        }

        return [$count, $dispatchedCounter];
    }
}
