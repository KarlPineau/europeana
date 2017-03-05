<?php

namespace RS\AdministrationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Exception\IOException;

class AlgorithmExperimentController extends Controller
{
    public function generateJsonAction()
    {
        set_time_limit(0);

        $files = [
            "../web/data/1478613188/1478613188-2016-11-08-03-35-49-similarItems-top5of1000.json",
            "../web/data/1478621948/1478621948-2016-11-08-06-22-10-similarItems-top5of1000.json",
            "../web/data/1478683288/1478683288-2016-11-09-11-11-22-similarItems-top5of1000.json",
            "../web/data/1478686902/1478686902-2016-11-09-12-05-56-similarItems-top5of1000.json",
            "../web/data/1478695251/1478695251-2016-11-09-02-24-46-similarItems-top5of1000.json",
            "../web/data/1478776353/1478776353-2016-11-10-02-05-41-similarItems-top5of1000.json",
            "../web/data/1478789118/1478789118-2016-11-10-04-33-20-similarItems-top5of1000.json",
            "../web/data/1478792575/1478792575-2016-11-10-05-44-13-similarItems-top5of1000.json",
            "../web/data/1478853379/1478853379-2016-11-11-10-26-44-similarItems-top5of1000.json",
            "../web/data/1478860767/1478860767-2016-11-11-12-39-52-similarItems-top5of1000.json",
            "../web/data/1478867710/1478867710-2016-11-11-02-20-01-similarItems-top5of1000.json",
        ];

        $content = file_get_contents('../web/data/algorithmExperiment.json');
        $items = json_decode($content, true);

        $generatedArrayList = [];

        foreach($items as $itemContainer) {
            $item = $itemContainer['containerItem']['europeana_id'];
            $itemInfo = $this->queryEuropeana($item);
            $generatedArrayList[$item] = $itemInfo;

            foreach($files as $file) {
                $itemsFromFile = json_decode(file_get_contents($file), true);
                foreach($itemsFromFile as $itemFromFile) {
                    if($itemFromFile["containerItem"]["europeana_id"] == $item) {
                        foreach($itemFromFile["similarItems"] as $subItemFromFile) {
                            $europeanaId = $subItemFromFile["europeana_id"];
                            if(!array_key_exists($europeanaId, $generatedArrayList)) {
                                $subItemInfo = $this->queryEuropeana($europeanaId);
                                $generatedArrayList[$europeanaId] = $subItemInfo;
                            }
                        }
                    }
                }
            }
        }

        $this->register($generatedArrayList);

        $this->get('session')->getFlashBag()->add('notice', 'Fichier généré' );
        return $this->redirectToRoute("rs_usertest_home_index");
    }

    public function queryEuropeana($query)
    {
        set_time_limit(0);
        $this->get('Buzz')->getClient()->setTimeout(0);

        $timestart=microtime(true);
        $queryResponse = $this->get('Buzz')->get('http://sol1.eanadev.org:9191/solr/search_1_shard1_replica2/select?q=europeana_id:"'.$query.'"&rows=1&wt=json');

        $timeend=microtime(true);
        $time=$timeend-$timestart;
        $timeQuery = number_format($time, 3);

        return [json_decode($queryResponse->getContent())->response->docs[0], $timeQuery];
    }

    public function register($returnList)
    {
        set_time_limit(0);
        $fs = new \Symfony\Component\Filesystem\Filesystem();

        try {
            $fs->dumpFile('../web/data/generatedArrayList.json', json_encode($returnList));
        }
        catch(IOException $e) {}
    }
}
