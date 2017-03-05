<?php

namespace DSG\AdministrationBundle\Controller;

use DSG\HomeBundle\Service\CsvResponse;
use DSG\ModelBundle\Entity\EuropeanaItem;
use DSG\ModelBundle\Entity\EuropeanaItemsSession;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BabelNetDataSetController extends Controller
{
    public function queryAction(Request $request)
    {
        set_time_limit(0);
        $form = $this->createFormBuilder()
            ->add('urlFile', UrlType::class, array('mapped' => false, 'required' => true))
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $urlFile = $form->get('urlFile')->getData();
            $dateTime = new \DateTime();
            $timestamp = $dateTime->getTimestamp();

            $returnList = $this->query($urlFile, $timestamp);
            //return new Response($returnList);

            $response = new CsvResponse($returnList, 200, explode(', ', 'URI'));
            $response->setFilename("data.csv");
            return $response;
        }

        return $this->render('DSGAdministrationBundle:BabelNetDataSet:query.html.twig', array('form' => $form->createView()));
    }

    protected function query($urlFile, $timestamp)
    {
        set_time_limit(0);
        $em = $this->getDoctrine()->getManager();
        $europeanaItemsSession = new EuropeanaItemsSession();
        $em->persist($europeanaItemsSession);
        $em->flush();
        $returnList = array();

        $row = 1;
        if (($handle = fopen($urlFile, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $num = count($data);
                $row++;
                for ($c=0; $c < $num; $c++) {
                    //$data[$c] = query
                    //return $this->queryEuropeana($data[$c]);
                    $europeanaIds = $this->queryEuropeana($data[$c], $timestamp)[0];
                    if($europeanaIds != null) {
                        foreach($europeanaIds as $europeanaId) {
                            $europeanaItem = new EuropeanaItem();
                            $europeanaItem->setURI($europeanaId);
                            $europeanaItem->setEuropeanaItemsSession($europeanaItemsSession);
                            $em->persist($europeanaItem);
                            $em->flush();
                            $returnList[] = [$europeanaId];
                        }
                    }
                }
            }
            fclose($handle);
        }

        return $returnList;
    }


    public function queryEuropeana($query, $timestamp)
    {
        //http://sol1.eanadev.org:9191/solr/search_1_shard1_replica2/select?q=*&qf=LANGUAGE:fr&fl=europeana_id&rows=300000&cursorMark=*&wt=json&start=0&sort=europeana_id asc
        set_time_limit(0);
        $this->get('Buzz')->getClient()->setTimeout(0);
        $timestart=microtime(true);

        $id = null;

        $parts = parse_url($query);
        parse_str($parts['query'], $attributes);
        //return json_encode([$query, intval($attributes['start'])]);
        $ids = $this->queryCursorEuropeana($query, $timestamp);

        $timeend=microtime(true);
        $time=$timeend-$timestart;
        $timeQuery = number_format($time, 3);

        return [$ids, $timeQuery];
    }

    public function queryCursorEuropeana($query, $timestamp)
    {
        //http://sol1.eanadev.org:9191/solr/search_1_shard1_replica2/select?q=edm_datasetName:2058621_Ag_EU_LoCloud_NRA&fl=europeana_id&rows=1&start=0&wt=json&sort=europeana_id+asc
        $parts = parse_url($query);
        parse_str($parts['query'], $attributes);
        $q = $attributes['q'];
        $fl = $attributes['fl'];
        $rows = $attributes['rows'];
        $start = $attributes['start'];
        $wt = $attributes['wt'];
        $sort = $attributes['sort'];

        $counter = 0;

        $cursorMark = "*";
        $ids = null;
        $numFound = 0;
        $countRow = 0;

        $data = array(
            'q' => $q,
            'fl' => $fl,
            'rows' => 0,
            'wt' => $wt,
            'sort' => $sort);
        $queryResponse = @file_get_contents('http://sol1.eanadev.org:9191/solr/search_1_shard1_replica2/select?'.http_build_query($data));
        if($queryResponse !== FALSE) {
            $content = json_decode($queryResponse);
            if (isset($content->response->numFound)) {
                $numFound = $content->response->numFound;
            }
        }

        $interval = $numFound/$rows;

        while($countRow <= intval($rows)) {
            $data = array(
                'q' => $q,
                'fl' => $fl,
                'rows' => 26000,
                'cursorMark' => $cursorMark,
                'wt' => $wt,
                'sort' => $sort);
            $queryResponse = @file_get_contents('http://sol1.eanadev.org:9191/solr/search_1_shard1_replica2/select?'.http_build_query($data));

            /* LOGGING */
            $fs = new \Symfony\Component\Filesystem\Filesystem();
            if(!$fs->exists('data/dsg/'.$timestamp.'-logs.text')) {
                $fs->dumpFile('data/dsg/'.$timestamp.'-logs.text', '');
            }
            $contentLog = file_get_contents('data/dsg/'.$timestamp.'-logs.text');
            $contentLog .= urldecode('http://sol1.eanadev.org:9191/solr/search_1_shard1_replica2/select?'.http_build_query($data))."\n";
            try {$fs->dumpFile('data/dsg/'.$timestamp.'-logs.text', $contentLog);}
            catch(IOException $e) {}
            /* -- END LOGGING */

            if($queryResponse !== FALSE) {
                $content = json_decode($queryResponse);
                $counter += 26000;
                if (isset($content->response->docs)) {
                    $cursorMark = $content->nextCursorMark;
                    if($counter == 26000) {
                        $ids[] = $content->response->docs[0]->europeana_id;
                    }
                    if ($counter >= ($interval*($countRow+1))) {
                        if(isset($content->response->docs[(($interval*($countRow+1))-($counter-26000))])) {
                            $ids[] = $content->response->docs[(($interval*($countRow+1))-($counter-26000))]->europeana_id;
                        }
                        $countRow++;
                        if($countRow >= intval($rows)) {break;}
                    }
                }
            }
        }

        return $ids;
    }
}
