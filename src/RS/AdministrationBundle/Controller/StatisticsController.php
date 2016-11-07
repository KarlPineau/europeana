<?php

namespace RS\AdministrationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class StatisticsController extends Controller
{
    public function indexAction()
    {
        if(!isset($_GET['profile']) OR empty($_GET['profile'])) {
            $profile = 'light';
        } else {
            $profile = $_GET['profile'];
        }

        $path = $this->get('kernel')->getRootDir() . '../../web/data/similarItems-top5Of1000-current.json';
        $content = file_get_contents($path);
        $json = json_decode($content, true);

        return $this->render('RSAdministrationBundle:Statistics:index.html.twig', array('returnList' => $json, 'profile' => $profile));
    }

    public function compareAction()
    {
        set_time_limit(0);
        if(!isset($_GET['profile']) OR empty($_GET['profile'])) {
            $profile = 'light';
        } else {
            $profile = $_GET['profile'];
        }

        $pathEnglish = $this->get('kernel')->getRootDir() . '../../web/data/similarItems-top5Of1000-current-english.json';
        $contentEnglish = file_get_contents($pathEnglish);
        $jsonEnglish = json_decode($contentEnglish, true);

        $pathNatural = $this->get('kernel')->getRootDir() . '../../web/data/similarItems-top5Of1000-current-natural.json';
        $contentNatural = file_get_contents($pathNatural);
        $jsonNatural = json_decode($contentNatural, true);

        $return = array();
        foreach($jsonEnglish as $item) {
            $id = $item['containerItem']['europeana_id'];
            $return[$id] = ['containerItem' => $item['containerItem'], 'numFoundEnglish' => $item['numFound'], 'numFoundNatural' => 0];
        }

        foreach($jsonNatural as $item) {
            if (array_key_exists($item['containerItem']['europeana_id'], $return)) {
                $return[$item['containerItem']['europeana_id']]['numFoundNatural'] = $item['numFound'];
            }
        }

        return $this->render('RSAdministrationBundle:Statistics:compare.html.twig', array('returnList' => $return, 'profile' => $profile));
    }
}
