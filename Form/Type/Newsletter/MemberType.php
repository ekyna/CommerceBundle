<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Newsletter;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\ConstantChoiceType;
use Ekyna\Component\Commerce\Newsletter\Model\MemberStatuses;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class MemberType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Newsletter
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class MemberType extends ResourceFormType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SET_DATA, function(FormEvent $event) {
            /** @var \Ekyna\Component\Commerce\Newsletter\Model\MemberInterface $member */
            $member = $event->getData();

            $disabled = $member && $member->getId();

            $event
                ->getForm()
                ->add('audience', ResourceType::class, [
                    'resource' => 'ekyna_commerce.audience',
                    'disabled' => $disabled,
                ])
                ->add('email', EmailType::class, [
                    'label' => 'ekyna_core.field.email_address',
                    'disabled' => $disabled,
                ]);
        });

        $builder
            ->add('status', ConstantChoiceType::class, [
                'label' => 'ekyna_core.field.status',
                'class' => MemberStatuses::class,
            ]);
    }
}
