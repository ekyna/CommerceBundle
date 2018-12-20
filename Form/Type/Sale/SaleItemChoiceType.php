<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Sale;

use Ekyna\Bundle\CommerceBundle\Form\ChoiceList\SaleItemChoiceLoader;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Symfony\Bridge\Doctrine\Form\DataTransformer\CollectionToArrayTransformer;
use Symfony\Bridge\Doctrine\Form\EventListener\MergeDoctrineCollectionListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SaleItemChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Sale
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemChoiceType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['multiple']) {
            $builder
                ->addEventSubscriber(new MergeDoctrineCollectionListener())
                ->addViewTransformer(new CollectionToArrayTransformer(), true)
            ;
        }
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('sale')
            ->setDefaults([
                'label' => function(Options $options) {
                    return 'ekyna_commerce.sale_item.label.' . ($options['multiple'] ? 'plural' : 'singular');
                },
                'public'        => true,
                'depth'         => null,
                'choices'       => null,
                'choice_loader' => function (Options $options) {
                    return new SaleItemChoiceLoader($options['sale']);
                },
                'choice_label'  => function (SaleItemInterface $item) {
                    return sprintf(
                        '%s [%s] %s',
                        str_repeat('&nbsp;&nbsp;&rsaquo;&nbsp;', $item->getLevel()),
                        $item->getReference(),
                        $item->getDesignation()
                    );
                },
                'choice_name'   => 'id',
                'choice_value'  => 'id',
            ])
            ->setAllowedTypes('sale', SaleInterface::class)
            ->setAllowedTypes('public', 'bool')
            ->setAllowedTypes('depth', ['int', 'null']);
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
