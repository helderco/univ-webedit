<?php

namespace Siriux\WebeditBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class TemplateType extends AbstractType
{

    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('body', 'textarea', array(
                'attr' => array('rows' => 18),
                'error_bubbling' => true))
        ;
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Siriux\WebeditBundle\Entity\Template',
        );
    }

    public function getName()
    {
        return 'siriux_template';
    }
}
