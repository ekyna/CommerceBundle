<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\StockUnit;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\AdminBundle\Action\Util\ModalTrait;
use Ekyna\Bundle\CommerceBundle\Action\Admin\StockViewTrait;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\FormTrait;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Bundle\ResourceBundle\Action\RegistryTrait;
use Ekyna\Bundle\ResourceBundle\Action\RepositoryTrait;
use Ekyna\Bundle\ResourceBundle\Action\RoutingActionInterface;
use Ekyna\Bundle\UiBundle\Form\Type\ConfirmType;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Component\Commerce\Exception\StockLogicException;
use Ekyna\Component\Commerce\Stock\Model\StockAdjustmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Route;

use function Symfony\Component\Translation\t;

/**
 * Class DeleteAdjustmentAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\StockUnit
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class DeleteAdjustmentAction extends AbstractAction implements AdminActionInterface, RoutingActionInterface
{
    use RepositoryTrait;
    use RegistryTrait;
    use FormTrait;
    use HelperTrait;
    use ManagerTrait;
    use StockViewTrait;
    use ModalTrait;

    public function __invoke(): Response
    {
        if (!$this->request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }

        $stockUnit = $this->context->getResource();
        if (!$stockUnit instanceof StockUnitInterface) {
            throw new UnexpectedTypeException($stockUnit, StockUnitInterface::class);
        }

        /** @var StockAdjustmentInterface $stockAdjustment */
        $stockAdjustment = $this
            ->getRepository(StockAdjustmentInterface::class)
            ->find(
                $this->request->attributes->getInt('adjustmentId')
            );

        if (!$stockAdjustment) {
            throw new NotFoundHttpException();
        }

        $form = $this
            ->createForm(ConfirmType::class, null, [
                'action'            => $this->generateResourcePath($stockUnit, self::class, [
                    'adjustmentId' => $stockAdjustment->getId(),
                ]),
                'attr'              => ['class' => 'form-horizontal'],
                'method'            => 'POST',
                '_redirect_enabled' => true,
                'message'           => t('stock_adjustment.confirm.remove', [], 'EkynaCommerce'),
                'buttons'           => false,
            ]);

        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $event = $this
                    ->getManager(StockAdjustmentInterface::class)
                    ->delete($stockAdjustment);

                if (!$event->hasErrors()) {
                    return $this->createStockViewResponse($stockUnit->getSubject());
                }

                // TODO all event messages should be bound to XHR response
                FormUtil::addErrorsFromResourceEvent($form, $event);
            } catch (StockLogicException $exception) {
                $form->addError(new FormError($exception->getMessage()));
            }
        }

        $modal = new Modal('stock_adjustment.header.remove');
        $modal
            ->setDomain('EkynaCommerce')
            ->setSize(Modal::SIZE_NORMAL)
            ->setForm($form->createView())
            ->addButton(Modal::BTN_CONFIRM)
            ->addButton(Modal::BTN_CLOSE);

        return $this->renderModal($modal);
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_stock_unit_adjustment_delete',
            'permission' => Permission::DELETE,
            'route'      => [
                'name'     => 'admin_%s_stock_unit_adjustment_delete',
                'path'     => '/adjustments/{adjustmentId}/delete',
                'methods'  => ['GET', 'POST'],
                'resource' => true,
            ],
            'button'     => [
                'label'        => 'button.remove',
                'trans_domain' => 'EkynaUi',
                'theme'        => 'success',
                'icon'         => 'plus',
            ],
        ];
    }

    public static function buildRoute(Route $route, array $options): void
    {
        $route->addRequirements([
            'adjustmentId' => '\d+',
        ]);
    }
}
