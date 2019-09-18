<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\ColumnType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class CartType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartType extends AbstractColumnType
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;


    /**
     * Constructor.
     *
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @inheritDoc
     */
    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options)
    {
        $carts = $row->getData($column->getConfig()->getPropertyPath());

        if ($carts instanceof CartInterface) {
            $href = $this->urlGenerator->generate('ekyna_commerce_cart_admin_show', [
                'cartId' => $carts->getId(),
            ]);

            $view->vars['value'] = sprintf(
                '<a href="%s">%s</a> ',
                $href,
                $carts->getNumber()
            );

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

            $href = $this->urlGenerator->generate('ekyna_commerce_cart_admin_show', [
                'cartId' => $cart->getId(),
            ]);

            $summary = json_encode([
                'route'      => 'ekyna_commerce_cart_admin_summary',
                'parameters' => ['cartId' => $cart->getId()],
            ]);

            $output .= sprintf(
                '<a href="%s" data-side-detail=\'%s\'>%s</a> ',
                $href,
                $summary,
                $cart->getNumber()
            );
        }

        $view->vars['value'] = $output;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'multiple'      => false,
            'label'         => function (Options $options, $value) {
                if ($value) {
                    return $value;
                }

                return 'ekyna_commerce.cart.label.' . ($options['multiple'] ? 'plural' : 'singular');
            },
            'property_path' => function (Options $options, $value) {
                if ($value) {
                    return $value;
                }

                return $options['multiple'] ? 'carts' : 'cart';
            },
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'text';
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return ColumnType::class;
    }
}
