<?php

declare(strict_types=1);

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

use function sprintf;
use function Symfony\Component\Translation\t;

/**
 * Class SaleItemChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Sale
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemChoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['multiple']) {
            $builder
                ->addEventSubscriber(new MergeDoctrineCollectionListener())
                ->addViewTransformer(new CollectionToArrayTransformer(), true);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('sale')
            ->setDefaults([
                'label'                     => function (Options $options) {
                    return t('sale_item.label.' . ($options['multiple'] ? 'plural' : 'singular'), [], 'EkynaCommerce');
                },
                'public'                    => true,
                'depth'                     => null,
                'choices'                   => null,
                'choice_loader'             => function (Options $options) {
                    return new SaleItemChoiceLoader($options['sale']);
                },
                'choice_label'              => function (SaleItemInterface $item) {
                    return sprintf(
                        '%s [%s] %s',
                        str_repeat('&nbsp;&nbsp;&rsaquo;&nbsp;', $item->getLevel()),
                        $item->getReference(),
                        $item->getDesignation()
                    );
                },
                'choice_translation_domain' => false,
                'choice_name'               => 'id',
                'choice_value'              => 'id',
            ])
            ->setAllowedTypes('sale', SaleInterface::class)
            ->setAllowedTypes('public', 'bool')
            ->setAllowedTypes('depth', ['int', 'null']);
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}
