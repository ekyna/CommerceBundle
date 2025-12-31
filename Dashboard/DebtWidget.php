<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Dashboard;

use Ekyna\Bundle\AdminBundle\Dashboard\Widget\Type\AbstractWidgetType;
use Ekyna\Bundle\AdminBundle\Dashboard\Widget\WidgetInterface;
use Ekyna\Bundle\CommerceBundle\Model\Permission;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderInvoiceRepositoryInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierOrderRepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;

/**
 * Class DebtWidget
 * @package Ekyna\Bundle\CommerceBundle\Dashboard
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DebtWidget extends AbstractWidgetType
{
    public const NAME = 'commerce_debt';

    public function __construct(
        protected readonly OrderInvoiceRepositoryInterface  $invoiceRepository,
        protected readonly OrderRepositoryInterface         $orderRepository,
        protected readonly SupplierOrderRepositoryInterface $supplierOrderRepository,
        protected readonly AuthorizationCheckerInterface    $authorizationChecker,
    ) {

    }

    public function render(WidgetInterface $widget, Environment $twig): string
    {
        if (!$this->authorizationChecker->isGranted(Permission::DASHBOARD_EXPORT, OrderInterface::class)) {
            return '';
        }

        return $twig->render('@EkynaCommerce/Admin/Dashboard/widget_debt.html.twig', [
            'export'           => true, // TODO Remove
            'due_invoices'     => $this->invoiceRepository->getDueTotal(),
            'fall_invoices'    => $this->invoiceRepository->getFallTotal(),
            'remaining_orders' => $this->orderRepository->getRemainingTotal(),
            'supplier_expired' => $this->supplierOrderRepository->getSuppliersExpiredDue(),
            'supplier_fall'    => $this->supplierOrderRepository->getSuppliersFallDue(),
            'carrier_expired'  => $this->supplierOrderRepository->getForwardersExpiredDue(),
            'carrier_fall'     => $this->supplierOrderRepository->getForwardersFallDue(),
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'frame'    => false,
            'position' => 9997,
            'col_md'   => 8,
            'css_path' => 'bundles/ekynacommerce/css/admin-dashboard.css',
        ]);
    }

    public static function getName(): string
    {
        return self::NAME;
    }
}
