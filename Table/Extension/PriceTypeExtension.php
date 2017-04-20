<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Extension;

use Ekyna\Bundle\TableBundle\Extension\Type\Column\PriceType;
use Ekyna\Component\Table\Extension\AbstractColumnTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PriceTypeExtension
 * @package Ekyna\Bundle\CommerceBundle\Table\Extension
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PriceTypeExtension extends AbstractColumnTypeExtension
{
    private string $defaultCurrency;

    public function __construct(string $defaultCurrency)
    {
        $this->defaultCurrency = $defaultCurrency;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('currency', $this->defaultCurrency);
    }

    public static function getExtendedTypes(): array
    {
        return [PriceType::class];
    }
}
