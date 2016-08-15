<?php

namespace NER\ModelBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class NameEntityRecognitionController extends Controller
{
    public function generateNameEntityRecognitionAction($field)
    {
        set_time_limit(0);
        $middleArray = array();
        foreach ($this->get('data_data.entity')->find('all', null, 'large') as $entity) {
            if($this->get('data_data.entity')->get($field, $entity) != null) {
                $middleArray[] = $entity;
            }
        }
        shuffle($middleArray);

        $count = 0;
        foreach($middleArray as $item) {
            if($this->getDoctrine()->getManager()->getRepository('TOOLSNerBundle:NameEntityRecognition')->findOneBy(array('usedIn' => $item, 'field' => $field)) == null AND $count < 2) {
                $return = $this->get('tools_ner.ner')->nameEntityRecognition($item->getId(), $field);
                if($return == false) {break;}
                else {
                    $count++;
                }
            }
        }

        return $this->redirectToRoute('tools_ner_index_index');
    }
}
