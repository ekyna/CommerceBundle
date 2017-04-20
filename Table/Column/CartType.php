<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\AdminBundle\Action\ReadAction;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\ColumnType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_replace;
use function is_array;
use function json_encode;
use function sprintf;
use function Symfony\Component\Translation\t;

/**
 * Class CartType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartType extends AbstractColumnType
{
    private ResourceHelper $resourceHelper;

    public function __construct(ResourceHelper $resourceHelper)
    {
        $this->resourceHelper = $resourceHelper;
    }

    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        $carts = $row->getData($column->getConfig()->getPropertyPath());

        if ($carts instanceof CartInterface) {
            $href = $this->resourceHelper->generateResourcePath($carts, ReadAction::class);

            /** @noinspection HtmlUnknownTarget */
            $view->vars['value'] = sprintf('<a href="%s">%s</a> ', $href, $carts->getNumber());

            $view->vars['attr'] = array_replace($view->vars['attr'], [
                'data-side-detail' => json_encode([
                    'route'      => 'ekyna_commerce_cart_admin_summary',
                    'parameters' => [
                        'cartId' => $carts->getId(),
                    ],
                ]),
            ]);

            return;
        }

        if ($carts instanceof Collection) {
            $carts = $carts->toArray();
        } elseif (!is_array($carts)) {
            $carts = [$carts];
        }

        $output = '';

        foreach ($carts as $cart) {
            if (!$cart instanceof CartInterface) {
                continue;
            }

            $href = $this->resourceHelper->generateResourcePath($cart, ReadAction::class);

            $summary = json_encode([
                'route'      => 'ekyna_commerce_cart_admin_summary',
                'parameters' => ['cartId' => $cart->getId()],
            ]);

            /** @noinspection HtmlUnknownTarget */
            $output .= sprintf('<a href="%s" data-side-detail=\'%s\'>%s</a>', $href, $summary, $cart->getNumber());
        }

        $view->vars['value'] = $output;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'multiple'      => false,
            'label'         => function (Options $options, $value) {
                if ($value) {
                    return $value;
                }

                return t('cart.label.' . ($options['multiple'] ? 'plural' : 'singular'), [], 'EkynaCommerce');
            },
            'property_path' => function (Options $options, $value) {
                if ($value) {
                    return $value;
                }

                return $options['multiple'] ? 'carts' : 'cart';
            },
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'text';
    }

    public function getParent(): ?string
    {
        return ColumnType::class;
    }
}
