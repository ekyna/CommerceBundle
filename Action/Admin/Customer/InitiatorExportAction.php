<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Customer;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\CommerceBundle\Service\Customer\InitiatorExporter;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\RoutingActionInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Action\Permission;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;

/**
 * Class InitiatorExportAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Customer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InitiatorExportAction extends AbstractAction implements AdminActionInterface, RoutingActionInterface
{
    public function __construct(private readonly InitiatorExporter $initiatorExporter)
    {
    }

    public function __invoke(): Response
    {
        $customer = $this->context->getResource();

        if (!$customer instanceof CustomerInterface) {
            throw new UnexpectedTypeException($customer, CustomerInterface::class);
        }

        $type = $this->request->attributes->get('type');

        if ('order' === $type) {
            $csv = $this->initiatorExporter->exportOrders($customer);
        } elseif ('quote' === $type) {
            $csv = $this->initiatorExporter->exportQuotes($customer);
        } else {
            throw new InvalidArgumentException('Expected \'order\' or \'quote\' as type.');
        }

        return $csv->download();
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_customer_initiator_export',
            'permission' => Permission::READ,
            'route'      => [
                'name'     => 'admin_%s_initiator_export',
                'path'     => '/initiator-{type}-export',
                'resource' => true,
                'methods'  => ['GET'],
            ],
            'button'     => [
                'label'        => 'customer.button.export',
                'trans_domain' => 'EkynaCommerce',
                'icon'         => 'download',
            ],
        ];
    }

    public static function buildRoute(Route $route, array $options): void
    {
        $route->addRequirements([
            'type' => 'quote|order',
        ]);
    }
}
