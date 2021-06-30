<?php

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
    /**
     * @var FormFactoryInterface
     */
    private $factory;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;


    /**
     * Constructor.
     *
     * @param FormFactoryInterface  $factory
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(FormFactoryInterface $factory, UrlGeneratorInterface $urlGenerator)
    {
        $this->factory = $factory;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @inheritDoc
     */
    public function render(WidgetInterface $widget, Environment $twig)
    {
        $accountingForm = $this->factory->create(ExportType::class, null, [
            'action' => $this->urlGenerator->generate('ekyna_commerce_export_admin_accounting'),
            'method' => 'POST',
        ]);

        $costsForm = $this->factory->create(ExportType::class, null, [
            'action' => $this->urlGenerator->generate('ekyna_commerce_export_admin_invoice_costs'),
            'method' => 'POST',
        ]);

        return $twig->render('@EkynaCommerce/Admin/Dashboard/widget_export.html.twig', [
            'accounting_form' => $accountingForm->createView(),
            'costs_form'      => $costsForm->createView(),
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
            'position' => 9996,
            'css_path' => 'bundles/ekynacommerce/css/admin-dashboard.css',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'commerce_export';
    }
}
