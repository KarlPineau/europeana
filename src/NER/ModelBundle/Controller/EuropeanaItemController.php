<?php

namespace NER\ModelBundle\Controller;

use NER\ModelBundle\Entity\EuropeanaItem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class EuropeanaItemController extends Controller
{
    public function harvestEuropeanaItemAction($field, $lang)
    {
        set_time_limit(0);
        $count = 0;
        $cursor = '*';
        $listOfProperties = ['dcCreator', 'dcContributor'];

        while($count < 10000)
        {
            $responseArray = $this->queryEuropeana($lang, $cursor);
            $response = json_decode($responseArray[0]->getContent());
            $count = $this->registerEuropeanaItem($response->items, $count, $listOfProperties);

            if(isset($response->nextCursor)) {
                $cursor = $response->nextCursor;
            } else {
                break;
            }
        }

        return $this->render('NERModelBundle:EuropeanaItem:index.html.twig', array(
            'items' => $response,
            'timeQuery' => $responseArray[1]
        ));
        //return $this->redirectToRoute('ner_home_europeanaItem_index');
    }

    private function queryEuropeana($lang, $cursor)
    {
        $buzz = $this->container->get('buzz');
        $timestart=microtime(true);
        $queryResponse = $buzz->get('https://www.europeana.eu/api/v2/search.json?wskey=api2demo&profile=rich&query=*&qf=LANGUAGE:'.$lang.'&cursor='.urlencode($cursor));
        $timeend=microtime(true);
        $time=$timeend-$timestart;
        $timeQuery = number_format($time, 3);

        return [$queryResponse, $timeQuery];
    }

    private function registerEuropeanaItem($items, $count, $listOfProperties)
    {
        $em = $this->getDoctrine()->getManager();
        foreach($items as $item)
        {
            if($count <= 10000)
            {
                $registrationBoolean = false;
                foreach($listOfProperties as $property) {
                    if(isset($item->{$property}[0])) {$registrationBoolean = true;}
                }

                if($registrationBoolean == true AND $em->getRepository('NERModelBundle:EuropeanaItem')->findOneBy(array('URI' => $item->link)) == null) {
                    $europeanaItem = new EuropeanaItem();
                    $europeanaItem->setURI($item->link);
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
