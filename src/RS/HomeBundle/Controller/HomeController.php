<?php

namespace RS\HomeBundle\Controller;

use RS\ModelBundle\Entity\RecommenderResults;
use RS\ModelBundle\Entity\RecommenderSearch;
use RS\ModelBundle\Form\RecommenderSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends Controller
{
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $recommenderSearch = new RecommenderSearch();
        $form = $this->createForm(RecommenderSearchType::class, $recommenderSearch);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($recommenderSearch);
            $em->persist($recommenderSearch->getRecommenderParameters());
            $em->flush();

            return $this->redirectToRoute('rs_home_home_recommender', array('recommenderSearch_id' => $recommenderSearch->getId()));
        }

        return $this->render('RSHomeBundle:Home:index.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function recommenderAction($recommenderSearch_id)
    {
        set_time_limit(0);
        $em = $this->getDoctrine()->getManager();

        $recommenderSearch = $em->getRepository('RSModelBundle:RecommenderSearch')->findOneById($recommenderSearch_id);
        if($recommenderSearch === null) {throw $this->createNotFoundException('This id is not callable.');}
        $parameters = $recommenderSearch->getRecommenderParameters();

        $queryItemInformation = $this->get('rs_home.recommenderQuery')->getQuery('http://www.europeana.eu/api/v2/record/'.$recommenderSearch->getItem().'.json?profile=rich&wskey=api2demo');
        $timeQuery = $queryItemInformation[1];
        $entity = json_decode($queryItemInformation[0]->getContent());
        $mainProxy = $entity->object->proxies[0];

        $itemInformation = $this->get('rs_home.recommenderQuery')->getInformation($mainProxy, $entity);

        $queryRelatedItemsInformation = $this->get('rs_home.recommenderQuery')->getRecommenderQuery($parameters, $recommenderSearch, $itemInformation);

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
        $recommenderResult->setRecommenderSearch($recommenderSearch);
        $recommenderResult->setResults($queryRelatedItemsInformation['relatedItems']);
        $em->persist($recommenderResult);
        $em->flush();

        return $this->render('RSHomeBundle:Home:recommender.html.twig', array('entities' => $entities));
    }
}
