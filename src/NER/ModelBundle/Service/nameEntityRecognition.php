<?php

namespace NER\ModelBundle\Service;

use Doctrine\ORM\EntityManager;
use Gedmo\References\Mapping\Event\Adapter\ORM;
use NER\ModelBundle\Entity\Synset;

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

    public function getBabelNet($field)
    {
        // BabelNet Query:
        $query = 'https://babelnet.io/v3/getSynsetIds?word='.urlencode($field->getLiteral());
        if($field->getLanguage() != null) {$query .= '&langs='.ucwords($field->getLanguage());} else {$query .= '&langs=EN';}
        $query .= '&key='.$this->key;

        $response = $this->buzz->get($query);

        $objectResponse = json_decode($response->getContent());
        if(isset($objectResponse->message) AND $objectResponse->message == 'Your key is not valid or the daily requests limit has been reached. Please visit http://babelnet.org.') {
            return false;
        } else {
            // BabelNet Query for specific synset:
            if(count($objectResponse) > 0) {
                foreach ($objectResponse as $item) {
                    $synset = new Synset();
                    $synset->setType('BabelNet');
                    $synset->setField($field);

                    if (isset($item->id)) {
                        $synset->setSynset($item->id);
                    } else {
                        $synset->setErrorStatement('No synset id');
                    }
                    $this->em->persist($synset);
                }
            }
            $this->em->flush();

            return true;
        }

    }

    public function getBabelFy($field)
    {
        // BabelNet Query:
        $query = 'https://babelfy.io/v1/disambiguate?text='.urlencode($field->getLiteral());
        if($field->getLanguage() != null) {$query .= '&lang='.ucwords($field->getLanguage());} else {$query .= '&lang=EN';}
        $query .= '&key='.$this->key;

        $response = $this->buzz->get($query);

        $objectResponse = json_decode($response->getContent());
        if(isset($objectResponse->message) AND $objectResponse->message == 'Your key is not valid or the daily requests limit has been reached. Please visit http://babelnet.org.') {
            return false;
        } else {
            // BabelNet Query for specific synset:
            if(count($objectResponse) > 0) {
                foreach ($objectResponse as $item) {
                    $synset = new Synset();
                    $synset->setType('BabelFy');
                    $synset->setField($field);

                    if (isset($item->babelSynsetID)) {
                        $synset->setSynset($item->babelSynsetID);
                    } else {
                        $synset->setErrorStatement('No synset id');
                    }
                    $this->em->persist($synset);
                }
            }
            $this->em->flush();

            return true;
        }

    }

    public function getListProperties()
    {
        return [
            'dcCreator' => 'dcCreator',
            'dcPublisher' => 'dcPublisher',
            'dcSubject' => 'dcSubject',
            'dcTitle' => 'dcTitle',
            'dcType' => 'dcType',
            'dctermsMedium' => 'dctermsMedium',
            'dctermsProvenance' => 'dctermsProvenance',
            'dcDescription' => 'dcDescription',
            'dcSource' => 'dcSource',
            'dctermsIsPartOf' => 'dctermsIsPartOf',
        ];
    }

    public function countEntitiesByUploadFile($uploadFile)
    {
        return count($this->em->getRepository('NERModelBundle:Entity')->findByUploadFile($uploadFile));
    }

    public function countFieldsByEntity($entity)
    {
        return count($this->em->getRepository('NERModelBundle:Field')->findByEntity($entity));
    }

    public function countSynsetsByField($field)
    {
        return count($this->em->getRepository('NERModelBundle:Synset')->findByField($field));
    }

    public function countForURI($entity)
    {
        $count = 0;
        foreach($this->getFieldsByEntity($entity) as $field) {
            $countSynset = $this->countSynsetsByField($field);
            if($countSynset == 0) {
                $count += 1;
            } else {
                $count += $countSynset;
            }
        }
        return $count;
    }

    public function countSynsetsByUploadFile($uploadFile)
    {
        $count = 0;
        foreach($this->getEntityByUploadFile($uploadFile) as $entity) {
            $count += $this->countForURI($entity);
        }
        return $count;
    }

    public function getEntityByUploadFile($uploadFile)
    {
        return $this->em->getRepository('NERModelBundle:Entity')->findByUploadFile($uploadFile);
    }

    public function getFieldsByEntity($entity)
    {
        return $this->em->getRepository('NERModelBundle:Field')->findByEntity($entity);
    }

    public function getSynsetsByField($field)
    {
        return $this->em->getRepository('NERModelBundle:Synset')->findByField($field);
    }
}
