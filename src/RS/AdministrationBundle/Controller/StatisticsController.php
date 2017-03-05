<?php

namespace RS\AdministrationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Request;

class StatisticsController extends Controller
{
    public function indexAction(Request $request)
    {
        set_time_limit(0);
        $form = $this->createFormBuilder()
            ->add('urlFile', UrlType::class,  array('mapped' => false, 'required' => true))
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $urlFile = $form->get('urlFile')->getData();

            $timestamp = strstr(basename($urlFile), '-', true);
            return $this->loadAction('data/'.$timestamp.'/'.basename($urlFile));
        }

        $this->get('session')->getFlashBag()->add('notice', 'Step 5: use similarItems-top5of1000.json' );
        return $this->render('RSAdministrationBundle:Query:query.html.twig', array(
            'form' => $form->createView(),
            'previous' => 'rs_administration_statistics_query'));
    }

    public function loadAction($path)
    {
        if(!isset($_GET['profile']) OR empty($_GET['profile'])) {
            $profile = 'light';
        } else {
            $profile = $_GET['profile'];
        }

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

        $pathEnglish = '../web/data/similarItems-top5Of1000-current-english.json';
        $contentEnglish = file_get_contents($pathEnglish);
        $jsonEnglish = json_decode($contentEnglish, true);

        $pathNatural = '../web/data/similarItems-top5Of1000-current-natural.json';
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
