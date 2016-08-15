<?php

namespace NER\ModelBundle\Service;

use Doctrine\ORM\EntityManager;
use Gedmo\References\Mapping\Event\Adapter\ORM;

class nameEntityRecognition
{
    protected $em;
    protected $buzz;
    protected $key;

    public function __construct(EntityManager $EntityManager, $buzz)
    {
        $this->em = $EntityManager;
        $this->buzz = $buzz;
        $this->key = '90c5d1ce-94a6-4f2b-bc04-6cb46a526b3c'; //9f566346-b8e8-4b41-b4b3-f094ff68f495
    }

    public function nameEntityRecognition($id, $field)
    {
        $entity = $this->entity->find('one', array('id' => $id), 'large');
        if ($entity === null) { throw $this->createNotFoundException('Entité : [id='.$id.'] inexistante.'); }

        // BabelNet Query:
        $response = $this->buzz->get('https://babelnet.io/v3/getSynsetIds?word='.urlencode($this->entity->get($field, $entity)).'&langs=FR&key='.$this->key);

        $objectResponse = json_decode($response->getContent());
        if(isset($objectResponse->message) AND $objectResponse->message == 'Your key is not valid or the daily requests limit has been reached. Please visit http://babelnet.org.') {
            return false;
        } else {
            // Log BabelNet Result:
            $nameEntityRecognition = new \NER\ModelBundle\Entity\NameEntityRecognition();
            $nameEntityRecognition->setField($field);
            $nameEntityRecognition->setIsVerified(false);
            $nameEntityRecognition->setLiteral($this->entity->get($field, $entity));
            $nameEntityRecognition->setUsedIn($entity);
            $nameEntityRecognition->setSynsets($response->getContent());

            // BabelNet Query for specific synset:
            $returnEntities = array();
            foreach($objectResponse as $item) {
                array_push($returnEntities, [json_decode($this->buzz->get('https://babelnet.io/v3/getSynset?id='.$item->id.'&key='.$this->key)->getContent()), $item->id]);
            }

            foreach($returnEntities as $entityWithSenseArray) {
                $this->getSense($entityWithSenseArray, $nameEntityRecognition);
            }

            $this->em->persist($nameEntityRecognition);
            $this->em->flush();

            return true;
        }

    }

    public function getSense($entityWithSenseArray, $nameEntityRecognition)
    {
        $errorStatementWikidataMissing = true;

        $entityWithSense = $entityWithSenseArray[0];
        // We are going to look at the Wikidata id:
        if (property_exists($entityWithSense, 'senses')) {
            foreach ($entityWithSense->senses as $sense) {
                if ($sense->source == "WIKIDATA") {
                    $errorStatementWikidataMissing = false;
                    $key = preg_replace('/#1/', '', $sense->sensekey);
                    $responseWikidata = json_decode($this->buzz->get('https://www.wikidata.org/w/api.php?action=wbgetentities&ids=' . urlencode($key) . '&format=json')->getContent());

                    if(isset($responseWikidata->entities)) {
                        foreach ($responseWikidata->entities as $wikidataEntity) {
                            foreach ($wikidataEntity->claims as $claim) {
                                foreach ($claim as $claimInstance) {
                                    //Cette condition récupère toutes les entités instance de (P31) human (Q5)
                                    if ($nameEntityRecognition->getField() == 'auteur' AND $claimInstance->mainsnak->property == "P31" AND (
                                        $claimInstance->mainsnak->datavalue->value->{'numeric-id'} == 5 OR //human
                                        $claimInstance->mainsnak->datavalue->value->{'numeric-id'} == 380342 OR //manufactory
                                        $claimInstance->mainsnak->datavalue->value->{'numeric-id'} == 83405 //factory
                                        )) {
                                        $nameEntityRecognition->setUri('https://www.wikidata.org/entity/' . $key);
                                        $nameEntityRecognition->setSynset($entityWithSenseArray[1]);
                                        $nameEntityRecognition->setErrorStatement(null);
                                    } elseif ($nameEntityRecognition->getField() == 'lieuDeConservation' AND $claimInstance->mainsnak->property == "P31"
                                        AND ($claimInstance->mainsnak->datavalue->value->{'numeric-id'} == 33506 OR //museum
                                            $claimInstance->mainsnak->datavalue->value->{'numeric-id'} == 207694 OR //art museum
                                            $claimInstance->mainsnak->datavalue->value->{'numeric-id'} == 17431399 OR //national museum
                                            $claimInstance->mainsnak->datavalue->value->{'numeric-id'} == 5193377 OR //cultural institution
                                            $claimInstance->mainsnak->datavalue->value->{'numeric-id'} == 2668072 OR //collection
                                            $claimInstance->mainsnak->datavalue->value->{'numeric-id'} == 811979 OR //architectural structure
                                            $claimInstance->mainsnak->datavalue->value->{'numeric-id'} == 839954 OR //archaeological site
                                            $claimInstance->mainsnak->datavalue->value->{'numeric-id'} == 15661340 OR //ancient city 515
                                            $claimInstance->mainsnak->datavalue->value->{'numeric-id'} == 515 OR //city -> AMBIGUITY FOR AMERICAN CITIES ! (Naples, Italy <-> Naples, Floride)
                                            $claimInstance->mainsnak->datavalue->value->{'numeric-id'} == 484170 OR //commune of France
                                            $claimInstance->mainsnak->datavalue->value->{'numeric-id'} == 262166 OR //municipality of Germany
                                            $claimInstance->mainsnak->datavalue->value->{'numeric-id'} == 6256 OR //country
                                            $claimInstance->mainsnak->datavalue->value->{'numeric-id'} == 570116 // tourist attraction
                                        )) {
                                        $nameEntityRecognition->setUri('https://www.wikidata.org/entity/' . $key);
                                        $nameEntityRecognition->setSynset($entityWithSenseArray[1]);
                                        $nameEntityRecognition->setErrorStatement(null);
                                    } else {
                                        $nameEntityRecognition->setErrorStatement('Invalide value for "instanceOf" Wikidata property');
                                    }
                                }
                            }
                        }
                    } else {
                        $nameEntityRecognition->setErrorStatement('Wikidata query returns 0 result');
                    }
                }
            }
        } else {
            $nameEntityRecognition->setErrorStatement('No sense in BabelNet entity');
        }

        if($errorStatementWikidataMissing == true) {
            $nameEntityRecognition->setErrorStatement('No wikidata link');
        }

        $this->em->persist($nameEntityRecognition);
        $this->em->flush();
    }
}
