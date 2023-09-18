<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Common;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;

use function array_replace;
use function sprintf;

/**
 * Class CustomerRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Common
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CustomerRenderer
{
    public function __construct(
        private readonly RepositoryFactoryInterface $repositoryFactory,
        private readonly string                     $defaultCountry,
    ) {
    }

    public function getCustomerFlags(CustomerInterface $customer, array $options = []): string
    {
        $repository = $this->repositoryFactory->getRepository('ekyna_commerce.order');
        if (!$repository instanceof OrderRepositoryInterface) {
            throw new UnexpectedTypeException($repository, OrderRepositoryInterface::class);
        }

        $orderCount = $repository->existsForCustomer($customer) ? 1 : 0;

        $country = $customer->getDefaultInvoiceAddress()?->getCountry()->getCode();

        return $this->renderCustomerFlags($orderCount, $country, $options);
    }

    public function renderCustomerFlags(int $orderCount, ?string $country, array $options = []): string
    {
        $options = array_replace([
            'long'    => false,
        ], $options);

        $parameters = 0 < $orderCount
            ? ['green', $options['long'] ? 'Customer' : 'C']
            : ['orange', $options['long'] ? 'Prospect' : 'P'];

        $output = sprintf(
            '<span class="label label-%s">%s</span>',
            ...$parameters
        );

        $parameters = match ($country) {
            null                  => ['grey', '?'],
            $this->defaultCountry => ['blue', $options['long'] ? 'National' : 'N'],
            default               => ['purple', $options['long'] ? 'International' : 'I'],
        };

        $output .= sprintf(
            '<span class="label label-%s">%s</span>',
            ...$parameters
        );

        return $output;
    }
}
