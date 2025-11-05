<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\ProductionOrder;

use Ekyna\Bundle\AdminBundle\Action\CreateAction as BaseAction;
use Ekyna\Bundle\ResourceBundle\Action\RepositoryTrait;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Manufacture\Model\BillOfMaterialsInterface;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionOrderInterface;
use Ekyna\Component\Commerce\Manufacture\Repository\BillOfMaterialsRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CreateAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\ProductionOrder
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CreateAction extends BaseAction
{
    use RepositoryTrait;

    protected function onInit(): ?Response
    {
        $resource = $this->context->getResource();

        if (!$resource instanceof ProductionOrderInterface) {
            throw new UnexpectedTypeException($resource, ProductionOrderInterface::class);
        }

        if ($this->request->query->has('bom')) {
            /** @var BillOfMaterialsRepositoryInterface $repository */
            $repository = $this->getRepository(BillOfMaterialsInterface::class);

            if (null !== $bom = $repository->find($this->request->query->getInt('bom'))) {
                $resource->setBom($bom);
            }
        }

        return parent::onInit();
    }
}
