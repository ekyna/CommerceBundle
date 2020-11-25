<?php

namespace Ekyna\Bundle\CommerceBundle\Dashboard;

use Ekyna\Bundle\AdminBundle\Dashboard\Widget\Type\AbstractWidgetType;
use Ekyna\Bundle\AdminBundle\Dashboard\Widget\WidgetInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderInvoiceRepositoryInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierOrderRepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DebtWidget
 * @package Ekyna\Bundle\CommerceBundle\Dashboard
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DebtWidget extends AbstractWidgetType
{
    /**
     * @var OrderInvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var SupplierOrderRepositoryInterface
     */
    protected $supplierOrderRepository;


    /**
     * Constructor.
     *
     * @param OrderInvoiceRepositoryInterface  $invoiceRepository
     * @param OrderRepositoryInterface         $orderRepository
     * @param SupplierOrderRepositoryInterface $supplierOrderRepository
     */
    public function __construct(
        OrderInvoiceRepositoryInterface $invoiceRepository,
        OrderRepositoryInterface $orderRepository,
        SupplierOrderRepositoryInterface $supplierOrderRepository
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->orderRepository = $orderRepository;
        $this->supplierOrderRepository = $supplierOrderRepository;
    }

    /**
     * @inheritDoc
     */
    public function render(WidgetInterface $widget, \Twig_Environment $twig)
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

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'frame'    => false,
            'position' => 9997,
            'col_md'   => 8,
            'css_path' => 'bundles/ekynacommerce/css/admin-dashboard.css',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'commerce_debt';
    }
}
