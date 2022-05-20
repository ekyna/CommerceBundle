<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Filter;

use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerSearchType;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type\Filter\EntityType;
use Ekyna\Component\Table\Filter\AbstractFilterType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class CustomerType
 * @package Ekyna\Bundle\CommerceBundle\Table\Filter
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerType extends AbstractFilterType
{
    private string $customerClass;

    public function __construct(string $class)
    {
        $this->customerClass = $class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label'      => t('customer.label.singular', [], 'EkynaCommerce'),
            'class'      => $this->customerClass,
            'form_class' => CustomerSearchType::class,
        ]);
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
