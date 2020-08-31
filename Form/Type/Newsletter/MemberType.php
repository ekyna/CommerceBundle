<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Newsletter;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
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
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            /** @var \Ekyna\Component\Commerce\Newsletter\Model\MemberInterface $member */
            $member = $event->getData();

            $disabled = $member && $member->getId();

            $event
                ->getForm()
                ->add('email', EmailType::class, [
                    'label'    => 'ekyna_core.field.email_address',
                    'disabled' => $disabled,
                ]);
        });
    }
}
