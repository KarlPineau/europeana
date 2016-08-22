<?php

namespace NER\ModelBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class NameEntityRecognitionController extends Controller
{
    public function generateNameEntityRecognitionAction($field, $dataSet, $limit)
    {
        set_time_limit(0);
        $count = 0;
        $buzz = $this->container->get('Buzz');

        foreach($dataSet as $uri) {
            $responseItem = $buzz->get($uri.'&profile=rich');
            $item = json_decode($responseItem->getContent());

            if($this->getDoctrine()->getManager()->getRepository('NERModelBundle:NameEntityRecognition')->findOneBy(array('europeanaURI' => $uri, 'field' => $field)) == null AND
                isset($item->object->proxies[0]->{$field}) AND
                $count < $limit) {

                $content = testType($item->object->proxies[0]->{$field});
                $return = $this->get('ner_model.nameEntityRecognition')->nameEntityRecognition($content, $field, $uri);

                if($return == false) {break;}
                else {
                    $count++;
                }
            }
        }

        return $this->redirectToRoute('ner_home_home_index');
    }

    private function testType($entity)
    {
        if(is_array($entity)) {
            testType(array_values($entity)[0]);
        } elseif(is_string($entity)) {
            return $entity;
        }
    }
}
