<?php

namespace RS\ModelBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecommenderParametersType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isDcSubject',        ChoiceType::class, array('required' => true,
                                                                 'choices' => array('Yes' => true, 'No' => false),
                                                                 'expanded' => true,
                                                                 'multiple' => false))
            ->add('isDcType',           ChoiceType::class, array('required' => true,
                'choices' => array('Yes' => true, 'No' => false),
                'expanded' => true,
                'multiple' => false))
            ->add('isDcCreator',        ChoiceType::class, array('required' => true,
                'choices' => array('Yes' => true, 'No' => false),
                'expanded' => true,
                'multiple' => false))
            ->add('isDcContributor',    ChoiceType::class, array('required' => true,
                'choices' => array('Yes' => true, 'No' => false),
                'expanded' => true,
                'multiple' => false))
            ->add('isTitle',            ChoiceType::class, array('required' => true,
                'choices' => array('Yes' => true, 'No' => false),
                'expanded' => true,
                'multiple' => false))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'RS\ModelBundle\Entity\RecommenderParameters'
        ));
    }
}
