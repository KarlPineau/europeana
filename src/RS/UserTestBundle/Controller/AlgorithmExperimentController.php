<?php

namespace RS\UserTestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AlgorithmExperimentController extends Controller
{
    public function indexAction()
    {
        $content = file_get_contents('../web/data/algorithmExperiment.json');
        $items = json_decode($content, true);

        $numberOfItems = count($items);
        $randomItemKey = rand(0, $numberOfItems-1);

        return $this->render('RSUserTestBundle:AlgorithmExperiment:index.html.twig', array('item' => $items[$randomItemKey]));
    }
}
