<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Dashboard;

use Ekyna\Bundle\AdminBundle\Dashboard\Widget\Type\AbstractWidgetType;
use Ekyna\Bundle\AdminBundle\Dashboard\Widget\WidgetInterface;
use Ekyna\Bundle\CommerceBundle\Form\Type\Accounting\ExportType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

/**
 * Class AccountingWidget
 * @package Ekyna\Bundle\CommerceBundle\Dashboard
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ExportWidget extends AbstractWidgetType
{
    public const NAME = 'commerce_export';

    private FormFactoryInterface  $factory;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(FormFactoryInterface $factory, UrlGeneratorInterface $urlGenerator)
    {
        $this->factory = $factory;
        $this->urlGenerator = $urlGenerator;
    }

    public function render(WidgetInterface $widget, Environment $twig): string
    {
        $accountingForm = $this->factory->create(ExportType::class, null, [
            'action' => $this->urlGenerator->generate('admin_ekyna_commerce_export_accounting'),
            'method' => 'POST',
        ]);

        $costsForm = $this->factory->create(ExportType::class, null, [
            'action' => $this->urlGenerator->generate('admin_ekyna_commerce_export_invoice_costs'),
            'method' => 'POST',
        ]);

        return $twig->render('@EkynaCommerce/Admin/Dashboard/widget_export.html.twig', [
            'accounting_form' => $accountingForm->createView(),
            'costs_form'      => $costsForm->createView(),
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'frame'    => false,
            'position' => 9996,
            'css_path' => 'bundles/ekynacommerce/css/admin-dashboard.css',
        ]);
    }

    public static function getName(): string
    {
        return self::NAME;
    }
}
