<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Filter;

use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceSearchType;
use Ekyna\Bundle\ResourceBundle\Table\Filter\ResourceType;
use Ekyna\Component\Table\Filter\AbstractFilterType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class OrderType
 * @package Ekyna\Bundle\CommerceBundle\Table\Filter
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderType extends AbstractFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'resource'     => 'ekyna_commerce.order',
            'form_class'   => ResourceSearchType::class,
            'form_options' => ['resource' => 'ekyna_commerce.order'],
        ]);
    }

    public function getParent(): ?string
    {
        return ResourceType::class;
    }
}
