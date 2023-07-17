<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Sale;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\CommerceBundle\Service\Checker\SaleItemsChecker;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Symfony\Component\HttpFoundation\Response;

use function Symfony\Component\Translation\t;

/**
 * Class CheckItemsAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Sale
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CheckItemsAction extends AbstractAction implements AdminActionInterface
{
    use ManagerTrait;
    use FlashTrait;
    use HelperTrait;

    public function __construct(
        private readonly SaleItemsChecker $checker
    ) {
    }

    public function __invoke(): Response
    {
        $sale = $this->context->getResource();

        if (!$sale instanceof SaleInterface) {
            throw new UnexpectedTypeException($sale, SaleInterface::class);
        }

        if (!$this->checker->check($sale)) {
            $this->getManager($sale)->save($sale);

            $this->addFlash(t('sale.message.invalid_items', [], 'EkynaCommerce'), 'warning');
        } else {
            $this->addFlash(t('sale.message.valid_items', [], 'EkynaCommerce'), 'success');
        }

        return $this->redirect(
            $this->generateResourcePath($sale)
        );
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_sale_check_items',
            'permission' => Permission::READ,
            'route'      => [
                'name'    => 'admin_%s_check_items',
                'path'    => '/check-items',
                'methods' => ['GET'],
                'resource' => true,
            ],
            'button'     => [
                'label'        => 'sale.button.check_items',
                'trans_domain' => 'EkynaCommerce',
                'theme'        => 'primary',
                'icon'         => 'ok-circle',
            ],
        ];
    }
}
