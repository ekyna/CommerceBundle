<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Sale;

use Ekyna\Bundle\CommerceBundle\Service\SaleHelper;
use Ekyna\Bundle\CommerceBundle\Service\SaleItemHelper;
use Ekyna\Bundle\ResourceBundle\Action\RoutingActionInterface;
use Ekyna\Bundle\ResourceBundle\Action\ValidatorTrait;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Common\Updater\SaleUpdaterInterface;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedValueException;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;

use function array_map;
use function implode;

/**
 * Class BatchAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Sale
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class BatchAction extends AbstractSaleAction implements RoutingActionInterface
{
    //private const ACTION_UPDATE_QUANTITIES    = 'update-quantities';
    private const ACTION_REMOVE_ITEMS         = 'remove-items';
    private const ACTION_SYNCHRONISE_SUBJECTS = 'synchronise-subjects';

    use XhrTrait;
    use ValidatorTrait;

    public function __construct(
        private readonly SaleHelper           $saleHelper,
        private readonly SaleItemHelper       $saleItemHelper,
        private readonly SaleUpdaterInterface $saleUpdater,
    ) {
    }

    public function __invoke(): Response
    {
        if (!$sale = $this->getSale()) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $action = $this->request->attributes->get('action');

        try {
            $changed = match ($action) {
                //self::ACTION_UPDATE_QUANTITIES    => $this->updateQuantities(),
                self::ACTION_REMOVE_ITEMS         => $this->removeItems(),
                self::ACTION_SYNCHRONISE_SUBJECTS => $this->synchroniseLines(),
            };
        } catch (CommerceExceptionInterface) {
            $changed = false;
        }

        if ($changed) {
            $violations = $this->validate($sale);

            if (0 === $violations->count()) {
                $this->getManager()->update($sale);
            }
        }

        return $this->buildXhrSaleViewResponse($sale);
    }

    private function removeItems(): bool
    {
        $sale = $this->getSale();

        $changed = false;

        $identifiers = $this->getIdentifiers();

        foreach ($identifiers as $id) {
            $item = $this->findItem($sale, $id);

            $this->saleItemHelper->preventIllegalOperation($item);
        }

        foreach ($identifiers as $id) {
            $this->saleHelper->removeItemById($sale, $id);

            $changed = true;
        }

        return $changed;
    }

    private function synchroniseLines(): bool
    {
        $sale = $this->getSale();

        $changed = false;

        $identifiers = $this->getIdentifiers();

        foreach ($identifiers as $id) {
            $item = $this->findItem($sale, $id);

            $this->saleItemHelper->preventIllegalOperation($item);
        }

        foreach ($identifiers as $id) {
            $item = $this->findItem($sale, $id);

            $this->saleItemHelper->initialize($item, null);
            $this->saleItemHelper->build($item);

            $changed = true;
        }

        return $changed;
    }

    private function findItem(SaleInterface $sale, int $id): SaleItemInterface
    {
        if (null === $item = $this->saleHelper->findItemById($sale, $id, true)) {
            throw new UnexpectedValueException('Unknown identifier');
        }

        if ($item->hasParent() || $item->isImmutable()) {
            throw new UnexpectedValueException('Item is immutable');
        }

        return $item;
    }

    /**
     * @return array <int, int>
     */
    private function getIdentifiers(): array
    {
        $data = $this->request->request->all()['sale']['items'] ?? [];

        return array_map(fn(string $value) => (int)$value, $data);
    }

    /*private function updateQuantities(): bool
    {
        $sale = $this->getSale();

        $data = $this->request->request->all()['sale']['item'] ?? [];

        $quantities = []; // TODO

        foreach ($data as $id => $datum) {
            $quantities[(int)$id] = (string) $datum['quantity'] ?? '0';
        }

        if (!$this->saleHelper->updateQuantities($sale, $quantities)) {
            return false;
        }

        return $this->saleUpdater->recalculate($sale);
    }*/

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_sale_batch',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_batch',
                'path'     => '/batch/{action}',
                'resource' => true,
                'methods'  => ['POST'],
            ],
            'options'    => [
                'expose' => true,
            ],
        ];
    }

    public static function buildRoute(Route $route, array $options): void
    {
        $route->setRequirement('action', implode('|', [
            //self::ACTION_UPDATE_QUANTITIES,
            self::ACTION_REMOVE_ITEMS,
            self::ACTION_SYNCHRONISE_SUBJECTS,
        ]));
    }
}
