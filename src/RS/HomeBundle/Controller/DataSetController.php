<?php

namespace RS\HomeBundle\Controller;

use RS\ModelBundle\Entity\RecommenderParameters;
use RS\ModelBundle\Entity\RecommenderResults;
use RS\ModelBundle\Form\RecommenderParametersType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DataSetController extends Controller
{
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $recommenderParameter = new RecommenderParameters();
        $form = $this->createForm(RecommenderParametersType::class, $recommenderParameter);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($recommenderParameter);
            $em->flush();

            return $this->redirectToRoute('rs_home_dataset_recommender', array('recommenderParameter_id' => $recommenderParameter->getId()));
        }

        return $this->render('RSHomeBundle:DataSet:index.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function recommenderAction($recommenderParameter_id)
    {
        set_time_limit(0);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 0); // the timeout in seconds

        $em = $this->getDoctrine()->getManager();

        $recommenderParameter = $em->getRepository('RSModelBundle:RecommenderParameters')->findOneById($recommenderParameter_id);
        if($recommenderParameter === null) {throw $this->createNotFoundException('This id is not callable.');}

        $entities = array();
        foreach($this->get('rs_model.dataset')->getDataSet() as $item) {
            if($item[0] == '/') {$queryItemInformation = $this->get('rs_home.recommenderQuery')->getQuery('http://www.europeana.eu/api/v2/record' . $item . '.json?profile=rich&wskey=api2demo');}
            else{$queryItemInformation = $this->get('rs_home.recommenderQuery')->getQuery('http://www.europeana.eu/api/v2/record/' . $item . '.json?profile=rich&wskey=api2demo');}
            $timeQuery = $queryItemInformation[1];
            $entity = json_decode($queryItemInformation[0]->getContent());
            $mainProxy = $entity->object->proxies[0];

            $itemInformation = $this->get('rs_home.recommenderQuery')->getInformation($mainProxy, $entity);

            $queryRelatedItemsInformation = $this->get('rs_home.recommenderQuery')->getRecommenderQuery($recommenderParameter, $item, $itemInformation);

            $entities[] = [
                'dcTypes' => $itemInformation['dcTypes'],
                'dcSubjects' => $itemInformation['dcSubjects'],
                'dcCreators' => $itemInformation['dcCreators'],
                'title' => $itemInformation['title'],
                'dataProvider' => $itemInformation['dataProvider'],
                'entity' => $entity,
                'timeQuery' => $timeQuery,
                'relatedItems' => $queryRelatedItemsInformation['relatedItems'],
                'timeRelatedItems' => $queryRelatedItemsInformation['timeRelatedItems']];

            $recommenderResult = new RecommenderResults();
            $recommenderResult->setItem($item);
            $recommenderResult->setResults($queryRelatedItemsInformation['relatedItems']);
            $em->persist($recommenderResult);
            $em->flush();

        }

        return $this->render('RSHomeBundle:DataSet:Recommender/recommender.html.twig', array(
            'entities' => $entities,
            'recommenderParameter' => $recommenderParameter
        ));
    }
}
