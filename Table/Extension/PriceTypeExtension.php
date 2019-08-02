<?php

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
    /**
     * @var string
     */
    private $defaultCurrency;


    /**
     * Constructor.
     *
     * @param string $defaultCurrency
     */
    public function __construct(string $defaultCurrency)
    {
        $this->defaultCurrency = $defaultCurrency;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('currency', $this->defaultCurrency);
    }

    /**
     * @inheritDoc
     */
    public function getExtendedType()
    {
        return PriceType::class;
    }
}
