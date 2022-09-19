<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\Item;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\XhrTrait;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Bundle\ResourceBundle\Action\RepositoryTrait;
use Ekyna\Bundle\ResourceBundle\Action\RoutingActionInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Doctrine\ORM\ListenerToggler;
use Gedmo\Sortable\SortableListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;

/**
 * Class MoveAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\Item
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class MoveAction extends AbstractAction implements AdminActionInterface, RoutingActionInterface
{
    use HelperTrait;
    use ManagerTrait;
    use RepositoryTrait;
    use XhrTrait;

    public function __construct(
        private readonly ListenerToggler $listenerToggler
    ) {
    }

    public function __invoke(): Response
    {
        $item = $this->context->getResource();

        if (!$item instanceof SaleItemInterface) {
            throw new UnexpectedTypeException($item, SaleItemInterface::class);
        }

        if ($item->hasParent()) {
            return new Response(null, Response::HTTP_BAD_REQUEST);
        }

        $target = $this
            ->getRepository()
            ->find($this->request->attributes->getInt('targetId'));

        if (!$target instanceof SaleItemInterface || $target->hasParent() || $target === $item) {
            return new Response(null, Response::HTTP_BAD_REQUEST);
        }

        $sale = $item->getRootSale();

        if ($sale !== $target->getRootSale()) {
            return new Response(null, Response::HTTP_BAD_REQUEST);
        }

        $this->move($item, $target, 'before' === $this->request->attributes->get('mode'));

        /*if ($item->getPosition() !== $target->getPosition() + 1) {
            $item->setPosition($target->getPosition() + 1);

            $this->getManager()->save($item);
        }*/

        if ($this->request->isXmlHttpRequest()) {
            return $this->buildXhrSaleViewResponse($item->getRootSale());
        }

        return $this->redirectToReferer($this->generateResourcePath($item->getRootSale()));
    }

    /**
     * TODO Temporary while Gedmo Sortable Listener is buggy.
     */
    private function move(SaleItemInterface $source, SaleItemInterface $target, bool $before): void
    {
        $this->listenerToggler->disable(SortableListener::class);

        $sale = $source->getRootSale();
        $manager = $this->getManager();

        $sourcePos = $source->getPosition();
        $targetPos = $target->getPosition();

        if ($before) {
            $targetPos--;
        }

        if ($sourcePos === $targetPos) {
            return;
        }

        if ($sourcePos > $targetPos) {
            // Move Up
            foreach ($sale->getItems() as $i) {
                if ($i->getPosition() <= $targetPos) {
                    continue;
                }

                if ($i->getPosition() >= $sourcePos) {
                    continue;
                }

                $i->setPosition($i->getPosition() + 1);

                $manager->persist($i);
            }

            $source->setPosition($targetPos + 1);
        } else {
            // Move Down
            foreach ($sale->getItems() as $i) {
                if ($i->getPosition() <= $sourcePos) {
                    continue;
                }

                if ($i->getPosition() > $targetPos) {
                    continue;
                }

                $i->setPosition($i->getPosition() - 1);

                $manager->persist($i);
            }

            $source->setPosition($targetPos);
        }

        $manager->save($source);
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_sale_item_move',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_move',
                'path'     => '/move/{mode}/{targetId}',
                'methods'  => ['GET'],
                'resource' => true,
            ],
            'options'    => [
                'expose' => true,
            ],
        ];
    }

    public static function buildRoute(Route $route, array $options): void
    {
        $route->addRequirements([
            'targetId' => '\d+',
            'mode'     => 'before|after',
        ]);
    }
}
