<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

//use Ekyna\Bundle\CoreBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SupplierOrderSubmitType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderSubmitType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            /*->add('emails', CollectionType::class, [
                'label' => 'ekyna_core.field.email',
                'constraints' => [
                    new Assert\Count(['min' => 1]),
                    new Assert\All([
                        'constraints' => [
                            new Assert\Email(),
                        ]
                    ])
                ]
            ])*/
            ->add('confirm', CheckboxType::class, [
                'label'       => 'ekyna_core.message.action_confirm',
                'required'    => false,
                'constraints' => [
                    new Assert\IsTrue(),
                ],
                'attr' => [
                    'align_with_widget' => true,
                ]
            ]);
    }
}
