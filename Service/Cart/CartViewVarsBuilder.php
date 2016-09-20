<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Cart;

use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Common\View;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderRegistryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class CartViewVarsBuilder
 * @package Ekyna\Bundle\CommerceBundle\Service\Cart
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartViewVarsBuilder implements View\ViewVarsBuilderInterface
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var SubjectProviderRegistryInterface
     */
    private $subjectProviderRegistry;


    /**
     * Constructor.
     *
     * @param UrlGeneratorInterface            $urlGenerator
     * @param SubjectProviderRegistryInterface $subjectProviderRegistry
     */
    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        SubjectProviderRegistryInterface $subjectProviderRegistry
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->subjectProviderRegistry = $subjectProviderRegistry;
    }

    /**
     * @inheritDoc
     */
    public function buildSaleViewVars(Model\SaleInterface $sale, array $options = [])
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function buildItemViewVars(Model\SaleItemInterface $item, array $options = [])
    {
        if ($item->isImmutable() || !$options['editable']) {
            return [];
        }

        $actions = [];

        // Configure action
        if ($item->isConfigurable()) {
            $configurePath = $this->generateUrl('ekyna_commerce_cart_configure_item', [
                'itemId' => $item->getId(),
            ]);
            $actions[] = new View\Action($configurePath, 'fa fa-cog', [
                'title'           => 'ekyna_commerce.sale.button.configure_item',
                'data-sale-modal' => null,
            ]);
        }

        // Remove action
        $removePath = $this->generateUrl('ekyna_commerce_cart_remove_item', [
            'itemId' => $item->getId(),
        ]);
        $actions[] = new View\Action($removePath, 'fa fa-remove', [
            'title'         => 'ekyna_commerce.sale.button.remove_item',
            'confirm'       => 'ekyna_commerce.sale.confirm.remove_item',
            'data-sale-xhr' => null,
        ]);

        return [
            'actions' => $actions,
        ];
    }

    /**
     * @inheritDoc
     */
    public function buildAdjustmentViewVars(Model\AdjustmentInterface $adjustment, array $options = [])
    {
        if ($adjustment->isImmutable() || !$options['editable']) {
            return [];
        }

        $actions = [];

        $adjustable = $adjustment->getAdjustable();
        if ($adjustable instanceof Model\SaleInterface) {
            $removePath = $this->generateUrl('ekyna_commerce_cart_remove_adjustment', [
                'adjustmentId' => $adjustment->getId(),
            ]);
        } elseif ($adjustable instanceof Model\SaleItemInterface) {
            $removePath = $this->generateUrl('ekyna_commerce_cart_remove_item_adjustment', [
                'itemId'       => $adjustable->getId(),
                'adjustmentId' => $adjustment->getId(),
            ]);
        } else {
            throw new InvalidArgumentException('Unexpected adjustable.');
        }

        $actions[] = new View\Action($removePath, 'fa fa-remove', [
            'title'         => 'ekyna_commerce.sale.button.remove_adjustment',
            'confirm'       => 'ekyna_commerce.sale.confirm.remove_adjustment',
            'data-sale-xhr' => null,
        ]);

        return [
            'actions' => $actions,
        ];
    }

    /**
     * Generates the url.
     *
     * @param string $name
     * @param array  $parameters
     *
     * @return string
     */
    protected function generateUrl($name, array $parameters = [])
    {
        return $this->urlGenerator->generate($name, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * Resolves the item's subject.
     *
     * @param Model\SaleItemInterface $item
     *
     * @return mixed|null
     *
     * @see SubjectProviderRegistryInterface
     */
    protected function resolveItemSubject(Model\SaleItemInterface $item)
    {
        return $this->subjectProviderRegistry->resolveItemSubject($item);
    }
}
