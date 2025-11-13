<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Dashboard;

use Ekyna\Bundle\AdminBundle\Dashboard\Widget\Type\AbstractWidgetType;
use Ekyna\Bundle\AdminBundle\Dashboard\Widget\WidgetInterface;
use Ekyna\Bundle\CommerceBundle\Model\Permission;
use Ekyna\Bundle\CommerceBundle\Service\Export\ExportFormHelper;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;

/**
 * Class AccountingWidget
 * @package Ekyna\Bundle\CommerceBundle\Dashboard
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ExportWidget extends AbstractWidgetType
{
    public const NAME = 'commerce_export';

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly ExportFormHelper $formHelper,
    ) {
    }

    public static function getName(): string
    {
        return self::NAME;
    }

    public function render(WidgetInterface $widget, Environment $twig): string
    {
        if (!$this->authorizationChecker->isGranted(Permission::DASHBOARD_EXPORT, OrderInterface::class)) {
            return '';
        }

        $accountingForm = $this->formHelper->createMonthForm('admin_ekyna_commerce_export_accounting');
        $costsForm = $this->formHelper->createMonthForm('admin_ekyna_commerce_export_invoice_costs');
        $samplesForm = $this->formHelper->createRangeForm('admin_ekyna_commerce_export_sample_order_items');

        return $twig->render('@EkynaCommerce/Admin/Dashboard/widget_export.html.twig', [
            'accounting_form' => $accountingForm->createView(),
            'costs_form'      => $costsForm->createView(),
            'samples_form'    => $samplesForm->createView(),
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'frame'    => false,
            'position' => 9996,
            'col_md'   => 6,
            'css_path' => 'bundles/ekynacommerce/css/admin-dashboard.css',
        ]);
    }
}
