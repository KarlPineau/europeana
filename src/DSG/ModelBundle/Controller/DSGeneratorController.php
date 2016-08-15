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
        $count = 0;
        $limit = $europeanaItemsSession->getNumberOfItems();
        $query = $europeanaItemsSession->getQuery();
        $queryFacet = $europeanaItemsSession->getQF();
        $cursor = '*';

        while($count < $limit)
        {
            $responseArray = $this->queryEuropeana($query, $queryFacet, $cursor);
            $response = json_decode($responseArray[0]->getContent());
            $count = $this->registerEuropeanaItem($response->items, $count, $europeanaItemsSession, $limit);

            if(isset($response->nextCursor)) {
                $cursor = $response->nextCursor;
            } else {
                break;
            }
        }

        /*return $this->render('NERModelBundle:EuropeanaItem:index.html.twig', array(
            'items' => $response,
            'timeQuery' => $responseArray[1]
        ));*/
        return $this->redirectToRoute('dsg_home_home_result', array('europeanaItemsSession_id' => $europeanaItemsSession_id));
    }

    private function queryEuropeana($query, $queryFacet, $cursor)
    {
        $buzz = $this->container->get('buzz');
        $timestart=microtime(true);
        $queryResponse = $buzz->get('https://www.europeana.eu/api/v2/search.json?wskey=api2demo&profile=rich&query='.urlencode($query).'&qf='.urlencode($queryFacet).'&cursor='.urlencode($cursor));
        $timeend=microtime(true);
        $time=$timeend-$timestart;
        $timeQuery = number_format($time, 3);

        return [$queryResponse, $timeQuery];
    }

    private function registerEuropeanaItem($items, $count, $europeanaItemsSession, $limit)
    {
        $em = $this->getDoctrine()->getManager();
        foreach($items as $item)
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
        }

        return $count;
    }
}
