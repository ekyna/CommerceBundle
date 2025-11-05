<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\ProductionOrder;

use Ekyna\Bundle\AdminBundle\Action\AbstractConfirmAction;
use Ekyna\Bundle\AdminBundle\Action\ReadAction;
use Ekyna\Bundle\CommerceBundle\Service\Manufacture\ManufactureHelper;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Manufacture\Model\POState;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionOrderInterface;
use Ekyna\Component\Commerce\Manufacture\Repository\BillOfMaterialsRepositoryInterface;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;

use function array_replace;
use function Symfony\Component\Translation\t;

/**
 * Class UpgradeAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\ProductionOrder
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UpgradeAction extends AbstractConfirmAction
{
    public function __construct(
        private readonly ManufactureHelper                  $manufactureHelper,
        private readonly BillOfMaterialsRepositoryInterface $bomRepository,
    ) {
    }

    protected function onInit(): ?Response
    {
        $resource = $this->context->getResource();

        if (!$resource instanceof ProductionOrderInterface) {
            throw new UnexpectedTypeException($resource, ProductionOrderInterface::class);
        }

        if (!$this->manufactureHelper->canOrderBeUpgraded($resource)) {
            $this->addFlash(t('production_order.alert.order_cant_be_upgraded', [], 'EkynaCommerce'), 'warning');

            return $this->redirect($this->generateResourcePath($resource));
        }

        $upgrade = $this->bomRepository->findNewVersion($resource->getBom());

        $resource->setBom($upgrade);
        $resource->setState(POState::NEW);
        $resource->setStartAt(null);
        $resource->setEndAt(null);

        return null;
    }

    protected function getFormOptions(): array
    {
        return array_replace(parent::getFormOptions(), [
            'message' => t('production_order.message.upgrade', [], 'EkynaCommerce'),
        ]);
    }

    protected function getRedirectAction(): string
    {
        return ReadAction::class;
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'production_order_upgrade',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_upgrade',
                'path'     => '/upgrade',
                'resource' => true,
                'methods'  => ['GET', 'POST'],
            ],
            'button'     => [
                'label'        => 'production_order.button.upgrade',
                'trans_domain' => 'EkynaCommerce',
                'theme'        => 'default',
                'icon'         => 'upload',
            ],
            'options'    => [
                'template'      => '@EkynaCommerce/Admin/ProductionOrder/upgrade.html.twig',
                'form_template' => '@EkynaAdmin/Entity/Crud/_form_confirm.html.twig',
            ],
        ];
    }
}
