<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Common;

use Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\RecalculateAction;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleQuantitiesType;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\View\SaleView;
use Ekyna\Component\Commerce\Common\View\ViewBuilder;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

use function array_replace;

/**
 * Class SaleViewHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Common
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SaleViewHelper
{
    protected ViewBuilder $viewBuilder;
    protected ResourceHelper $resourceHelper;
    protected FormFactoryInterface $formFactory;


    public function __construct(
        ViewBuilder $viewBuilder,
        ResourceHelper $resourceHelper,
        FormFactoryInterface $formFactory
    ) {
        $this->viewBuilder = $viewBuilder;
        $this->resourceHelper = $resourceHelper;
        $this->formFactory = $formFactory;
    }

    public function buildQuantitiesForm(SaleInterface $sale, array $options = []): FormInterface
    {
        $options = array_replace([
            'method' => 'post',
        ], $options);

        if (!isset($options['action'])) {
            $options['action'] = $this
                ->resourceHelper
                ->generateResourcePath($sale, RecalculateAction::class);
        }

        return $this->formFactory->create(SaleQuantitiesType::class, $sale, $options);
    }

    public function buildSaleView(SaleInterface $sale, array $options = [], FormInterface $form = null): SaleView
    {
        $options = array_replace([
            'private'  => true,
            'editable' => !$sale->isReleased(),
        ], $options);

        $view = $this->viewBuilder->buildSaleView($sale, $options);

        if (!$options['editable']) {
            return $view;
        }

        if (null === $form) {
            $form = $this->buildQuantitiesForm($sale); // TODO action
        }

        $view->vars['quantities_form'] = $form->createView();

        return $view;
    }
}
