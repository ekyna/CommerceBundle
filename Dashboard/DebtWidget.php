<?php

namespace Ekyna\Bundle\CommerceBundle\Dashboard;

use Ekyna\Bundle\AdminBundle\Dashboard\Widget\Type\AbstractWidgetType;
use Ekyna\Bundle\AdminBundle\Dashboard\Widget\WidgetInterface;
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
     * @param OrderRepositoryInterface         $orderRepository
     * @param SupplierOrderRepositoryInterface $supplierOrderRepository
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SupplierOrderRepositoryInterface $supplierOrderRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->supplierOrderRepository = $supplierOrderRepository;
    }

    /**
     * @inheritDoc
     */
    public function render(WidgetInterface $widget, \Twig_Environment $twig)
    {
        return $twig->render('EkynaCommerceBundle:Admin\Dashboard:widget_debt.html.twig', [
            'regular_due'         => $this->orderRepository->getRegularDue(),
            'outstanding_expired' => $this->orderRepository->getOutstandingExpiredDue(),
            'outstanding_to_go'   => $this->orderRepository->getOutstandingToGoDue(),
            'outstanding_pending' => $this->orderRepository->getOutstandingPendingDue(),
            'supplier_expired'    => $this->supplierOrderRepository->getSuppliersExpiredDue(),
            'supplier_fall'       => $this->supplierOrderRepository->getSuppliersFallDue(),
            'carrier_expired'     => $this->supplierOrderRepository->getCarriersExpiredDue(),
            'carrier_fall'        => $this->supplierOrderRepository->getCarriersFallDue(),
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
            'css_path' => '/bundles/ekynacommerce/css/admin-dashboard.css',
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