<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Dashboard;

use Ekyna\Bundle\AdminBundle\Dashboard\Widget\Type\AbstractWidgetType;
use Ekyna\Bundle\AdminBundle\Dashboard\Widget\WidgetInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderInvoiceRepositoryInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierOrderRepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * Class DebtWidget
 * @package Ekyna\Bundle\CommerceBundle\Dashboard
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DebtWidget extends AbstractWidgetType
{
    public const NAME = 'commerce_debt';

    protected OrderInvoiceRepositoryInterface  $invoiceRepository;
    protected OrderRepositoryInterface         $orderRepository;
    protected SupplierOrderRepositoryInterface $supplierOrderRepository;

    public function __construct(
        OrderInvoiceRepositoryInterface $invoiceRepository,
        OrderRepositoryInterface $orderRepository,
        SupplierOrderRepositoryInterface $supplierOrderRepository
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->orderRepository = $orderRepository;
        $this->supplierOrderRepository = $supplierOrderRepository;
    }

    public function render(WidgetInterface $widget, Environment $twig): string
    {
        return $twig->render('@EkynaCommerce/Admin/Dashboard/widget_debt.html.twig', [
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
