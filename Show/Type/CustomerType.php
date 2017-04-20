<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Show\Type;

use Ekyna\Bundle\AdminBundle\Show\Type\AbstractType;
use Ekyna\Bundle\AdminBundle\Show\View;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Exception\UnexpectedValueException;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class CustomerType
 * @package Ekyna\Bundle\CommerceBundle\Show\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function build(View $view, $value, array $options = []): void
    {
        if ($value && !$value instanceof CustomerInterface) {
            throw new UnexpectedTypeException($value, CustomerInterface::class);
        }

        parent::build($view, $value, $options);
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('label', t('customer.label.singular', [], 'EkynaCommerce'));
    }

    public static function getName(): string
    {
        return 'commerce_customer';
    }
}
