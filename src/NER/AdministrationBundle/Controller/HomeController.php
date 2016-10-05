<?php

namespace NER\AdministrationBundle\Controller;

use NER\ModelBundle\Entity\Entity;
use NER\ModelBundle\Entity\Field;
use NER\ModelBundle\Entity\UploadFile;
use NER\ModelBundle\Form\UploadFileType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends Controller
{
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $uploadFile = new UploadFile();
        $form = $this->createForm(UploadFileType::class, $uploadFile);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $uploadFile->setFields(preg_replace('/\s/', '',$uploadFile->getFields()));
            $em->persist($uploadFile);
            $em->flush();

            return $this->redirectToRoute('ner_administration_home_process', array('uploadFile_id' => $uploadFile->getId()));
        }

        return $this->render('NERAdministrationBundle:Home:index.html.twig', array('form' => $form->createView()));
    }

    public function processAction($uploadFile_id)
    {
        set_time_limit(0);
        $em = $this->getDoctrine()->getManager();
        $uploadFile = $em->getRepository('NERModelBundle:UploadFile')->findOneById($uploadFile_id);

        if($uploadFile === null) {throw $this->createNotFoundException('UploadFile [id='.$uploadFile_id.'] not found.');}

        $count = 0;
        $limit = 100;
        $row = 1;


        if($uploadFile->getFields() != null) {
            $fields = explode(',',$uploadFile->getFields());
        } else {
            $fields = array();
        }

        $return = array();
        if (($handle = fopen($uploadFile->getUrlFile(), "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $num = count($data);
                $row++;
                for ($c=0; $c < $num; $c++) {
                    $responseItem = $this->getQuery($data[$c].'&profile=rich');
                    $item = json_decode($responseItem[0]->getContent());
                    if(isset($item->language)) {$language = $item->language[0];}
                    elseif(isset($item->object->language)) {$language = $item->object->language[0];}
                    else {$language = null;}

                    $entity = new Entity();
                    $entity->setEuropeanaURI($data[$c]);
                    $entity->setUploadFile($uploadFile);
                    $em->persist($entity);

                    //$itemProperty = array();
                    foreach($this->get('ner_model.nameEntityRecognition')->getListProperties() as $property) {
                        if (isset($item->object->proxies[0]->{$property}) AND
                            $count < $limit
                        ) {
                            if((count($fields) > 0 AND in_array($property, $fields)) OR count($fields) == 0) {
                                $return[] =
                                    [
                                        'literal' => $this->testType($item->object->proxies[0]->{$property}, $language),
                                        'field' => $property,
                                        'entity' => $entity,
                                        'language' => strtoupper($language),
                                    ];
                            }
                        }
                    }
                    //$return[] = $itemProperty;
                }
            }
            fclose($handle);
        }

        foreach($return as $toDoNER) {
            $field = new Field();
            $field->setEntity($toDoNER['entity']);
            $field->setField($toDoNER['field']);
            $field->setLanguage($toDoNER['language']);
            $field->setLiteral($toDoNER['literal']);
            $em->persist($field);

            $this->get('ner_model.nameEntityRecognition')->getBabelNet($field);
            $this->get('ner_model.nameEntityRecognition')->getBabelFy($field);
        }

        $em->flush();

        return $this->redirectToRoute('ner_home_home_view', array('uploadFile_id' => $uploadFile_id));
    }

    public function getQuery($query)
    {
        $buzz = $this->get('Buzz');
        $buzz->getClient()->setTimeout(0);
        $timeStart = microtime(true);
        $response = $buzz->get($query);
        $timeEnd = microtime(true);
        $time = $timeEnd - $timeStart;
        $timeQuery = number_format($time, 3);

        return [$response, $timeQuery];
    }


    private function testType($entity, $lang)
    {
        if(is_array($entity)) {
            if(isset($entity[$lang])) {
                return $this->testType($entity[$lang], $lang);
            } elseif(isset($entity['def'])) {
                return $this->testType($entity['def'], $lang);
            } else {
                return $this->testType($entity[0], $lang);
            }
        } elseif(is_object($entity)) {
            if(isset($entity->{$lang})) {
                return $this->testType($entity->{$lang}, $lang);
            } elseif(isset($entity->{'def'})) {
                return $this->testType($entity->{'def'}, $lang);
            }
        } elseif(is_string($entity)) {
            return $entity;
        } else {
            return gettype($entity);
        }
    }
}
