<?php

namespace DSG\HomeBundle\Service;

use Doctrine\ORM\EntityManager;
use DSG\ModelBundle\Entity\EuropeanaItem;
use Symfony\Component\HttpFoundation\Response;

class DSGenerator
{
    protected $em;
    protected $buzz;

    public function __construct(EntityManager $EntityManager, $buzz)
    {
        $this->em = $EntityManager;
        $this->buzz = $buzz;
    }

    public function generator($europeanaItemsSession)
    {
        set_time_limit(0);
        $limit = $europeanaItemsSession->getNumberOfItems();
        $query = $europeanaItemsSession->getQuery();
        $queryFacet = $europeanaItemsSession->getQF();
        $fl = null;

        $dispatched = false;
        $dispatchedCounter = 0;

        if(count($this->em->getRepository('DSGModelBundle:EuropeanaItem')->findBy(array('europeanaItemsSession' => $europeanaItemsSession))) > 0) {
            $count = count($this->em->getRepository('DSGModelBundle:EuropeanaItem')->findBy(array('europeanaItemsSession' => $europeanaItemsSession)));
            $dispatched = $europeanaItemsSession->getDispatcher();
            if($europeanaItemsSession->getQueryCursor() == null) {$cursor = '*'; $europeanaItemsSession->setQueryCursor('*'); $this->em->persist($europeanaItemsSession); $this->em->flush();}
            else {$cursor = $europeanaItemsSession->getQueryCursor();}
        } else {
            $count = 0;
            $cursor = '*';
        }

        while($count <= $limit)
        {
            $responseArray = $this->queryEuropeana($query, $queryFacet, $fl, $cursor);
            if(isset(json_decode($responseArray[0]->getContent())->response)) {
                $response = json_decode($responseArray[0]->getContent())->response;

                if ($count == 0) {
                    $resultsNumber = $response->numFound;
                    $europeanaItemsSession->setResultsNumber($resultsNumber);
                    if ($resultsNumber > $limit) {
                        $dispatched = floor($resultsNumber / $limit);
                        $europeanaItemsSession->setDispatcher($dispatched);
                    }
                    $this->em->persist($europeanaItemsSession);
                    $this->em->flush();
                }

                $registerReturn = $this->registerEuropeanaItem($response->docs, $count, $europeanaItemsSession, $limit, $dispatched, $dispatchedCounter);
                $count = $registerReturn[0];
                $dispatchedCounter = $registerReturn[1];

                if(isset(json_decode($responseArray[0]->getContent())->nextCursorMark)) {
                    $cursor = json_decode($responseArray[0]->getContent())->nextCursorMark;
                    $europeanaItemsSession->setQueryCursor($cursor);
                    $this->em->persist($europeanaItemsSession);
                    $this->em->flush();
                } else {
                    $europeanaItemsSession->setQueryCursor(null);
                    $this->em->persist($europeanaItemsSession);
                    $this->em->flush();
                    break;
                }
            } else {
                return new Response($responseArray[0]->getContent());
            }

            $count = count($this->em->getRepository('DSGModelBundle:EuropeanaItem')->findBy(array('europeanaItemsSession' => $europeanaItemsSession)));
            if($count > $limit) {break;}
        }

        if($count >= ($limit-1)) {return true;}
        else {return false;}
    }

    public function queryEuropeana($query, $queryFacet=null, $fl=null, $cursor='*')
    {
        //http://sol7.eanadev.org:9191/solr/search/search?q=*&qf=LANGUAGE:fr&fl=europeana_id&rows=300000&cursorMark=*&wt=json&start=0&sort=europeana_id asc
        set_time_limit(0);
        $this->buzz->getClient()->setTimeout(0);

        $timestart=microtime(true);
        $query = 'http://sol7.eanadev.org:9191/solr/search/search?q='.urlencode($query);
        if($queryFacet != null) {$query .= '&fq='.urlencode($queryFacet);}
        if($fl == null) {$query .= '&fl=europeana_id';}
        $query .= '&cursorMark='.urlencode($cursor).'&rows=150000&wt=json&start=0&sort='.urlencode('europeana_id asc');
        $queryResponse = $this->buzz->get($query);

        $timeend=microtime(true);
        $time=$timeend-$timestart;
        $timeQuery = number_format($time, 3);

        return [$queryResponse, $timeQuery];
    }

    public function queryCountEuropeana($query, $queryFacet)
    {
        //http://sol7.eanadev.org:9191/solr/search/search?q=*&qf=LANGUAGE:fr&fl=europeana_id&wt=json&start=0&sort=europeana_id asc
        set_time_limit(0);
        $this->buzz->getClient()->setTimeout(0);

        $timestart=microtime(true);
        $query = 'http://sol7.eanadev.org:9191/solr/search/search?q='.urlencode($query);
        if($queryFacet != null) {$query .= '&fq='.urlencode($queryFacet);}
        $query .= '&fl=europeana_id&wt=json&rows=1&start=0&sort='.urlencode('europeana_id asc');
        $queryResponse = $this->buzz->get($query);

        $timeend=microtime(true);
        $time=$timeend-$timestart;
        $timeQuery = number_format($time, 3);

        return [json_decode($queryResponse->getContent())->response->numFound, $timeQuery];
    }

    private function registerEuropeanaItem($items, $count, $europeanaItemsSession, $limit, $dispatched, $dispatchedCounter)
    {
        if ($count <= $limit) {
            $items = (array) $items;
            foreach($items as $item) {
                if($dispatched == $dispatchedCounter) {
                    if ($this->em->getRepository('DSGModelBundle:EuropeanaItem')->findOneBy(array('URI' => $item->europeana_id, 'europeanaItemsSession' => $europeanaItemsSession)) == null) {
                        $europeanaItem = new EuropeanaItem();
                        $europeanaItem->setURI($item->europeana_id);
                        $europeanaItem->setEuropeanaItemsSession($europeanaItemsSession);
                        $this->em->persist($europeanaItem);
                        $this->em->flush();
                        $count++;
                        $dispatchedCounter=0;
                    }
                } else {
                    $dispatchedCounter++;
                }
            }
        }
        return [$count, $dispatchedCounter];
    }
}
